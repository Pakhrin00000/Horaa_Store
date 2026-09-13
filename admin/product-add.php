<?php
// admin/product-add.php - Add Product Form
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Fetch Categories
$catStmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
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

    // Handle Image Upload
    $imagePath = 'assets/images/placeholder.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $newFileName = 'prod_' . time() . '_' . rand(100, 999) . '.' . $fileExt;
        $targetFile = $uploadDir . $newFileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = 'uploads/' . $newFileName;
        }
    }

    $slug = slugify($name) . '-' . time();

    if (empty($name) || $categoryId <= 0 || $price <= 0) {
        set_flash('error', 'Please fill in product name, category, and price.');
    } else {
        $ins = $db->prepare("INSERT INTO products (category_id, name, slug, short_description, description, specifications, price, sale_price, stock, sku, image, is_featured, is_trending, is_digital, digital_file_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([
            $categoryId, $name, $slug, $shortDesc, $description, $specsJson,
            $price, $salePrice, $stock, $sku, $imagePath, $isFeatured, $isTrending, $isDigital, $digitalFileUrl
        ]);

        set_flash('success', 'New product added successfully!');
        header('Location: products.php');
        exit;
    }
}

$adminPageTitle = "Add New Product";
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white font-monospace mb-0"><i class="fas fa-plus-circle text-cyan me-2"></i> Add New Product</h4>
    <a href="products.php" class="btn btn-sm btn-outline-cyan"><i class="fas fa-arrow-left me-1"></i> Back to Catalog</a>
</div>

<form action="product-add.php" method="POST" enctype="multipart/form-data">
    <div class="row g-4 mb-5">
        
        <div class="col-lg-8">
            <div class="admin-card">
                <h5 class="text-cyan mb-4 font-monospace">Basic Details</h5>

                <div class="mb-3">
                    <label class="form-label-cyber">Product Name *</label>
                    <input type="text" name="name" class="form-control form-control-cyber" required placeholder="e.g. HORAA Apex Pro RGB Mechanical Keyboard">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label-cyber">Category *</label>
                        <select name="category_id" class="form-select bg-dark text-white border-cyan" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo sanitize($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-cyber">SKU Code</label>
                        <input type="text" name="sku" class="form-control form-control-cyber" placeholder="HOR-KB-101">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-cyber">Short Description</label>
                    <textarea name="short_description" rows="2" class="form-control form-control-cyber" placeholder="Brief summary displayed on cards..."></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label-cyber">Full Description</label>
                    <textarea name="description" rows="5" class="form-control form-control-cyber" placeholder="Detailed product specifications and info..."></textarea>
                </div>

                <!-- Dynamic Specs Builder -->
                <h5 class="text-cyan mb-3 font-monospace">Technical Specifications</h5>
                <div id="spec-pairs-container">
                    <div class="row g-2 mb-2 spec-row">
                        <div class="col-5">
                            <input type="text" name="spec_keys[]" class="form-control form-control-cyber" placeholder="Key (e.g. Material)">
                        </div>
                        <div class="col-6">
                            <input type="text" name="spec_values[]" class="form-control form-control-cyber" placeholder="Value (e.g. Aluminum Alloy)">
                        </div>
                        <div class="col-1 text-end">
                            <button type="button" class="btn btn-outline-danger btn-remove-spec"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
                <button type="button" id="btn-add-spec" class="btn btn-sm btn-outline-info mt-2">
                    <i class="fas fa-plus me-1"></i> Add Spec Row
                </button>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-card">
                <h5 class="text-cyan mb-4 font-monospace">Pricing & Inventory</h5>

                <div class="mb-3">
                    <label class="form-label-cyber">Regular Price (NPR) *</label>
                    <input type="number" step="0.01" name="price" class="form-control form-control-cyber" required placeholder="149.99">
                </div>

                <div class="mb-3">
                    <label class="form-label-cyber">Sale Price (NPR)</label>
                    <input type="number" step="0.01" name="sale_price" class="form-control form-control-cyber" placeholder="129.99 (Optional)">
                </div>

                <div class="mb-4">
                    <label class="form-label-cyber">Stock Inventory Qty *</label>
                    <input type="number" name="stock" class="form-control form-control-cyber" required value="50">
                </div>

                <h5 class="text-cyan mb-3 font-monospace">Product Image</h5>
                <div class="mb-4">
                    <input type="file" name="image" class="form-control bg-dark text-white border-cyan" accept="image/*">
                </div>

                <h5 class="text-cyan mb-3 font-monospace">Flags & Type</h5>
                <div class="form-check mb-2">
                    <input class="form-check-input bg-dark border-cyan" type="checkbox" name="is_featured" id="chkFeatured" value="1">
                    <label class="form-check-label text-white" for="chkFeatured">Feature on Homepage</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input bg-dark border-magenta" type="checkbox" name="is_digital" id="chkDigital" value="1">
                    <label class="form-check-label text-magenta fw-bold" for="chkDigital">Is Digital Download Product</label>
                </div>

                <div class="mb-4">
                    <label class="form-label-cyber">Digital File URL / License Key Link</label>
                    <input type="text" name="digital_file_url" class="form-control form-control-cyber" placeholder="https://horaa.store/downloads/key.pdf">
                </div>

                <button type="submit" name="save_product" class="btn-cyan w-100 py-3 justify-content-center">
                    <i class="fas fa-save me-2"></i> Save & Publish Product
                </button>
            </div>
        </div>

    </div>
</form>

<script src="../assets/js/admin.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
