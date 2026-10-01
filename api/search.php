<?php
// ============================================================
// FarmersBD — Global AJAX Search API
// Returns matching products, diseases, and blog posts
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$query = trim($_GET['q'] ?? '');
if (mb_strlen($query) < 1) {
    echo json_encode(['success' => true, 'results' => []]);
    exit;
}

$term = "%{$query}%";
$results = [];

try {
    // 1. Search Products
    $products = db_query(
        "SELECT id, name, slug, price, discount_price, image 
         FROM products 
         WHERE is_active = 1 AND (name LIKE ? OR description LIKE ?) 
         ORDER BY id DESC LIMIT 4",
        [$term, $term]
    );

    foreach ($products as $p) {
        $img = '';
        if (!empty($p['image'])) {
            $img = uploaded_image_url('products', $p['image']);
        }
        $finalPrice = (!empty($p['discount_price']) && $p['discount_price'] > 0) ? $p['discount_price'] : $p['price'];
        $results[] = [
            'id'          => $p['id'],
            'title'       => $p['name'],
            'type'        => 'product',
            'type_label'  => 'ঔষধ ও পণ্য',
            'badge_class' => 'bg-primary',
            'url'         => url('products/details.php?id=' . $p['id']),
            'image'       => $img,
            'extra'       => format_price($finalPrice)
        ];
    }

    // 2. Search Diseases
    $diseases = db_query(
        "SELECT id, name, slug, image, symptoms 
         FROM diseases 
         WHERE is_active = 1 AND (name LIKE ? OR symptoms LIKE ? OR description LIKE ?) 
         ORDER BY id DESC LIMIT 3",
        [$term, $term, $term]
    );

    foreach ($diseases as $d) {
        $img = '';
        if (!empty($d['image'])) {
            $img = uploaded_image_url('diseases', $d['image']);
        }
        $results[] = [
            'id'          => $d['id'],
            'title'       => $d['name'],
            'type'        => 'disease',
            'type_label'  => 'মাছের রোগ',
            'badge_class' => 'bg-danger',
            'url'         => url('diseases/details.php?id=' . $d['id']),
            'image'       => $img,
            'extra'       => ''
        ];
    }

    // 3. Search Blogs
    $blogs = db_query(
        "SELECT id, title, slug, image 
         FROM blogs 
         WHERE is_active = 1 AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?) 
         ORDER BY id DESC LIMIT 3",
        [$term, $term, $term]
    );

    foreach ($blogs as $b) {
        $img = '';
        if (!empty($b['image'])) {
            $img = uploaded_image_url('blogs', $b['image']);
        }
        $results[] = [
            'id'          => $b['id'],
            'title'       => $b['title'],
            'type'        => 'blog',
            'type_label'  => 'ব্লগ আর্টিকেল',
            'badge_class' => 'bg-success',
            'url'         => url('blog/details.php?slug=' . urlencode($b['slug'] ?? '')) ?: url('blog/details.php?id=' . $b['id']),
            'image'       => $img,
            'extra'       => ''
        ];
    }

    // 4. Search Fish Species
    $fishList = db_query(
        "SELECT id, name, slug, image 
         FROM fish 
         WHERE is_active = 1 AND (name LIKE ? OR description LIKE ?) 
         ORDER BY id DESC LIMIT 3",
        [$term, $term]
    );

    foreach ($fishList as $f) {
        $img = '';
        if (!empty($f['image'])) {
            $img = uploaded_image_url('fish', $f['image']);
        }
        $results[] = [
            'id'          => $f['id'],
            'title'       => $f['name'],
            'type'        => 'fish',
            'type_label'  => 'মাছের তথ্য',
            'badge_class' => 'bg-info',
            'url'         => url('fish/details.php?slug=' . urlencode($f['slug'] ?? '')),
            'image'       => $img,
            'extra'       => ''
        ];
    }

    // 5. Generate Keyword Suggestions
    $suggestions = [];
    foreach ($results as $item) {
        $cleanTitle = trim(preg_replace('/\s*\(.*?\)/', '', $item['title']));
        if (!empty($cleanTitle) && !in_array($cleanTitle, $suggestions)) {
            $suggestions[] = $cleanTitle;
        }
    }

    echo json_encode([
        'success'     => true,
        'count'       => count($results),
        'query'       => $query,
        'suggestions' => array_slice($suggestions, 0, 5),
        'results'     => $results
    ]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'results' => [], 'suggestions' => []]);
}


