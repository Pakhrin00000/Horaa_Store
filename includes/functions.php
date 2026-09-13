<?php
// includes/functions.php - Global Helper Functions for HORAA STORE

if (session_status() === PHP_SESSION_NONE) {
    ob_start();
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/esewa.php';

/**
 * Get unique persistent guest session ID for non-logged-in users
 */
function get_session_cart_id() {
    if (!isset($_SESSION['cart_session_id'])) {
        $_SESSION['cart_session_id'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['cart_session_id'];
}

/**
 * Calculate dynamic site root URL path for links & assets
 */
function url($path = '') {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if (substr($scriptDir, -6) === '/admin') {
        $base = substr($scriptDir, 0, -6);
    } else {
        $base = $scriptDir;
    }
    $base = rtrim($base, '/');
    $path = ltrim($path, '/');
    return ($base === '' ? '' : $base) . '/' . $path;
}

/**
 * Sanitize string input
 */
function sanitize($input) {
    if (is_null($input)) return '';
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format price currency ($ formatting)
 */
function format_price($amount) {
    return 'NPR ' . number_format((float)$amount, 2, '.', ',');
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data array
 */
function current_user() {
    if (!is_logged_in()) return null;
    global $db;
    $stmt = $db->prepare("SELECT id, name, email, phone, address, city, postal_code, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Check if user is admin
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Enforce logged-in authentication
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to access this page.');
        header('Location: login.php');
        exit;
    }
}

/**
 * Enforce admin authorization
 */
function require_admin() {
    if (!is_admin()) {
        set_flash('error', 'Admin privileges required.');
        header('Location: login.php');
        exit;
    }
}

/**
 * Set flash alert notification message
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, error, info, warning
        'message' => $message
    ];
}

/**
 * Retrieve and clear flash alert message
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render flash alert message HTML box
 */
function render_flash() {
    $flash = get_flash();
    if ($flash) {
        $typeClass = ($flash['type'] === 'error') ? 'alert-danger' : 'alert-' . $flash['type'];
        $icon = ($flash['type'] === 'error' || $flash['type'] === 'danger') ? 'fa-exclamation-circle' : 'fa-check-circle';
        echo '<div class="alert ' . $typeClass . ' alert-dismissible fade show" role="alert">
                <i class="fas ' . $icon . ' me-2"></i>' . sanitize($flash['message']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}

/**
 * Slugify text helper
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * Get Cart Items
 */
function get_cart_items() {
    global $db;
    $userId = is_logged_in() ? $_SESSION['user_id'] : null;
    $sessionId = get_session_cart_id();

    if ($userId) {
        $stmt = $db->prepare("SELECT c.id AS cart_id, c.quantity, p.*, cat.name AS category_name 
                              FROM cart c 
                              JOIN products p ON c.product_id = p.id 
                              JOIN categories cat ON p.category_id = cat.id 
                              WHERE c.user_id = ? OR c.session_id = ? ORDER BY c.created_at DESC");
        $stmt->execute([$userId, $sessionId]);
    } else {
        $stmt = $db->prepare("SELECT c.id AS cart_id, c.quantity, p.*, cat.name AS category_name 
                              FROM cart c 
                              JOIN products p ON c.product_id = p.id 
                              JOIN categories cat ON p.category_id = cat.id 
                              WHERE c.session_id = ? ORDER BY c.created_at DESC");
        $stmt->execute([$sessionId]);
    }
    return $stmt->fetchAll();
}

/**
 * Get total quantity count in cart
 */
function get_cart_count() {
    $items = get_cart_items();
    $count = 0;
    foreach ($items as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

/**
 * Get Cart Subtotal
 */
function get_cart_subtotal() {
    $items = get_cart_items();
    $subtotal = 0;
    foreach ($items as $item) {
        $effectivePrice = ($item['sale_price'] && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
        $subtotal += $effectivePrice * $item['quantity'];
    }
    return $subtotal;
}

/**
 * Add product to cart
 */
function add_to_cart($productId, $quantity = 1) {
    global $db;
    $userId = is_logged_in() ? $_SESSION['user_id'] : null;
    $sessionId = get_session_cart_id();
    $quantity = max(1, (int)$quantity);

    // Verify stock
    $stmt = $db->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) return false;

    // Check existing item
    if ($userId) {
        $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE (user_id = ? OR session_id = ?) AND product_id = ?");
        $stmt->execute([$userId, $sessionId, $productId]);
    } else {
        $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $productId]);
    }
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        if ($newQty > $product['stock']) $newQty = $product['stock'];
        $updateStmt = $db->prepare("UPDATE cart SET quantity = ?, user_id = ? WHERE id = ?");
        $updateStmt->execute([$newQty, $userId, $existing['id']]);
    } else {
        if ($quantity > $product['stock']) $quantity = $product['stock'];
        $insertStmt = $db->prepare("INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
        $insertStmt->execute([$userId, $sessionId, $productId, $quantity]);
    }
    return true;
}

/**
 * Wishlist functions
 */
function get_wishlist_count() {
    if (!is_logged_in()) return 0;
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return (int)$stmt->fetchColumn();
}

function is_in_wishlist($productId) {
    if (!is_logged_in()) return false;
    global $db;
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $productId]);
    return (bool)$stmt->fetch();
}

/**
 * Generate unique order number (e.g. HORAA-20260912-A89F)
 */
function generate_order_number() {
    return 'HORAA-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

/**
 * Generate or get CSRF token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render CSRF hidden input tag
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Validate CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Fetch product by ID
 */
function get_product_by_id($id) {
    global $db;
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([(int)$id]);
    return $stmt->fetch() ?: null;
}

/**
 * Fetch all categories ordered by name
 */
function get_all_categories() {
    global $db;
    $stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
    return $stmt->fetchAll();
}

