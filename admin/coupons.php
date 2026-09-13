<?php
// admin/coupons.php - Manage Discount Coupons
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Add Coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $type = trim($_POST['discount_type'] ?? 'percent');
    $value = (float)($_POST['discount_value'] ?? 0);
    $minOrder = (float)($_POST['min_order_amount'] ?? 0);
    $limit = (int)($_POST['usage_limit'] ?? 100);
    $expiry = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;

    if (!empty($code) && $value > 0) {
        $ins = $db->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, usage_limit, expiry_date) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->execute([$code, $type, $value, $minOrder, $limit, $expiry]);
        set_flash('success', 'Coupon code generated.');
        header('Location: coupons.php');
        exit;
    }
}

// Delete Coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_coupon'])) {
    $cId = (int)$_POST['coupon_id'];
    $del = $db->prepare("DELETE FROM coupons WHERE id = ?");
    $del->execute([$cId]);
    set_flash('success', 'Coupon removed.');
    header('Location: coupons.php');
    exit;
}

$stmt = $db->query("SELECT * FROM coupons ORDER BY id DESC");
$coupons = $stmt->fetchAll();

$adminPageTitle = "Coupons & Discounts";
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="row g-4 mb-5">
    
    <!-- LEFT: CREATE COUPON FORM -->
    <div class="col-lg-4">
        <div class="admin-card">
            <h5 class="text-magenta mb-3 font-monospace"><i class="fas fa-ticket me-2"></i> Create Coupon Code</h5>

            <form action="coupons.php" method="POST">
                <div class="mb-3">
                    <label class="form-label-cyber">Coupon Code *</label>
                    <input type="text" name="code" class="form-control form-control-cyber" required placeholder="e.g. HORAA20" style="text-transform: uppercase;">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label-cyber">Discount Type</label>
                        <select name="discount_type" class="form-select bg-dark text-white border-cyan">
                            <option value="percent">Percent (%)</option>
                            <option value="fixed">Fixed (NPR)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label-cyber">Value *</label>
                        <input type="number" step="0.01" name="discount_value" class="form-control form-control-cyber" required placeholder="10.00">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-cyber">Min Order Threshold (NPR)</label>
                    <input type="number" step="0.01" name="min_order_amount" class="form-control form-control-cyber" value="50.00">
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <label class="form-label-cyber">Usage Limit</label>
                        <input type="number" name="usage_limit" class="form-control form-control-cyber" value="500">
                    </div>
                    <div class="col-6">
                        <label class="form-label-cyber">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control form-control-cyber" value="2026-12-31">
                    </div>
                </div>

                <button type="submit" name="add_coupon" class="btn-magenta w-100 justify-content-center">
                    <i class="fas fa-plus-circle me-2"></i> Generate Voucher
                </button>
            </form>
        </div>
    </div>

    <!-- RIGHT: COUPON LIST TABLE -->
    <div class="col-lg-8">
        <div class="admin-card">
            <h5 class="text-white mb-3 font-monospace"><i class="fas fa-tags text-cyan me-2"></i> Active Discount Coupons (<?php echo count($coupons); ?>)</h5>

            <div class="table-responsive">
                <table class="admin-table align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Min Order</th>
                            <th>Usage</th>
                            <th>Expiry</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $c): ?>
                            <tr>
                                <td class="font-monospace fw-bold text-magenta"><?php echo sanitize($c['code']); ?></td>
                                <td class="font-monospace text-cyan fw-bold">
                                    <?php echo $c['discount_type'] === 'percent' ? $c['discount_value'] . '%' : format_price($c['discount_value']); ?>
                                </td>
                                <td class="font-monospace"><?php echo format_price($c['min_order_amount']); ?></td>
                                <td>
                                    <span class="badge-cyber"><?php echo $c['times_used']; ?> / <?php echo $c['usage_limit'] ?: '∞'; ?> used</span>
                                </td>
                                <td class="small text-muted font-monospace"><?php echo $c['expiry_date'] ?: 'No Expiry'; ?></td>
                                <td class="text-end">
                                    <form action="coupons.php" method="POST" class="d-inline" onsubmit="return confirm('Delete coupon?');">
                                        <input type="hidden" name="coupon_id" value="<?php echo $c['id']; ?>">
                                        <button type="submit" name="delete_coupon" class="btn btn-sm btn-outline-danger">
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
