<?php
// index.php - HORAA STORE Homepage
$pageTitle = "HORAA STORE - Gaming • Tech • Digital — Everything in One Place";
require_once __DIR__ . '/includes/header.php';

// Fetch Categories
$catStmt = $db->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $catStmt->fetchAll();

// Fetch Featured Products
$featStmt = $db->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 ORDER BY p.id DESC LIMIT 8");
$featuredProducts = $featStmt->fetchAll();

// Fetch Esports Jerseys (Category ID 1)
$jerseyStmt = $db->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.category_id = 1 LIMIT 4");
$esportsMerch = $jerseyStmt->fetchAll();

// Fetch Digital Products (is_digital = 1)
$digitalStmt = $db->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_digital = 1 LIMIT 4");
$digitalProducts = $digitalStmt->fetchAll();
?>

<!-- HERO BANNER SECTION -->
<section class="hero-section rounded-4 mb-5 border border-secondary">
    <div class="row align-items-center g-4 px-4 px-md-5">
        <div class="col-lg-7">
            <span class="badge-red mb-3 d-inline-block">
                <i class="fas fa-bolt text-red me-1"></i> OFFICIAL HORAA ESPORTS MARKETPLACE
            </span>
            <h1 class="hero-title text-white">
                GEAR UP WITH <span class="text-red">HORAA</span> ESPORTS & TECH
            </h1>
            <p class="hero-subtitle">
                Level up your setup with official Esports Jerseys, Pro Mechanical Keyboards, 8K Wireless Mice, Custom Flagship PCs, and Instant Digital Game Codes.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="shop.php" class="btn-red">
                    <i class="fas fa-gamepad me-2"></i> Explore Shop
                </a>
                <a href="shop.php?cat=1" class="btn-magenta">
                    <i class="fas fa-tshirt me-2"></i> Official Merch
                </a>
                <a href="shop.php?digital=1" class="btn-outline-cyber">
                    <i class="fas fa-key me-2"></i> Digital Keys
                </a>
            </div>
        </div>
        <div class="col-lg-5 text-center">
            <div class="hero-img-box">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR-mwlNRs8Z21M5ysZ--Bg4UuoaGDFPDGSO2h0GE7zGeRMGfGWSzFI_tG8&s=10" alt="HORAA Esports Jersey" class="rounded-4 img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- VALUE PROPOSITIONS BAR -->
<div class="row g-3 mb-5">
    <div class="col-md-3 col-6">
        <div class="bg-glass p-3 rounded-3 text-center border-0 neon-border">
            <i class="fas fa-truck-fast text-cyan fs-2 mb-2"></i>
            <h6 class="mb-1 text-white font-subheading">Free Express Shipping</h6>
            <small class="text-muted">On all orders over NPR 99</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bg-glass p-3 rounded-3 text-center border-0 neon-border">
            <i class="fas fa-key text-magenta fs-2 mb-2"></i>
            <h6 class="mb-1 text-white font-subheading">Instant Key Delivery</h6>
            <small class="text-muted">Direct to your dashboard</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bg-glass p-3 rounded-3 text-center border-0 neon-border">
            <i class="fas fa-shield-halved text-gold fs-2 mb-2"></i>
            <h6 class="mb-1 text-white font-subheading">100% Genuine Warranty</h6>
            <small class="text-muted">Official brand protection</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bg-glass p-3 rounded-3 text-center border-0 neon-border">
            <i class="fas fa-headset text-cyan fs-2 mb-2"></i>
            <h6 class="mb-1 text-white font-subheading">24/7 Gamer Support</h6>
            <small class="text-muted">Live chat & Discord assistance</small>
        </div>
    </div>
</div>

