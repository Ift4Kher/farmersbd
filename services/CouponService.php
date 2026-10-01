<?php
// ============================================================
// FarmersBD — Coupon Service
// Handles coupon validation, application, and tracking.
// ============================================================

class CouponService {
    
    /**
     * Validate a coupon against cart subtotal and user.
     *
     * @param string $code
     * @param float $subtotal
     * @param int|null $userId
     * @param string|null $phone
     * @return array ['success' => bool, 'discount' => float, 'error' => string, 'coupon' => array]
     */
    public function validateCoupon(string $code, float $subtotal, ?int $userId = null, ?string $phone = null): array {
        $code = trim($code);
        if (empty($code)) {
            return ['success' => false, 'error' => 'কুপন কোড প্রদান করুন।'];
        }

        $coupon = db_query_one("SELECT * FROM coupons WHERE code = ?", [$code]);
        if (!$coupon) {
            return ['success' => false, 'error' => 'কুপন কোডটি সঠিক নয়।'];
        }

        if (!$coupon['is_active']) {
            return ['success' => false, 'error' => 'এই কুপনটি বর্তমানে বন্ধ আছে।'];
        }

        $now = date('Y-m-d H:i:s');
        if ($coupon['start_date'] && $coupon['start_date'] > $now) {
            return ['success' => false, 'error' => 'এই কুপনটি এখনও শুরু হয়নি।'];
        }

        if ($coupon['expiry_date'] && $coupon['expiry_date'] < $now) {
            return ['success' => false, 'error' => 'এই কুপনটির মেয়াদ শেষ হয়ে গেছে।'];
        }

        if ($coupon['usage_limit'] !== null && $coupon['used_count'] >= $coupon['usage_limit']) {
            return ['success' => false, 'error' => 'এই কুপনটির ব্যবহার সীমা অতিক্রম করেছে।'];
        }

        if ($subtotal < $coupon['min_order_amount']) {
            return ['success' => false, 'error' => 'এই কুপনটি ব্যবহার করতে ন্যূনতম ' . format_price_bn($coupon['min_order_amount']) . ' টাকার অর্ডার করতে হবে।'];
        }

        // Check per user limit
        if ($coupon['per_user_limit'] !== null && $coupon['per_user_limit'] > 0) {
            $usageCount = 0;
            if ($userId) {
                $usageCount = db_query_one("SELECT COUNT(*) as c FROM orders WHERE coupon_code = ? AND user_id = ? AND payment_status != 'failed' AND order_status != 'cancelled'", [$code, $userId])['c'] ?? 0;
            } elseif ($phone) {
                $usageCount = db_query_one("SELECT COUNT(*) as c FROM orders WHERE coupon_code = ? AND customer_mobile = ? AND payment_status != 'failed' AND order_status != 'cancelled'", [$code, $phone])['c'] ?? 0;
            }

            if ($usageCount >= $coupon['per_user_limit']) {
                return ['success' => false, 'error' => 'আপনি এই কুপনটি ইতিমধ্যে সর্বোচ্চ সংখ্যকবার ব্যবহার করেছেন।'];
            }
        }

        // Calculate discount
        $discount = 0.00;
        if ($coupon['discount_type'] === 'percentage') {
            $discount = $subtotal * ($coupon['discount_value'] / 100);
            if ($coupon['max_discount'] !== null && $coupon['max_discount'] > 0 && $discount > $coupon['max_discount']) {
                $discount = $coupon['max_discount'];
            }
        } else {
            // fixed
            $discount = $coupon['discount_value'];
        }

        // Discount cannot exceed subtotal
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        return [
            'success' => true,
            'discount' => (float)$discount,
            'coupon' => $coupon
        ];
    }
}
