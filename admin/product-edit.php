<?php
// admin/product-edit.php - Edit Existing Product
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: products.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Product not found.');
    header('Location: products.php');
    exit;
}

$catStmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

$specs = !empty($product['specifications']) ? json_decode($product['specifications'], true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $skuInput = trim($_POST['sku'] ?? '');
    $sku = !empty($skuInput) ? $skuInput : ('HOR-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)));
    $shortDesc = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isTrending = isset($_POST['is_trending']) ? 1 : 0;
    $isDigital = isset($_POST['is_digital']) ? 1 : 0;
    $digitalFileUrl = trim($_POST['digital_file_url'] ?? '');

    // Process Specs JSON
    $specKeys = $_POST['spec_keys'] ?? [];
    $specValues = $_POST['spec_values'] ?? [];
    $specsArray = [];
    for ($i = 0; $i < count($specKeys); $i++) {
        $k = trim($specKeys[$i]);
        $v = trim($specValues[$i]);
        if (!empty($k) && !empty($v)) {
            $specsArray[$k] = $v;
        }
    }
    $specsJson = !empty($specsArray) ? json_encode($specsArray) : null;

    $imagePath = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $newFileName = 'prod_' . time() . '_' . rand(100, 999) . '.' . $fileExt;
        $targetFile = $uploadDir . $newFileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = 'uploads/' . $newFileName;
        }
    }

    if (empty($name) || $categoryId <= 0 || $price <= 0) {
        set_flash('error', 'Please fill in product name, category, and price.');
    } else {
        $upd = $db->prepare("UPDATE products SET category_id = ?, name = ?, short_description = ?, description = ?, specifications = ?, price = ?, sale_price = ?, stock = ?, sku = ?, image = ?, is_featured = ?, is_trending = ?, is_digital = ?, digital_file_url = ? WHERE id = ?");
        $upd->execute([
            $categoryId, $name, $shortDesc, $description, $specsJson,
            $price, $salePrice, $stock, $sku, $imagePath, $isFeatured, $isTrending, $isDigital, $digitalFileUrl, $id
        ]);

        set_flash('success', 'Product updated successfully.');
        header('Location: products.php');
        exit;
    }
}

$adminPageTitle = "Edit Product";
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white font-monospace mb-0"><i class="fas fa-edit text-info me-2"></i> Edit Product #<?php echo $product['id']; ?></h4>
    <a href="products.php" class="btn btn-sm btn-outline-cyan"><i class="fas fa-arrow-left me-1"></i> Back to Catalog</a>
</div>

<form action="product-edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
    <div class="row g-4 mb-5">
        
        <div class="col-lg-8">
            <div class="admin-card">
                <h5 class="text-cyan mb-4 font-monospace">Basic Details</h5>

                <div class="mb-3">
                    <label class="form-label-cyber">Product Name *</label>
                    <input type="text" name="name" class="form-control form-control-cyber" required value="<?php echo sanitize($product['name']); ?>">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label-cyber">Category *</label>
                        <select name="category_id" class="form-select bg-dark text-white border-cyan" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitize($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-cyber">SKU Code</label>
                        <input type="text" name="sku" class="form-control form-control-cyber" value="<?php echo sanitize($product['sku']); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-cyber">Short Description</label>
                    <textarea name="short_description" rows="2" class="form-control form-control-cyber"><?php echo sanitize($product['short_description']); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label-cyber">Full Description</label>
                    <textarea name="description" rows="5" class="form-control form-control-cyber"><?php echo sanitize($product['description']); ?></textarea>
                </div>

                <!-- Dynamic Specs Builder -->
                <h5 class="text-cyan mb-3 font-monospace">Technical Specifications</h5>
                <div id="spec-pairs-container">
                    <?php if (is_array($specs) && !empty($specs)): ?>
                        <?php foreach ($specs as $k => $v): ?>
                            <div class="row g-2 mb-2 spec-row">
                                <div class="col-5">
                                    <input type="text" name="spec_keys[]" class="form-control form-control-cyber" value="<?php echo sanitize($k); ?>">
                                </div>
                                <div class="col-6">
                                    <input type="text" name="spec_values[]" class="form-control form-control-cyber" value="<?php echo sanitize($v); ?>">
                                </div>
                                <div class="col-1 text-end">
                                    <button type="button" class="btn btn-outline-danger btn-remove-spec"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="row g-2 mb-2 spec-row">
                            <div class="col-5">
                                <input type="text" name="spec_keys[]" class="form-control form-control-cyber" placeholder="Key">
                            </div>
                            <div class="col-6">
                                <input type="text" name="spec_values[]" class="form-control form-control-cyber" placeholder="Value">
                            </div>
                            <div class="col-1 text-end">
                                <button type="button" class="btn btn-outline-danger btn-remove-spec"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" id="btn-add-spec" class="btn btn-sm btn-outline-info mt-2">
                    <i class="fas fa-plus me-1"></i> Add Spec Row
                </button>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-card">
                <h5 class="text-cyan mb-4 font-monospace">Pricing & Stock</h5>

                <div class="mb-3">
                    <label class="form-label-cyber">Regular Price (NPR) *</label>
                    <input type="number" step="0.01" name="price" class="form-control form-control-cyber" required value="<?php echo $product['price']; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label-cyber">Sale Price (NPR)</label>
                    <input type="number" step="0.01" name="sale_price" class="form-control form-control-cyber" value="<?php echo $product['sale_price']; ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label-cyber">Stock Inventory Qty *</label>
                    <input type="number" name="stock" class="form-control form-control-cyber" required value="<?php echo $product['stock']; ?>">
                </div>

                <h5 class="text-cyan mb-3 font-monospace">Product Image</h5>
                <div class="mb-3">
                    <img src="../<?php echo sanitize($product['image'] ?: 'assets/images/placeholder.jpg'); ?>" style="width: 100px; height: 100px; object-fit: cover;" class="rounded-3 border border-cyan mb-2">
                    <input type="file" name="image" class="form-control bg-dark text-white border-cyan" accept="image/*">
                </div>

                <h5 class="text-cyan mb-3 font-monospace">Flags & Type</h5>
                <div class="form-check mb-2">
                    <input class="form-check-input bg-dark border-cyan" type="checkbox" name="is_featured" id="chkFeatured" value="1" <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                    <label class="form-check-label text-white" for="chkFeatured">Feature on Homepage</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input bg-dark border-magenta" type="checkbox" name="is_digital" id="chkDigital" value="1" <?php echo $product['is_digital'] ? 'checked' : ''; ?>>
                    <label class="form-check-label text-magenta fw-bold" for="chkDigital">Is Digital Download Product</label>
                </div>

                <div class="mb-4">
                    <label class="form-label-cyber">Digital File URL / Key Link</label>
                    <input type="text" name="digital_file_url" class="form-control form-control-cyber" value="<?php echo sanitize($product['digital_file_url']); ?>">
                </div>

                <button type="submit" name="update_product" class="btn-cyan w-100 py-3 justify-content-center">
                    <i class="fas fa-save me-2"></i> Update Product
                </button>
            </div>
        </div>

    </div>
</form>

<script src="../assets/js/admin.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
