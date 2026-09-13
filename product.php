<?php
// product.php - HORAA STORE Single Product Details
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: shop.php');
    exit;
}

// Fetch Product
$stmt = $db->prepare("SELECT p.*, c.name AS category_name, c.id AS category_id FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Product not found.');
    header('Location: shop.php');
    exit;
}

$pageTitle = $product['name'] . " - HORAA STORE";

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating = (int)($_POST['rating'] ?? 5);
    $comment = trim($_POST['comment'] ?? '');
    $reviewerName = trim($_POST['user_name'] ?? '');
    
    if (is_logged_in()) {
        $u = current_user();
        $reviewerName = $u['name'];
        $userId = $u['id'];
    } else {
        $userId = null;
    }

    if (empty($reviewerName) || empty($comment)) {
        set_flash('error', 'Please fill out your name and review comment.');
    } else {
        $ins = $db->prepare("INSERT INTO reviews (product_id, user_id, user_name, rating, comment, status) VALUES (?, ?, ?, ?, ?, 'approved')");
        $ins->execute([$id, $userId, $reviewerName, $rating, $comment]);
        set_flash('success', 'Thank you! Your product review has been submitted.');
        header("Location: product.php?id={$id}");
        exit;
    }
}

// Decode Specifications JSON
$specs = !empty($product['specifications']) ? json_decode($product['specifications'], true) : [];
$additionalImages = !empty($product['additional_images']) ? json_decode($product['additional_images'], true) : [];

// Fetch Approved Reviews
$revStmt = $db->prepare("SELECT * FROM reviews WHERE product_id = ? AND status = 'approved' ORDER BY created_at DESC");
$revStmt->execute([$id]);
$reviews = $revStmt->fetchAll();

