<?php
// admin/order-detail.php - Order Inspector & Printable Invoice
$adminPageTitle = "Order Details";
require_once __DIR__ . '/../includes/admin_header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: orders.php');
    exit;
}

// Fetch Order
$stmt = $db->prepare("SELECT o.*, u.name AS account_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'Order not found.');
    header('Location: orders.php');
    exit;
}

// Fetch Order Items
$itemStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->execute([$id]);
$items = $itemStmt->fetchAll();

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_full'])) {
    $orderStatus = trim($_POST['order_status']);
    $paymentStatus = trim($_POST['payment_status']);

    $upd = $db->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
    $upd->execute([$orderStatus, $paymentStatus, $id]);
    set_flash('success', 'Order status updated.');
    header("Location: order-detail.php?id={$id}");
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="text-white font-monospace mb-0">INVOICE: <?php echo sanitize($order['order_number']); ?></h4>
        <small class="text-muted">Placed on <?php echo date('F d, Y \a\t H:i', strtotime($order['created_at'])); ?></small>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-sm btn-outline-cyan"><i class="fas fa-print me-1"></i> Print Invoice</button>
        <a href="orders.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Orders</a>
    </div>
</div>

<div class="row g-4 mb-5">
    
    <div class="col-lg-8">
        <div class="admin-card">
            <h5 class="text-cyan mb-3 font-monospace">Itemized Purchased Products</h5>
            
            <table class="admin-table align-middle mb-3">
                <thead>
                    <tr>
                        <th>Product Item</th>
                        <th>Unit Price</th>
                        <th>Qty</th>
                        <th class="text-end">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td class="fw-bold text-white"><?php echo sanitize($it['product_name']); ?></td>
                            <td class="font-monospace"><?php echo format_price($it['price']); ?></td>
                            <td class="font-monospace"><?php echo $it['quantity']; ?></td>
                            <td class="font-monospace text-cyan fw-bold text-end"><?php echo format_price($it['total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="border-top border-secondary pt-3 mt-3">
                <div class="row text-white font-monospace">
                    <div class="col-6">Subtotal:</div>
                    <div class="col-6 text-end"><?php echo format_price($order['total_amount']); ?></div>
                    <?php if ($order['discount_amount'] > 0): ?>
                        <div class="col-6 text-magenta">Voucher Discount:</div>
                        <div class="col-6 text-end text-magenta">-<?php echo format_price($order['discount_amount']); ?></div>
                    <?php endif; ?>
                    <div class="col-6">Shipping Fee:</div>
                    <div class="col-6 text-end"><?php echo format_price($order['shipping_fee']); ?></div>
                    <div class="col-6 fs-5 fw-bold text-cyan mt-2">Grand Total:</div>
                    <div class="col-6 fs-4 fw-bold text-cyan text-end mt-2"><?php echo format_price($order['final_amount']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="admin-card mb-4">
            <h5 class="text-cyan mb-3 font-monospace">Shipping & Customer</h5>
            <div class="text-white small mb-3">
                <strong>Name:</strong> <?php echo sanitize($order['shipping_name']); ?><br>
                <strong>Email:</strong> <?php echo sanitize($order['shipping_email']); ?><br>
                <strong>Phone:</strong> <?php echo sanitize($order['shipping_phone']); ?><br>
                <strong>City:</strong> <?php echo sanitize($order['shipping_city']); ?>, <?php echo sanitize($order['shipping_postal']); ?><br>
                <strong>Address:</strong> <?php echo sanitize($order['shipping_address']); ?>
            </div>
            <?php if (!empty($order['order_notes'])): ?>
                <div class="p-2 bg-dark rounded border border-secondary text-muted small">
                    <strong>Notes:</strong> <?php echo sanitize($order['order_notes']); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-card">
            <h5 class="text-cyan mb-3 font-monospace">Update Order Status</h5>
            <form action="order-detail.php?id=<?php echo $id; ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label-cyber">Order Fulfillment Status</label>
                    <select name="order_status" class="form-select bg-dark text-white border-cyan font-monospace">
                        <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>PENDING</option>
                        <option value="processing" <?php echo $order['order_status'] == 'processing' ? 'selected' : ''; ?>>PROCESSING</option>
                        <option value="shipped" <?php echo $order['order_status'] == 'shipped' ? 'selected' : ''; ?>>SHIPPED</option>
                        <option value="delivered" <?php echo $order['order_status'] == 'delivered' ? 'selected' : ''; ?>>DELIVERED</option>
                        <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>CANCELLED</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label-cyber">Payment Status</label>
                    <select name="payment_status" class="form-select bg-dark text-white border-cyan font-monospace">
                        <option value="pending" <?php echo $order['payment_status'] == 'pending' ? 'selected' : ''; ?>>PENDING</option>
                        <option value="paid" <?php echo $order['payment_status'] == 'paid' ? 'selected' : ''; ?>>PAID</option>
                        <option value="failed" <?php echo $order['payment_status'] == 'failed' ? 'selected' : ''; ?>>FAILED</option>
                    </select>
                </div>

                <button type="submit" name="update_order_full" class="btn-cyan w-100 justify-content-center">
                    <i class="fas fa-save me-2"></i> Update Order
                </button>
            </form>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
