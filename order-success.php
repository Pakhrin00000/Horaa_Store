<?php
// order-success.php - Order Receipt & Confirmation
$pageTitle = "Order Confirmed - HORAA STORE";
require_once __DIR__ . '/includes/header.php';

$esewaRefCode = null;

// Handle eSewa v2 Callback Return Data (?data=BASE64_JSON)
if (isset($_GET['data']) && !empty($_GET['data'])) {
    $encodedData = trim($_GET['data']);
    $jsonStr = base64_decode($encodedData);
    $esewaData = json_decode($jsonStr, true);

    if (is_array($esewaData) && isset($esewaData['transaction_uuid'])) {
        $transUuid = $esewaData['transaction_uuid'];
        $transCode = $esewaData['transaction_code'] ?? '';
        $transStatus = $esewaData['status'] ?? '';
        $transTotal = $esewaData['total_amount'] ?? 0;

        // Perform eSewa Transaction Status Check API verification
        $statusApiResult = esewa_check_status($transTotal, $transUuid);
        $finalStatus = isset($statusApiResult['status']) ? $statusApiResult['status'] : $transStatus;

        if ($finalStatus === 'COMPLETE' || $transStatus === 'COMPLETE') {
            $updStmt = $db->prepare("UPDATE orders SET payment_status = 'paid', order_status = 'processing', order_notes = CONCAT(IFNULL(order_notes, ''), ' [eSewa Ref: ', ?, ']') WHERE order_number = ?");
            $updStmt->execute([$transCode, $transUuid]);
            $esewaRefCode = $transCode;
            set_flash('success', 'eSewa ePay payment completed successfully! Ref Code: ' . sanitize($transCode));
        } else {
            set_flash('warning', 'eSewa transaction status: ' . sanitize($finalStatus));
        }
        $orderNumber = $transUuid;
    }
} else {
    $orderNumber = isset($_GET['order']) ? trim($_GET['order']) : '';
}

if (empty($orderNumber)) {
    header('Location: shop.php');
    exit;
}

// Fetch Order
$stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'Order not found.');
    header('Location: shop.php');
    exit;
}