// Calculate Average Rating
$avgRating = 5.0;
if (count($reviews) > 0) {
    $sum = 0;
    foreach ($reviews as $r) $sum += $r['rating'];
    $avgRating = round($sum / count($reviews), 1);
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- BREADCRUMB -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php" class="text-cyan">Home</a></li>
        <li class="breadcrumb-item"><a href="shop.php?cat=<?php echo $product['category_id']; ?>" class="text-cyan"><?php echo sanitize($product['category_name']); ?></a></li>
        <li class="breadcrumb-item active text-white" aria-current="page"><?php echo sanitize($product['name']); ?></li>
    </ol>
</nav>

<div class="product-detail-card mb-5">
    <div class="row g-4">
        
        <!-- LEFT: GALLERY & IMAGES -->
        <div class="col-lg-6">
            <div class="position-relative">
                <img id="product-main-view" src="<?php echo sanitize($product['image'] ?: 'assets/images/placeholder.jpg'); ?>" alt="<?php echo sanitize($product['name']); ?>" class="product-main-img">
                <div class="position-absolute top-0 start-0 p-3">
                    <?php if ($product['sale_price']): ?>
                        <span class="badge-magenta fs-6">SALE</span>
                    <?php endif; ?>
                    <?php if ($product['is_digital']): ?>
                        <span class="badge-cyber fs-6"><i class="fas fa-key me-1"></i> DIGITAL PRODUCT</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Thumbnails -->
            <div class="thumb-gallery">
                <div class="thumb-item active">
                    <img src="<?php echo sanitize($product['image'] ?: 'assets/images/placeholder.jpg'); ?>" alt="thumb">
                </div>
                <?php if (is_array($additionalImages)): ?>
                    <?php foreach ($additionalImages as $img): ?>
                        <div class="thumb-item">
                            <img src="<?php echo sanitize($img); ?>" alt="thumb">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT: SPECS & BUY OPTIONS -->
        <div class="col-lg-6">
            <span class="product-category"><?php echo sanitize($product['category_name']); ?></span>
            <h2 class="text-white font-monospace mb-2"><?php echo sanitize($product['name']); ?></h2>
            
            <!-- Rating Star Display -->
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="text-gold">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star<?php echo $i <= round($avgRating) ? '' : '-half-alt opacity-25'; ?>"></i>
                    <?php endfor; ?>
                </div>
                <span class="text-white fw-bold"><?php echo number_format($avgRating, 1); ?></span>
                <span class="text-muted small">(<?php echo count($reviews); ?> customer reviews)</span>
            </div>

            <!-- Price Display -->
            <div class="my-3 p-3 bg-glass rounded-3 border-0 neon-border d-inline-block">
                <?php if ($product['sale_price']): ?>
                    <span class="display-6 font-monospace fw-bold text-cyan"><?php echo format_price($product['sale_price']); ?></span>
                    <span class="fs-5 text-muted text-decoration-line-through ms-2"><?php echo format_price($product['price']); ?></span>
                    <span class="badge-magenta ms-3">Save <?php echo format_price($product['price'] - $product['sale_price']); ?></span>
                <?php else: ?>
                    <span class="display-6 font-monospace fw-bold text-cyan"><?php echo format_price($product['price']); ?></span>
                <?php endif; ?>
            </div>

            <!-- Stock Status -->
            <div class="mb-3">
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge-cyber bg-dark text-cyan"><i class="fas fa-check-circle me-1"></i> In Stock (<?php echo $product['stock']; ?> available)</span>
                <?php else: ?>
                    <span class="badge-magenta bg-dark"><i class="fas fa-times-circle me-1"></i> Currently Out of Stock</span>
                <?php endif; ?>
                <?php if (!empty($product['sku'])): ?>
                    <span class="text-muted small ms-3">SKU: <strong class="text-white font-monospace"><?php echo sanitize($product['sku']); ?></strong></span>
                <?php endif; ?>
            </div>

            <!-- Short Description -->
            <p class="text-muted mb-4 fs-6">
                <?php echo nl2br(sanitize($product['short_description'] ?: $product['description'])); ?>
            </p>

            <!-- Quantity & Add to Cart -->
            <?php if ($product['stock'] > 0): ?>
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="qty-box">
                        <button class="qty-btn qty-minus" type="button"><i class="fas fa-minus"></i></button>
                        <input type="number" id="detail-quantity" class="qty-input" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        <button class="qty-btn qty-plus" type="button"><i class="fas fa-plus"></i></button>
                    </div>

                    <button onclick="addToCart(<?php echo $product['id']; ?>, document.getElementById('detail-quantity').value)" class="btn-cyan flex-grow-1">
                        <i class="fas fa-shopping-cart me-2"></i> Add To Cart
                    </button>

                    <button class="btn btn-outline-magenta rounded-3 p-2 px-3 <?php echo is_in_wishlist($product['id']) ? 'active' : ''; ?>" 
                            onclick="toggleWishlist(<?php echo $product['id']; ?>, this)" title="Add to Wishlist">
                        <i class="fas fa-heart fs-5"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Digital Product Notice -->
            <?php if ($product['is_digital']): ?>
                <div class="p-3 bg-glass rounded-3 border border-magenta mb-4">
                    <h6 class="text-magenta mb-1"><i class="fas fa-bolt me-2"></i> Instant Digital Delivery</h6>
                    <small class="text-muted">License codes and download links will be made available instantly in your customer order dashboard upon checkout.</small>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- SPECIFICATIONS & DESCRIPTION TABBED SECTION -->
<div class="bg-card rounded-4 border border-cyan p-4 mb-5">
    <ul class="nav nav-tabs border-secondary mb-4" id="productTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active bg-transparent text-cyan font-subheading fw-bold fs-5" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc-pane">
                <i class="fas fa-file-text me-2"></i> Full Description
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link bg-transparent text-cyan font-subheading fw-bold fs-5" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs-pane">
                <i class="fas fa-sliders me-2"></i> Specifications
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link bg-transparent text-cyan font-subheading fw-bold fs-5" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-pane">
                <i class="fas fa-star me-2"></i> Customer Reviews (<?php echo count($reviews); ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content text-white" id="productTabContent">
        
        <!-- Tab 1: Description -->
        <div class="tab-pane fade show active" id="desc-pane">
            <div class="p-2 text-muted lh-lg">
                <?php echo nl2br(sanitize($product['description'] ?: 'No additional description provided.')); ?>
            </div>
        </div>

        <!-- Tab 2: Specs Table -->
        <div class="tab-pane fade" id="specs-pane">
            <?php if (!empty($specs) && is_array($specs)): ?>
                <table class="specs-table">
                    <tbody>
                        <?php foreach ($specs as $key => $val): ?>
                            <tr>
                                <th><?php echo sanitize($key); ?></th>
                                <td><?php echo sanitize($val); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted p-2">Standard manufacturer specifications apply.</p>
            <?php endif; ?>
        </div>

        <!-- Tab 3: Reviews -->
        <div class="tab-pane fade" id="reviews-pane">
            <div class="row g-4">
                
                <!-- Existing Reviews List -->
                <div class="col-lg-7">
                    <h5 class="text-cyan mb-3">Customer Feedback</h5>
                    <?php if (empty($reviews)): ?>
                        <p class="text-muted">No reviews yet for this product. Be the first to leave a review!</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $rev): ?>
                            <div class="p-3 bg-glass rounded-3 mb-3 border-0 neon-border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="text-white"><?php echo sanitize($rev['user_name']); ?></strong>
                                    <div class="text-gold small">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <i class="fas fa-star<?php echo $s <= $rev['rating'] ? '' : '-half-alt opacity-25'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-muted mb-1 small"><?php echo sanitize($rev['comment']); ?></p>
                                <small class="text-secondary font-monospace"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Write a Review Form -->
                <div class="col-lg-5">
                    <div class="p-4 bg-glass rounded-3 neon-border">
                        <h5 class="text-cyan mb-3"><i class="fas fa-pen me-2"></i> Write a Review</h5>
                        <form action="product.php?id=<?php echo $id; ?>" method="POST">
                            
                            <?php if (!is_logged_in()): ?>
                                <div class="mb-3">
                                    <label class="form-label-cyber">Your Name</label>
                                    <input type="text" name="user_name" class="form-control form-control-cyber" required placeholder="Alex Mercer">
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label-cyber">Rating Score</label>
                                <select name="rating" class="form-select bg-dark text-white border-cyan">
                                    <option value="5">⭐⭐⭐⭐⭐ (5/5 Excellent)</option>
                                    <option value="4">⭐⭐⭐⭐ (4/5 Very Good)</option>
                                    <option value="3">⭐⭐⭐ (3/5 Average)</option>
                                    <option value="2">⭐⭐ (2/5 Poor)</option>
                                    <option value="1">⭐ (1/5 Very Bad)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-cyber">Your Review Comment</label>
                                <textarea name="comment" rows="4" class="form-control form-control-cyber" required placeholder="Share your experience with this gaming gear..."></textarea>
                            </div>

                            <button type="submit" name="submit_review" class="btn-cyan w-100">
                                <i class="fas fa-paper-plane me-2"></i> Submit Review
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
