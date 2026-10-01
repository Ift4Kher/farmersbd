<?php
// ============================================================
// FarmersBD — Order Service
// Creates orders, updates stock, handles transactions.
// ============================================================

class OrderService {

    /**
     * Create a new order from checkout data.
     * All prices recalculated from DB — never trust client input.
     *
     * @param  int    $cartId         Cart ID
     * @param  array  $customerData   Validated customer/delivery fields
     * @param  string $paymentMethod  'cod' | 'sslcommerz'
     * @param  ?int   $userId         Logged-in user ID or null
     * @param  ?string $couponCode    Optional coupon code
     * @return array  ['success' => bool, 'order_id' => int, 'order_number' => string, 'total' => float, 'error' => string]
     */
    public function create_order(int $cartId, array $customerData, string $paymentMethod, ?int $userId, ?string $couponCode = null): array {
        $pdo = db();

        try {
            $pdo->beginTransaction();

            // 1. Load cart items from DB (never trust client-side prices)
            $cartItems = db_query(
                "SELECT ci.id AS cart_item_id, ci.quantity,
                        p.id AS product_id, p.name, p.price, p.discount_price, p.stock, p.is_active
                 FROM cart_items ci
                 JOIN products p ON p.id = ci.product_id
                 WHERE ci.cart_id = ?",
                [$cartId]
            );

            if (empty($cartItems)) {
                $pdo->rollBack();
                return ['success' => false, 'error' => 'কার্টটি খালি।'];
            }

            // 2. Validate each item
            $subtotal = 0.00;
            foreach ($cartItems as &$item) {
                if (!$item['is_active']) {
                    $pdo->rollBack();
                    return ['success' => false, 'error' => "পণ্যটি পাওয়া যাচ্ছে না: {$item['name']}"];
                }
                if ($item['stock'] < $item['quantity']) {
                    $pdo->rollBack();
                    return ['success' => false, 
                            'error' => "স্টক পর্যাপ্ত নেই: {$item['name']} (স্টক: {$item['stock']})"];
                }
                $item['unit_price'] = effective_price($item);
                $item['line_total'] = $item['unit_price'] * $item['quantity'];
                $subtotal += $item['line_total'];
            }
            unset($item);

            // 3. Calculate total and apply coupon
            $discountAmount = 0.00;
            if (!empty($couponCode)) {
                require_once dirname(__DIR__) . '/services/CouponService.php';
                $couponService = new CouponService();
                $couponRes = $couponService->validateCoupon($couponCode, $subtotal, $userId, $customerData['mobile'] ?? null);
                
                if (!$couponRes['success']) {
                    $pdo->rollBack();
                    return ['success' => false, 'error' => $couponRes['error']];
                }
                $discountAmount = $couponRes['discount'];
            }

            $shippingCost = (float) setting('shipping_cost', 60);
            $total = ($subtotal - $discountAmount) + $shippingCost;

            // 4. Generate order number
            $orderNumber = generate_order_number();

            // 5. Insert order
            $orderId = db_insert(
                "INSERT INTO orders 
                 (order_number, user_id, customer_name, customer_mobile, customer_email,
                  address, district, upazila, notes, subtotal, discount, coupon_code, shipping_cost, total,
                  payment_method, payment_status, order_status)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,'pending','pending')",
                [
                    $orderNumber,
                    $userId,
                    $customerData['name'],
                    $customerData['mobile'],
                    $customerData['email'] ?? null,
                    $customerData['address'],
                    $customerData['district'],
                    $customerData['upazila'] ?? null,
                    $customerData['notes']   ?? null,
                    $subtotal,
                    $discountAmount,
                    $couponCode,
                    $shippingCost,
                    $total,
                    $paymentMethod,
                ]
            );

            // 6. Insert order items & deduct stock atomically
            foreach ($cartItems as $item) {
                db_insert(
                    "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, subtotal)
                     VALUES (?,?,?,?,?,?)",
                    [$orderId, $item['product_id'], $item['name'], $item['unit_price'], $item['quantity'], $item['line_total']]
                );

                // Atomic stock decrement — prevent race condition
                $affected = db_execute(
                    "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?",
                    [$item['quantity'], $item['product_id'], $item['quantity']]
                );
                if ($affected === 0) {
                    $pdo->rollBack();
                    return ['success' => false, 'error' => "স্টক পর্যাপ্ত নেই: {$item['name']}"];
                }
                
                // Low stock notification
                $newStock = $item['stock'] - $item['quantity'];
                if ($newStock <= 5) {
                    send_admin_notification('alert', "লো-স্টক অ্যালার্ট: {$item['name']} (স্টক: {$newStock})", BASE_URL . "/admin/products/edit.php?id={$item['product_id']}");
                }
            }

            // 7. Mark cart as recovered instead of clearing it (for incomplete orders tracking)
            db_execute("UPDATE carts SET status = 'recovered', recovery_order_id = ? WHERE id = ?", [$orderId, $cartId]);
            // (We keep cart_items intact for historical incomplete order view)
            
            // Increment coupon usage
            if (!empty($couponCode)) {
                db_execute("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?", [$couponCode]);
            }
            
            // Generate a new session for Guest carts so they start with an empty cart
            if (!$userId) {
                unset($_SESSION['cart_session_id']);
            }

            // Send admin notification
            send_admin_notification('order', "নতুন অর্ডার এসেছে: {$orderNumber}", BASE_URL . "/admin/orders/details.php?id={$orderId}");

            $pdo->commit();

            return [
                'success'      => true,
                'order_id'     => $orderId,
                'order_number' => $orderNumber,
                'total'        => $total,
                'error'        => null,
            ];

        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('[OrderService] create_order failed: ' . $e->getMessage());
            return ['success' => false, 'error' => 'অর্ডার তৈরি ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।'];
        }
    }

    /**
     * Cancel an order and restore stock (if still pending/processing).
     */
    public function cancel_order(int $orderId, ?int $userId = null): array {
        $order = $userId
            ? db_query_one("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, $userId])
            : db_query_one("SELECT * FROM orders WHERE id = ?", [$orderId]);

        if (!$order) {
            return ['success' => false, 'error' => 'অর্ডার পাওয়া যায়নি।'];
        }

        if (!in_array($order['order_status'], ['pending', 'processing'], true)) {
            return ['success' => false, 'error' => 'এই অর্ডার বাতিল করা যাবে না।'];
        }

        try {
            db()->beginTransaction();

            // Restore stock
            $items = db_query("SELECT product_id, quantity FROM order_items WHERE order_id = ?", [$orderId]);
            foreach ($items as $item) {
                db_execute(
                    "UPDATE products SET stock = stock + ? WHERE id = ?",
                    [$item['quantity'], $item['product_id']]
                );
            }

            db_execute(
                "UPDATE orders SET order_status = 'cancelled', payment_status = 'cancelled' WHERE id = ?",
                [$orderId]
            );

            db()->commit();
            return ['success' => true, 'error' => null];

        } catch (Throwable $e) {
            db()->rollBack();
            error_log('[OrderService] cancel_order failed: ' . $e->getMessage());
            return ['success' => false, 'error' => 'অর্ডার বাতিল ব্যর্থ হয়েছে।'];
        }
    }

    /**
     * Get full order details with items.
     */
    public function get_order_details(int $orderId): ?array {
        $order = db_query_one("SELECT * FROM orders WHERE id = ?", [$orderId]);
        if (!$order) return null;
        $order['items'] = db_query("SELECT * FROM order_items WHERE order_id = ?", [$orderId]);
        $order['payment'] = db_query_one("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1", [$orderId]);
        return $order;
    }
}
