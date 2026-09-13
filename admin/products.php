<?php
// admin/products.php - Manage Product Catalog
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Handle Product Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $prodId = (int)$_POST['product_id'];

    if ($prodId > 0) {
        try {
            $db->beginTransaction();

            // 1. Clean up cart items referencing this product
            $stmt = $db->prepare("DELETE FROM cart WHERE product_id = ?");
            $stmt->execute([$prodId]);

            // 2. Clean up wishlist items referencing this product
            $stmt = $db->prepare("DELETE FROM wishlist WHERE product_id = ?");
            $stmt->execute([$prodId]);

            // 3. Clean up reviews referencing this product
            $stmt = $db->prepare("DELETE FROM reviews WHERE product_id = ?");
            $stmt->execute([$prodId]);

            // 4. Update order items referencing this product (keep record for invoice history)
            $stmt = $db->prepare("UPDATE order_items SET product_id = NULL WHERE product_id = ?");
            $stmt->execute([$prodId]);

            // 5. Fetch image file path for cleanup
            $imgStmt = $db->prepare("SELECT image, additional_images FROM products WHERE id = ?");
            $imgStmt->execute([$prodId]);
            $prodData = $imgStmt->fetch();

            if ($prodData) {
                // Delete primary image if custom uploaded file
                if (!empty($prodData['image']) && strpos($prodData['image'], 'uploads/') === 0) {
                    $imgPath = __DIR__ . '/../' . $prodData['image'];
                    if (file_exists($imgPath)) {
                        @unlink($imgPath);
                    }
                }
                // Delete additional images if present
                if (!empty($prodData['additional_images'])) {
                    $addImages = json_decode($prodData['additional_images'], true);
                    if (is_array($addImages)) {
                        foreach ($addImages as $addImg) {
                            if (!empty($addImg) && strpos($addImg, 'uploads/') === 0) {
                                $addImgPath = __DIR__ . '/../' . $addImg;
                                if (file_exists($addImgPath)) {
                                    @unlink($addImgPath);
                                }
                            }
                        }
                    }
                }
            }

            // 6. Delete product row
            $delStmt = $db->prepare("DELETE FROM products WHERE id = ?");
            $delStmt->execute([$prodId]);

            $db->commit();
            set_flash('success', 'Product deleted successfully.');
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            set_flash('error', 'Failed to delete product: ' . $e->getMessage());
        }
    }
    header('Location: products.php');
    exit;
}

// Fetch all products
$stmt = $db->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$products = $stmt->fetchAll();

$adminPageTitle = "Product Catalog";
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white font-monospace mb-0"><i class="fas fa-boxes-stacked text-warning me-2"></i> Products (<?php echo count($products); ?>)</h4>
    <a href="product-add.php" class="btn-cyan">
        <i class="fas fa-plus-circle me-2"></i> Add New Product
    </a>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Type</th>
                    <th>Featured</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="font-monospace text-muted">#<?php echo $p['id']; ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="../<?php echo sanitize($p['image'] ?: 'assets/images/placeholder.jpg'); ?>" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;" class="border border-secondary">
                                <div>
                                    <strong class="text-white d-block"><?php echo sanitize($p['name']); ?></strong>
                                    <small class="text-muted font-monospace">SKU: <?php echo sanitize($p['sku'] ?: 'N/A'); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge-cyber"><?php echo sanitize($p['category_name']); ?></span></td>
                        <td class="font-monospace">
                            <?php if ($p['sale_price']): ?>
                                <span class="text-cyan fw-bold"><?php echo format_price($p['sale_price']); ?></span>
                                <small class="text-muted text-decoration-line-through d-block"><?php echo format_price($p['price']); ?></small>
                            <?php else: ?>
                                <span class="text-white"><?php echo format_price($p['price']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['stock'] > 10): ?>
                                <span class="badge bg-success font-monospace"><?php echo $p['stock']; ?></span>
                            <?php elseif ($p['stock'] > 0): ?>
                                <span class="badge bg-warning text-dark font-monospace"><?php echo $p['stock']; ?> Low</span>
                            <?php else: ?>
                                <span class="badge bg-danger font-monospace">Out of Stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['is_digital']): ?>
                                <span class="badge-magenta small"><i class="fas fa-key"></i> Digital</span>
                            <?php else: ?>
                                <span class="text-muted small"><i class="fas fa-box"></i> Physical</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $p['is_featured'] ? '<i class="fas fa-star text-gold"></i>' : '<span class="text-muted">-</span>'; ?>
                        </td>
                        <td class="text-end">
                            <a href="product-edit.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-info me-1" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="products.php" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?');">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" name="delete_product" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
