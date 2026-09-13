<?php
// profile.php - User Dashboard & Order History
$pageTitle = "My Profile & Orders - HORAA STORE";
require_once __DIR__ . '/includes/header.php';

require_login();
$user = current_user();

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal = trim($_POST['postal_code'] ?? '');

    if (!empty($name)) {
        $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, address = ?, city = ?, postal_code = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $city, $postal, $user['id']]);
        $_SESSION['user_name'] = $name;
        set_flash('success', 'Profile information updated.');
        header('Location: profile.php');
        exit;
    }
}

// Fetch User Orders
$orderStmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$orderStmt->execute([$user['id']]);
$userOrders = $orderStmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <h2 class="text-white mb-0 font-monospace">
        <i class="fas fa-user-gear text-cyan me-2"></i> Gamer Dashboard
    </h2>
    <div>
        <?php if (is_admin()): ?>
            <a href="admin/index.php" class="btn btn-sm btn-outline-warning me-2"><i class="fas fa-gauge-high me-1"></i> Admin Portal</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-sm btn-outline-danger"><i class="fas fa-right-from-bracket me-1"></i> Sign Out</a>
    </div>
</div>

<div class="row g-4 mb-5">
    
    <!-- LEFT: PROFILE CARD -->
    <div class="col-lg-4">
        <div class="bg-card rounded-4 border border-cyan p-4">
            <div class="text-center mb-4">
                <div class="logo-icon mx-auto mb-2" style="width: 70px; height: 70px; font-size: 2rem;">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <h4 class="text-white mb-0 font-monospace"><?php echo sanitize($user['name']); ?></h4>
                <span class="badge-cyber"><?php echo strtoupper(sanitize($user['role'])); ?> ACCOUNT</span>
            </div>

            <form action="profile.php" method="POST">
                <div class="mb-3">
                    <label class="form-label-cyber">Full Name</label>
                    <input type="text" name="name" class="form-control form-control-cyber" required value="<?php echo sanitize($user['name']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label-cyber">Email Address</label>
                    <input type="email" class="form-control form-control-cyber" disabled value="<?php echo sanitize($user['email']); ?>">
                    <small class="text-muted">Email cannot be modified.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label-cyber">Phone</label>
                    <input type="text" name="phone" class="form-control form-control-cyber" value="<?php echo sanitize($user['phone']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label-cyber">Address</label>
                    <input type="text" name="address" class="form-control form-control-cyber" value="<?php echo sanitize($user['address']); ?>">
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-7">
                        <label class="form-label-cyber">City</label>
                        <input type="text" name="city" class="form-control form-control-cyber" value="<?php echo sanitize($user['city']); ?>">
                    </div>
                    <div class="col-5">
                        <label class="form-label-cyber">Postal Code</label>
                        <input type="text" name="postal_code" class="form-control form-control-cyber" value="<?php echo sanitize($user['postal_code']); ?>">
                    </div>
                </div>

                <button type="submit" name="update_profile" class="btn-cyan w-100 justify-content-center">
                    <i class="fas fa-save me-2"></i> Save Profile
                </button>
            </form>
        </div>
    </div>

    <!-- RIGHT: ORDER HISTORY -->
    <div class="col-lg-8">
        <div class="bg-card rounded-4 border border-cyan p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-white font-monospace mb-0"><i class="fas fa-history text-cyan me-2"></i> My Order History</h4>
                <span class="badge bg-secondary font-monospace"><?php echo count($userOrders); ?> Total Orders</span>
            </div>

            <?php if (empty($userOrders)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-box-open fs-1 mb-2"></i>
                    <p>No orders placed yet.</p>
                    <a href="shop.php" class="btn btn-sm btn-outline-cyber">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="text-cyan">Order Number</th>
                                <th class="text-cyan">Date</th>
                                <th class="text-cyan">Amount</th>
                                <th class="text-cyan">Payment</th>
                                <th class="text-cyan">Status</th>
                                <th class="text-cyan text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userOrders as $ord): ?>
                                <tr>
                                    <td class="font-monospace fw-bold text-white"><?php echo sanitize($ord['order_number']); ?></td>
                                    <td class="small text-muted"><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                                    <td class="font-monospace text-cyan fw-bold"><?php echo format_price($ord['final_amount']); ?></td>
                                    <td>
                                        <span class="badge bg-dark border border-secondary text-white small font-monospace me-1"><?php echo strtoupper(sanitize($ord['payment_method'])); ?></span>
                                        <?php if ($ord['payment_status'] === 'paid'): ?>
                                            <span class="badge bg-success font-monospace" style="font-size: 0.65rem;">PAID</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark font-monospace" style="font-size: 0.65rem;">UNPAID</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($ord['order_status'] === 'processing' || $ord['order_status'] === 'completed'): ?>
                                            <span class="badge bg-success font-monospace px-2 py-1"><i class="fas fa-check-circle me-1"></i> <?php echo strtoupper(sanitize($ord['order_status'])); ?></span>
                                        <?php elseif ($ord['order_status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark font-monospace px-2 py-1"><i class="fas fa-clock me-1"></i> PENDING</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary font-monospace px-2 py-1"><?php echo strtoupper(sanitize($ord['order_status'])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <?php if ($ord['payment_status'] === 'pending' && $ord['payment_method'] === 'esewa'): ?>
                                                <a href="checkout.php?pay_order=<?php echo urlencode($ord['order_number']); ?>" class="btn btn-sm btn-success font-monospace fw-bold shadow" style="background-color: #60b333; border: none;">
                                                    <i class="fas fa-wallet me-1"></i> Pay eSewa
                                                </a>
                                            <?php endif; ?>
                                            <a href="order-success.php?order=<?php echo urlencode($ord['order_number']); ?>" class="btn btn-sm btn-outline-cyan">
                                                <i class="fas fa-eye me-1"></i> Receipt
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
