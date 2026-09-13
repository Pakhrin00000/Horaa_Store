<?php
// shop.php - HORAA STORE Catalog & Filter Page
$pageTitle = "Shop Catalog - Gaming Gear & Digital Products";
require_once __DIR__ . '/includes/header.php';

// URL Filters
$catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$search = isset($_GET['s']) ? trim($_GET['s']) : '';
$isDigital = isset($_GET['digital']) ? (int)$_GET['digital'] : 0;
$inStock = isset($_GET['in_stock']) ? (int)$_GET['in_stock'] : 0;
$minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$sort = $_GET['sort'] ?? 'newest';

// Build SQL Query
$sql = "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($catId > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $catId;
}
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.short_description LIKE ? OR p.description LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}
if ($isDigital === 1) {
    $sql .= " AND p.is_digital = 1";
}
if ($inStock === 1) {
    $sql .= " AND p.stock > 0";
}
if ($minPrice > 0) {
    $sql .= " AND (CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) >= ?";
    $params[] = $minPrice;
}
if ($maxPrice > 0) {
    $sql .= " AND (CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) <= ?";
    $params[] = $maxPrice;
}

// Sorting Clause
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY (CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY (CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) DESC";
        break;
    case 'name':
        $sql .= " ORDER BY p.name ASC";
        break;
    default:
        $sql .= " ORDER BY p.id DESC";
        break;
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch Categories for Sidebar with Counts
$categoriesStmt = $db->query("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id ASC");
$allCategories = $categoriesStmt->fetchAll();

// Active Category Name
$activeCategoryName = "All Products";
if ($catId > 0) {
    foreach ($allCategories as $c) {
        if ($c['id'] == $catId) {
            $activeCategoryName = $c['name'];
            break;
        }
    }
}
?>

<!-- BREADCRUMB & HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="index.php" class="text-cyan">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?php echo sanitize($activeCategoryName); ?></li>
            </ol>
        </nav>
        <h2 class="text-white mb-0 font-monospace">
            <?php echo sanitize($activeCategoryName); ?> 
            <small class="text-muted fs-6 font-sans-serif">(<?php echo count($products); ?> items found)</small>
        </h2>
    </div>

    <!-- Clear Filters Button if filters are active -->
    <?php if ($catId || $search || $isDigital || $inStock || $minPrice || $maxPrice): ?>
        <a href="shop.php" class="btn btn-sm btn-outline-danger">
            <i class="fas fa-rotate-left me-1"></i> Clear All Filters
        </a>
    <?php endif; ?>
</div>

<div class="row g-4">
    
    <!-- SIDEBAR FILTERS -->
    <div class="col-lg-3">
        <!-- Search Filter -->
        <div class="filter-card">
            <h5 class="filter-title"><i class="fas fa-search me-2"></i> Search Keyword</h5>
            <form action="shop.php" method="GET">
                <?php if ($catId): ?><input type="hidden" name="cat" value="<?php echo $catId; ?>"><?php endif; ?>
                <div class="input-group">
                    <input type="text" name="s" class="form-control form-control-cyber" placeholder="Search..." value="<?php echo sanitize($search); ?>">
                    <button class="btn btn-cyan" type="submit"><i class="fas fa-arrow-right"></i></button>
                </div>
            </form>
        </div>

        <!-- Category Filter -->
        <div class="filter-card">
            <h5 class="filter-title"><i class="fas fa-layer-group me-2"></i> Categories</h5>
            <ul class="filter-list">
                <li>
                    <a href="shop.php" class="<?php echo $catId == 0 ? 'active' : ''; ?>">
                        <span><i class="fas fa-border-all me-2"></i> All Categories</span>
                    </a>
                </li>
                <?php foreach ($allCategories as $c): ?>
                    <li>
                        <a href="shop.php?cat=<?php echo $c['id']; ?>" class="<?php echo $catId == $c['id'] ? 'active' : ''; ?>">
                            <span><i class="<?php echo sanitize($c['icon']); ?> me-2"></i> <?php echo sanitize($c['name']); ?></span>
                            <span class="badge-cyber"><?php echo $c['product_count']; ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Price Range Filter -->
        <div class="filter-card">
            <h5 class="filter-title"><i class="fas fa-dollar-sign me-2"></i> Filter by Price</h5>
            <form action="shop.php" method="GET">
                <?php if ($catId): ?><input type="hidden" name="cat" value="<?php echo $catId; ?>"><?php endif; ?>
                <?php if ($search): ?><input type="hidden" name="s" value="<?php echo sanitize($search); ?>"><?php endif; ?>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <input type="number" step="0.01" name="min_price" class="form-control form-control-cyber" placeholder="Min $" value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>">
                    </div>
                    <div class="col-6">
                        <input type="number" step="0.01" name="max_price" class="form-control form-control-cyber" placeholder="Max $" value="<?php echo $maxPrice > 0 ? $maxPrice : ''; ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-outline-cyber w-100"><i class="fas fa-filter me-1"></i> Apply Price Filter</button>
            </form>
        </div>

        <!-- Options Toggles -->
        <div class="filter-card">
            <h5 class="filter-title"><i class="fas fa-sliders me-2"></i> Stock & Type</h5>
            <div class="form-check mb-2">
                <input class="form-check-input bg-dark border-cyan" type="checkbox" id="inStockCheck" <?php echo $inStock ? 'checked' : ''; ?> 
                       onchange="window.location.href='shop.php?cat=<?php echo $catId; ?>&in_stock=' + (this.checked ? '1' : '0')">
                <label class="form-check-label text-muted" for="inStockCheck">In Stock Only</label>
            </div>
            <div class="form-check">
                <input class="form-check-input bg-dark border-magenta" type="checkbox" id="digitalCheck" <?php echo $isDigital ? 'checked' : ''; ?>
                       onchange="window.location.href='shop.php?cat=<?php echo $catId; ?>&digital=' + (this.checked ? '1' : '0')">
                <label class="form-check-label text-magenta fw-bold" for="digitalCheck">Digital Game Keys Only</label>
            </div>
        </div>
    </div>

    <!-- MAIN PRODUCT CATALOG GRID -->
    <div class="col-lg-9">
        
        <!-- Sorting Bar -->
        <div class="bg-glass p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2 neon-border">
            <div class="text-muted small">
                Showing <strong class="text-cyan"><?php echo count($products); ?></strong> results
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="text-muted small text-nowrap">Sort By:</label>
                <select class="form-select form-select-sm bg-dark text-white border-cyan" onchange="location = this.value;">
                    <option value="shop.php?cat=<?php echo $catId; ?>&s=<?php echo urlencode($search); ?>&sort=newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest Arrivals</option>
                    <option value="shop.php?cat=<?php echo $catId; ?>&s=<?php echo urlencode($search); ?>&sort=price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="shop.php?cat=<?php echo $catId; ?>&s=<?php echo urlencode($search); ?>&sort=price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="shop.php?cat=<?php echo $catId; ?>&s=<?php echo urlencode($search); ?>&sort=name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                </select>
            </div>
        </div>

        <?php if (empty($products)): ?>
            <div class="text-center py-5 bg-card rounded-4 border border-secondary">
                <i class="fas fa-ghost text-muted display-1 mb-3"></i>
                <h4 class="text-white">No Gear Found</h4>
                <p class="text-muted">Try clearing your search query or selecting a different category.</p>
                <a href="shop.php" class="btn-cyan mt-2"><i class="fas fa-border-all me-2"></i> Reset Filters</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $prod): ?>
                    <div class="col-xl-4 col-md-6">
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
                                    <span class="small text-muted"><i class="fas fa-box text-cyan me-1"></i> <?php echo $prod['stock']; ?></span>
                                </div>

                                <button onclick="addToCart(<?php echo $prod['id']; ?>)" class="add-cart-btn">
                                    <i class="fas fa-cart-plus me-1"></i> Add To Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
