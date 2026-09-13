<?php
// admin/orders.php - Manage Customer Orders & Fulfillment
$adminPageTitle = "Order Management";
require_once __DIR__ . '/../includes/admin_header.php';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = trim($_POST['order_status']);
    $newPayment = trim($_POST['payment_status']);

    $stmt = $db->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $newPayment, $orderId]);
    set_flash('success', "Order #{$orderId} status updated to " . strtoupper($newStatus));
    header('Location: orders.php');
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];
if (!empty($statusFilter)) {
    $sql .= " AND order_status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="text-white font-monospace mb-0"><i class="fas fa-shopping-bag text-success me-2"></i> Orders & Logistics (<?php echo count($orders); ?>)</h4>
    
    <!-- Filter Status Tabs -->
    <div class="btn-group">
        <a href="orders.php" class="btn btn-sm btn-outline-cyan <?php echo empty($statusFilter) ? 'active' : ''; ?>">All</a>
        <a href="orders.php?status=pending" class="btn btn-sm btn-outline-warning <?php echo $statusFilter == 'pending' ? 'active' : ''; ?>">Pending</a>
        <a href="orders.php?status=processing" class="btn btn-sm btn-outline-info <?php echo $statusFilter == 'processing' ? 'active' : ''; ?>">Processing</a>
        <a href="orders.php?status=shipped" class="btn btn-sm btn-outline-primary <?php echo $statusFilter == 'shipped' ? 'active' : ''; ?>">Shipped</a>
        <a href="orders.php?status=delivered" class="btn btn-sm btn-outline-success <?php echo $statusFilter == 'delivered' ? 'active' : ''; ?>">Delivered</a>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table align-middle">
            <thead>
                <tr>
                    <th>Order No.</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Order Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $ord): ?>
                    <tr>
                        <td class="font-monospace fw-bold text-white"><?php echo sanitize($ord['order_number']); ?></td>
                        <td class="small text-muted"><?php echo date('M d, Y H:i', strtotime($ord['created_at'])); ?></td>
                        <td>
                            <strong class="text-white d-block"><?php echo sanitize($ord['shipping_name']); ?></strong>
                            <small class="text-muted"><?php echo sanitize($ord['shipping_email']); ?></small>
                        </td>
                        <td class="font-monospace text-cyan fw-bold"><?php echo format_price($ord['final_amount']); ?></td>
                        <td>
                            <span class="badge-cyber small mb-1 d-block"><?php echo strtoupper(sanitize($ord['payment_method'])); ?></span>
                            <span class="badge bg-<?php echo $ord['payment_status'] == 'paid' ? 'success' : 'warning text-dark'; ?> font-monospace">
                                <?php echo strtoupper(sanitize($ord['payment_status'])); ?>
                            </span>
                        </td>
                        <td>
                            <form action="orders.php" method="POST" class="d-flex align-items-center gap-1">
                                <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                                <input type="hidden" name="payment_status" value="<?php echo $ord['payment_status']; ?>">
                                <select name="order_status" class="form-select form-select-sm bg-dark text-white border-cyan font-monospace" style="width: 130px;" onchange="this.form.submit()">
                                    <option value="pending" <?php echo $ord['order_status'] == 'pending' ? 'selected' : ''; ?>>PENDING</option>
                                    <option value="processing" <?php echo $ord['order_status'] == 'processing' ? 'selected' : ''; ?>>PROCESSING</option>
                                    <option value="shipped" <?php echo $ord['order_status'] == 'shipped' ? 'selected' : ''; ?>>SHIPPED</option>
                                    <option value="delivered" <?php echo $ord['order_status'] == 'delivered' ? 'selected' : ''; ?>>DELIVERED</option>
                                    <option value="cancelled" <?php echo $ord['order_status'] == 'cancelled' ? 'selected' : ''; ?>>CANCELLED</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td class="text-end">
                            <a href="order-detail.php?id=<?php echo $ord['id']; ?>" class="btn btn-sm btn-cyan">
                                <i class="fas fa-file-invoice me-1"></i> Invoice
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
