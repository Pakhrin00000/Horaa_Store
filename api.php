<?php
// api.php - Central JSON AJAX API Endpoint for HORAA STORE
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/includes/functions.php';

$action = $_REQUEST['action'] ?? '';

// 1. ADD TO CART
if ($action === 'add_to_cart') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);

    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }

    $res = add_to_cart($productId, $quantity);
    if ($res) {
        echo json_encode([
            'success' => true,
            'message' => 'Product added to shopping cart!',
            'cart_count' => get_cart_count(),
            'cart_subtotal' => format_price(get_cart_subtotal())
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item is out of stock or unavailable.']);
    }
    exit;
}

// 2. TOGGLE WISHLIST
if ($action === 'toggle_wishlist') {
    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'message' => 'Please sign in to save items to your wishlist.']);
        exit;
    }

    $productId = (int)($_POST['product_id'] ?? 0);
    $userId = $_SESSION['user_id'];

    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }

    global $db;
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $del = $db->prepare("DELETE FROM wishlist WHERE id = ?");
        $del->execute([$existing['id']]);
        $inWishlist = false;
        $msg = 'Removed from wishlist.';
    } else {
        $ins = $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $ins->execute([$userId, $productId]);
        $inWishlist = true;
        $msg = 'Added to wishlist!';
    }

    echo json_encode([
        'success' => true,
        'message' => $msg,
        'in_wishlist' => $inWishlist,
        'wishlist_count' => get_wishlist_count()
    ]);
    exit;
}

// 3. SEARCH SUGGESTIONS (LIVE AUTOCOMPLETE)
if ($action === 'search_suggestions') {
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) {
        echo json_encode([]);
        exit;
    }

    global $db;
    $stmt = $db->prepare("SELECT id, name, price, sale_price, image FROM products WHERE name LIKE ? OR short_description LIKE ? LIMIT 6");
    $searchTerm = "%{$q}%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();

    echo json_encode($results);
    exit;
}

// 4. APPLY COUPON CODE
if ($action === 'apply_coupon') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a voucher code.']);
        exit;
    }

    global $db;
    $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE())");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code.']);
        exit;
    }

    $subtotal = get_cart_subtotal();
    if ($subtotal < $coupon['min_order_amount']) {
        echo json_encode([
            'success' => false, 
            'message' => 'Minimum order amount for this coupon is ' . format_price($coupon['min_order_amount'])
        ]);
        exit;
    }

    if ($coupon['usage_limit'] && $coupon['times_used'] >= $coupon['usage_limit']) {
        echo json_encode(['success' => false, 'message' => 'This coupon has reached its maximum usage limit.']);
        exit;
    }

    // Save coupon in session
    $_SESSION['applied_coupon'] = [
        'id' => $coupon['id'],
        'code' => $coupon['code'],
        'type' => $coupon['discount_type'],
        'value' => (float)$coupon['discount_value']
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Coupon "' . $coupon['code'] . '" applied successfully!',
        'code' => $coupon['code']
    ]);
    exit;
}

// Fallback
echo json_encode(['success' => false, 'message' => 'Invalid action API endpoint']);