// Fetch Order Items
$itemStmt = $db->prepare("SELECT oi.*, p.is_digital, p.digital_file_url FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$itemStmt->execute([$order['id']]);
$items = $itemStmt->fetchAll();

$hasDigital = false;
foreach ($items as $it) {
    if ($it['is_digital']) {
        $hasDigital = true;
        break;
    }
}
?>


<div class="mx-auto text-center py-2 mb-5" style="max-width: 800px;">
    
    <div class="text-start mb-3">
        <h5 class="text-white-50 font-monospace fst-italic" style="font-size: 1.1rem; letter-spacing: 0.5px;">Payment Confirmation</h5>
    </div>

    <!-- ESEWA PAYMENT SUCCESSFUL HERO CARD MATCHING BOTTOM SCREENSHOT -->
    <div class="p-4 p-md-5 rounded-4 text-center mb-5" style="background: #17181c; border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 15px 40px rgba(0, 0, 0, 0.65);">
        <div class="d-inline-flex align-items-center justify-content-center text-dark rounded-circle mb-4 shadow" style="width: 72px; height: 72px; background-color: #20c997;">
            <i class="fas fa-check fs-1 text-dark" style="font-weight: 900;"></i>
        </div>
        <h1 class="text-white fw-bold mb-2 fs-2" style="font-family: 'Plus Jakarta Sans', sans-serif;">Payment Successful!</h1>
        <p class="text-secondary fs-6 mb-4">Your order has been confirmed. Thank you for shopping with HORAA ESPORTS</p>
        
        <div class="mb-4 text-white">
            <span class="text-secondary small">Order Reference:</span>
            <strong class="font-monospace text-white fs-6 ms-1"><?php echo sanitize($order['order_number']); ?></strong>
        </div>

        <div class="d-flex justify-content-center align-items-center gap-3">
            <a href="profile.php" class="btn px-4 py-2 fw-semibold text-white shadow" style="background-color: #20c997; border: none; border-radius: 6px; font-size: 0.95rem;">
                View My Orders
            </a>
            <a href="shop.php" class="btn px-4 py-2 fw-semibold text-white border-secondary border-opacity-50" style="background: rgba(255,255,255,0.05); border-radius: 6px; font-size: 0.95rem;">
                Continue Shopping
            </a>
        </div>
    </div>

    <!-- DIGITAL DOWNLOAD KEYS SECTION IF INCLUDED -->
    <?php if ($hasDigital): ?>
        <div class="p-4 bg-card rounded-4 border border-magenta mb-4 text-start">
            <h4 class="text-magenta font-monospace mb-3"><i class="fas fa-bolt me-2"></i> Your Instant Digital Products</h4>
            <div class="alert alert-dark border-magenta text-white">
                <i class="fas fa-info-circle text-magenta me-2"></i> Below are your digital activation licenses and download packages:
            </div>
            
            <div class="list-group">
                <?php foreach ($items as $it): ?>
                    <?php if ($it['is_digital']): ?>
                        <div class="list-group-item bg-dark border-secondary text-white d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-1 text-cyan fw-bold"><?php echo sanitize($it['product_name']); ?></h6>
                                <span class="badge-cyber font-monospace">KEY: HORAA-<?php echo strtoupper(bin2hex(random_bytes(4))); ?>-ACTIVATED</span>
                            </div>
                            <a href="<?php echo sanitize($it['digital_file_url'] ?: 'shop.php'); ?>" target="_blank" class="btn btn-sm btn-magenta">
                                <i class="fas fa-download me-1"></i> Access File
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- ORDER SUMMARY BREAKDOWN CARD -->
    <div class="p-4 bg-card rounded-4 border border-secondary border-opacity-25 text-start mb-4" style="background: #17181c;">
        <h5 class="text-success font-monospace mb-3 pb-2 border-bottom border-secondary border-opacity-25"><i class="fas fa-receipt me-2"></i> Order Breakdown</h5>

        <div class="table-responsive mb-3">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-secondary">Item</th>
                        <th class="text-secondary">Price</th>
                        <th class="text-secondary">Qty</th>
                        <th class="text-secondary text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?php echo sanitize($it['product_name']); ?></td>
                            <td class="font-monospace"><?php echo format_price($it['price']); ?></td>
                            <td><?php echo $it['quantity']; ?></td>
                            <td class="font-monospace text-end text-success fw-bold"><?php echo format_price($it['total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row pt-2">
            <div class="col-md-6 mb-3 mb-md-0">
                <strong class="text-secondary d-block small">SHIPPING ADDRESS</strong>
                <div class="text-white small lh-sm mt-1">
                    <strong><?php echo sanitize($order['shipping_name']); ?></strong><br>
                    <?php echo sanitize($order['shipping_address']); ?><br>
                    <?php echo sanitize($order['shipping_city']); ?>, <?php echo sanitize($order['shipping_postal']); ?><br>
                    Phone: <?php echo sanitize($order['shipping_phone']); ?>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <strong class="text-secondary d-block small">PAYMENT METHOD & TOTAL</strong>
                <div class="text-white small mt-1">
                    Method: <strong class="text-success font-monospace uppercase"><i class="fas fa-wallet text-success me-1"></i> <?php echo strtoupper(sanitize($order['payment_method'])); ?></strong><br>
                    Payment Status: <strong class="text-success"><?php echo strtoupper(sanitize($order['payment_status'])); ?></strong><br>
                    <?php if (!empty($esewaRefCode) || strpos($order['order_notes'] ?? '', 'eSewa Ref:') !== false): ?>
                        <span class="badge bg-success font-monospace my-1"><i class="fas fa-check-circle me-1"></i> eSewa Ref: <?php echo sanitize($esewaRefCode ?: 'VERIFIED'); ?></span><br>
                    <?php endif; ?>
                    <span class="fs-4 text-success font-monospace fw-bold d-block mt-1">Total: <?php echo format_price($order['final_amount']); ?></span>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- CUSTOM FOOTER FOR ORDER SUCCESS -->
<footer class="text-center py-4 border-top border-secondary border-opacity-25 mt-5 w-100" style="background: #111317;">
    <div class="container text-secondary small">
        <div class="fw-bold text-white fs-5 font-monospace mb-1">HORAA ESPORTS</div>
        <p class="text-secondary mb-2" style="font-size: 0.85rem;">Your Premium Gaming Gear Hub</p>
        <p class="mb-0 text-secondary opacity-75" style="font-size: 0.78rem;">&copy; 2026 HORAA ESPORTS Project. Built for academic purposes.</p>
    </div>
</footer>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
