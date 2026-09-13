<?php
// admin/categories.php - Manage Product Categories
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fas fa-gamepad');
    $description = trim($_POST['description'] ?? '');

    if (!empty($name)) {
        $slug = slugify($name);
        $ins = $db->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
        $ins->execute([$name, $slug, $description, $icon]);
        set_flash('success', 'Category added.');
        header('Location: categories.php');
        exit;
    }
}

// Delete Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $catId = (int)$_POST['category_id'];
    if ($catId > 0) {
        try {
            $db->beginTransaction();

            // Fetch products in this category
            $pStmt = $db->prepare("SELECT id, image, additional_images FROM products WHERE category_id = ?");
            $pStmt->execute([$catId]);
            $catProducts = $pStmt->fetchAll();

            foreach ($catProducts as $prod) {
                $pId = (int)$prod['id'];

                // Clean up cart, wishlist, reviews
                $db->prepare("DELETE FROM cart WHERE product_id = ?")->execute([$pId]);
                $db->prepare("DELETE FROM wishlist WHERE product_id = ?")->execute([$pId]);
                $db->prepare("DELETE FROM reviews WHERE product_id = ?")->execute([$pId]);

                // Update order_items reference
                $db->prepare("UPDATE order_items SET product_id = NULL WHERE product_id = ?")->execute([$pId]);

                // Unlink images if uploaded
                if (!empty($prod['image']) && strpos($prod['image'], 'uploads/') === 0) {
                    $imgPath = __DIR__ . '/../' . $prod['image'];
                    if (file_exists($imgPath)) @unlink($imgPath);
                }
                if (!empty($prod['additional_images'])) {
                    $addImages = json_decode($prod['additional_images'], true);
                    if (is_array($addImages)) {
                        foreach ($addImages as $addImg) {
                            if (!empty($addImg) && strpos($addImg, 'uploads/') === 0) {
                                $addImgPath = __DIR__ . '/../' . $addImg;
                                if (file_exists($addImgPath)) @unlink($addImgPath);
                            }
                        }
                    }
                }
            }

            // Delete products in category
            $delProds = $db->prepare("DELETE FROM products WHERE category_id = ?");
            $delProds->execute([$catId]);

            // Delete category
            $del = $db->prepare("DELETE FROM categories WHERE id = ?");
            $del->execute([$catId]);

            $db->commit();
            set_flash('success', 'Category and its products deleted successfully.');
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            set_flash('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }
    header('Location: categories.php');
    exit;
}

$stmt = $db->query("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id ASC");
$categories = $stmt->fetchAll();

$adminPageTitle = "Manage Categories";
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="row g-4 mb-5">
    
    <!-- LEFT: ADD CATEGORY FORM -->
    <div class="col-lg-4">
        <div class="admin-card">
            <h5 class="text-cyan mb-3 font-monospace"><i class="fas fa-plus-circle me-2"></i> Add New Category</h5>
            
            <form action="categories.php" method="POST">
                <div class="mb-3">
                    <label class="form-label-cyber">Category Name *</label>
                    <input type="text" name="name" class="form-control form-control-cyber" required placeholder="e.g. VR Headsets">
                </div>

                <div class="mb-3">
                    <label class="form-label-cyber">FontAwesome Icon Class</label>
                    <input type="text" name="icon" class="form-control form-control-cyber" value="fas fa-vr-cardboard" placeholder="fas fa-gamepad">
                </div>

                <div class="mb-4">
                    <label class="form-label-cyber">Description</label>
                    <textarea name="description" rows="3" class="form-control form-control-cyber" placeholder="Category brief overview..."></textarea>
                </div>

                <button type="submit" name="add_category" class="btn-cyan w-100 justify-content-center">
                    <i class="fas fa-save me-2"></i> Create Category
                </button>
            </form>
        </div>
    </div>

    <!-- RIGHT: CATEGORIES LIST TABLE -->
    <div class="col-lg-8">
        <div class="admin-card">
            <h5 class="text-white mb-3 font-monospace"><i class="fas fa-layer-group text-cyan me-2"></i> Active Categories (<?php echo count($categories); ?>)</h5>

            <div class="table-responsive">
                <table class="admin-table align-middle">
                    <thead>
                        <tr>
                            <th>Icon</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="fs-4 text-cyan"><i class="<?php echo sanitize($cat['icon']); ?>"></i></td>
                                <td class="fw-bold text-white"><?php echo sanitize($cat['name']); ?></td>
                                <td class="font-monospace text-muted small"><?php echo sanitize($cat['slug']); ?></td>
                                <td><span class="badge-cyber"><?php echo $cat['product_count']; ?> Products</span></td>
                                <td class="text-end">
                                    <form action="categories.php" method="POST" class="d-inline" onsubmit="return confirm('Delete category? Products in this category will be affected.');">
                                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                        <button type="submit" name="delete_category" class="btn btn-sm btn-outline-danger">
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
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