<!-- CATEGORIES GRID -->
<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-white mb-0 font-monospace"><i class="fas fa-grid-2 text-cyan me-2"></i> Product Categories</h3>
            <small class="text-muted">Browse gear, peripherals & digital codes</small>
        </div>
        <a href="shop.php" class="btn btn-sm btn-outline-cyber">View All <i class="fas fa-arrow-right ms-1"></i></a>
    </div>

    <div class="row g-3">
        <?php foreach ($categories as $cat): ?>
            <div class="col-lg-2 col-md-3 col-6">
                <a href="shop.php?cat=<?php echo $cat['id']; ?>" class="category-card shadow-sm">
                    <div class="category-icon-wrapper">
                        <i class="<?php echo sanitize($cat['icon']); ?>"></i>
                    </div>
                    <div class="category-name"><?php echo sanitize($cat['name']); ?></div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-white mb-0 font-monospace"><i class="fas fa-star text-gold me-2"></i> Featured Products</h3>
            <small class="text-muted">Top rated gaming gear & components</small>
        </div>
        <a href="shop.php" class="btn btn-sm btn-outline-cyber">Browse All <i class="fas fa-arrow-right ms-1"></i></a>
    </div>

    <div class="row g-4">
        <?php foreach ($featuredProducts as $prod): ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="product-card">
                    <div class="product-img-wrapper">
                        <img src="<?php echo sanitize($prod['image'] ?: 'assets/images/placeholder.jpg'); ?>" alt="<?php echo sanitize($prod['name']); ?>">
                        <div class="product-badge-overlay">
                            <?php if ($prod['sale_price']): ?>
                                <span class="badge-magenta">SALE</span>
                            <?php endif; ?>
                            <?php if ($prod['is_digital']): ?>
                                <span class="badge-cyber"><i class="fas fa-key me-1"></i> DIGITAL</span>
                            <?php endif; ?>
                        </div>
                        <button class="wishlist-btn-overlay <?php echo is_in_wishlist($prod['id']) ? 'active' : ''; ?>" 
                                onclick="toggleWishlist(<?php echo $prod['id']; ?>, this)">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>

                    <div class="product-content">
                        <div class="product-category"><?php echo sanitize($prod['category_name']); ?></div>
                        <a href="product.php?id=<?php echo $prod['id']; ?>" class="product-title"><?php echo sanitize($prod['name']); ?></a>
                        
                        <div class="product-price-box">
                            <div>
                                <?php if ($prod['sale_price']): ?>
                                    <span class="current-price"><?php echo format_price($prod['sale_price']); ?></span>
                                    <span class="old-price"><?php echo format_price($prod['price']); ?></span>
                                <?php else: ?>
                                    <span class="current-price"><?php echo format_price($prod['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="small text-muted"><i class="fas fa-box text-cyan me-1"></i> Stock: <?php echo $prod['stock']; ?></span>
                        </div>

                        <button onclick="addToCart(<?php echo $prod['id']; ?>)" class="add-cart-btn">
                            <i class="fas fa-cart-plus me-1"></i> Add To Cart
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ESPORTS MERCHANDISE BANNER & ITEMS -->
<section class="mb-5 p-4 rounded-4 bg-glass border border-secondary">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <span class="badge-red mb-2">OFFICIAL ESPORTS COLLECTION</span>
            <h3 class="text-white mb-1"><i class="fas fa-tshirt text-red me-2"></i> HORAA Pro Esports Merchandise</h3>
            <p class="text-muted mb-0">Engineered for tournament champions. Ultra-breathable, moisture-wicking micro-mesh apparel.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="shop.php?cat=1" class="btn-red">Shop All Apparel <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($esportsMerch as $prod): ?>
            <div class="col-lg-3 col-md-6">
                <div class="product-card">
                    <div class="product-img-wrapper">
                        <img src="<?php echo sanitize($prod['image'] ?: 'assets/images/placeholder.jpg'); ?>" alt="<?php echo sanitize($prod['name']); ?>">
                    </div>
                    <div class="product-content">
                        <a href="product.php?id=<?php echo $prod['id']; ?>" class="product-title"><?php echo sanitize($prod['name']); ?></a>
                        <div class="product-price-box">
                            <span class="current-price"><?php echo format_price($prod['sale_price'] ?: $prod['price']); ?></span>
                        </div>
                        <button onclick="addToCart(<?php echo $prod['id']; ?>)" class="add-cart-btn">
                            <i class="fas fa-cart-plus me-1"></i> Add To Cart
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- DIGITAL PRODUCTS SPOTLIGHT -->
<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-white mb-0"><i class="fas fa-key text-magenta me-2"></i> Instant Digital Store</h3>
            <small class="text-muted">Game Keys, Steam Wallet & Subscriptions</small>
        </div>
        <a href="shop.php?digital=1" class="btn btn-sm btn-outline-cyber">Explore Digital Store <i class="fas fa-arrow-right ms-1"></i></a>
    </div>

    <div class="row g-4">
        <?php foreach ($digitalProducts as $prod): ?>
            <div class="col-lg-3 col-md-6">
                <div class="product-card">
                    <div class="product-img-wrapper">
                        <img src="<?php echo sanitize($prod['image'] ?: 'assets/images/placeholder.jpg'); ?>" alt="<?php echo sanitize($prod['name']); ?>">
                        <div class="product-badge-overlay">
                            <span class="badge-cyber"><i class="fas fa-bolt me-1"></i> INSTANT KEY</span>
                        </div>
                    </div>
                    <div class="product-content">
                        <a href="product.php?id=<?php echo $prod['id']; ?>" class="product-title"><?php echo sanitize($prod['name']); ?></a>
                        <div class="product-price-box">
                            <span class="current-price"><?php echo format_price($prod['sale_price'] ?: $prod['price']); ?></span>
                        </div>
                        <button onclick="addToCart(<?php echo $prod['id']; ?>)" class="add-cart-btn">
                            <i class="fas fa-bolt me-1"></i> Buy & Download
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
