<?php
// wishlist.php - HORAA STORE Wishlist Page
$pageTitle = "My Saved Wishlist - HORAA STORE";
require_once __DIR__ . '/includes/header.php';

require_login();

$userId = $_SESSION['user_id'];

// Fetch Wishlist Products
$stmt = $db->prepare("SELECT w.id AS wishlist_id, p.*, c.name AS category_name FROM wishlist w JOIN products p ON w.product_id = p.id JOIN categories c ON p.category_id = c.id WHERE w.user_id = ? ORDER BY w.created_at DESC");
$stmt->execute([$userId]);
$wishlistItems = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <h2 class="text-white mb-0 font-monospace">
        <i class="fas fa-heart text-magenta me-2"></i> My Wishlist 
        <small class="text-muted fs-6">(<?php echo count($wishlistItems); ?> saved items)</small>
    </h2>
    <a href="shop.php" class="btn btn-sm btn-outline-cyber"><i class="fas fa-store me-1"></i> Explore Shop</a>
</div>

<?php if (empty($wishlistItems)): ?>
    <div class="text-center py-5 bg-card rounded-4 border border-secondary my-4">
        <i class="fas fa-heart-crack text-muted display-1 mb-3"></i>
        <h4 class="text-white">Your Wishlist is Empty</h4>
        <p class="text-muted">Save your favorite gaming rigs, apparel, and digital games for later.</p>
        <a href="shop.php" class="btn-magenta mt-3"><i class="fas fa-gamepad me-2"></i> Browse Gear</a>
    </div>
<?php else: ?>
    <div class="row g-4 mb-5">
        <?php foreach ($wishlistItems as $item): ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="product-card">
                    <div class="product-img-wrapper">
                        <img src="<?php echo sanitize($item['image'] ?: 'assets/images/placeholder.jpg'); ?>" alt="<?php echo sanitize($item['name']); ?>">
                        <button class="wishlist-btn-overlay active" onclick="toggleWishlist(<?php echo $item['id']; ?>, this); setTimeout(() => location.reload(), 500);">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>

                    <div class="product-content">
                        <div class="product-category"><?php echo sanitize($item['category_name']); ?></div>
                        <a href="product.php?id=<?php echo $item['id']; ?>" class="product-title"><?php echo sanitize($item['name']); ?></a>
                        
                        <div class="product-price-box">
                            <span class="current-price"><?php echo format_price($item['sale_price'] ?: $item['price']); ?></span>
                            <span class="small text-muted">Stock: <?php echo $item['stock']; ?></span>
                        </div>

                        <button onclick="addToCart(<?php echo $item['id']; ?>)" class="add-cart-btn">
                            <i class="fas fa-cart-plus me-1"></i> Move To Cart
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
