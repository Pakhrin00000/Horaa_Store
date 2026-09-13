<?php
// admin/users.php - Manage Customer Accounts
$adminPageTitle = "Customer Accounts";
require_once __DIR__ . '/../includes/admin_header.php';

// Toggle Role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_role'])) {
    $uId = (int)$_POST['user_id'];
    $newRole = trim($_POST['new_role']);
    $upd = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
    $upd->execute([$newRole, $uId]);
    set_flash('success', 'User role updated.');
    header('Location: users.php');
    exit;
}

$stmt = $db->query("SELECT u.*, COUNT(o.id) AS total_orders FROM users u LEFT JOIN orders o ON u.id = o.user_id GROUP BY u.id ORDER BY u.id ASC");
$users = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white font-monospace mb-0"><i class="fas fa-users text-primary me-2"></i> Registered Accounts (<?php echo count($users); ?>)</h4>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Role</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="font-monospace text-muted">#<?php echo $u['id']; ?></td>
                        <td class="fw-bold text-white"><?php echo sanitize($u['name']); ?></td>
                        <td class="font-monospace text-cyan"><?php echo sanitize($u['email']); ?></td>
                        <td class="small text-muted"><?php echo sanitize($u['phone'] ?: 'N/A'); ?></td>
                        <td><span class="badge-cyber"><?php echo $u['total_orders']; ?> Orders</span></td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge bg-warning text-dark font-monospace"><i class="fas fa-shield-halved me-1"></i> ADMIN</span>
                            <?php else: ?>
                                <span class="badge bg-secondary font-monospace">USER</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <form action="users.php" method="POST" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <input type="hidden" name="new_role" value="<?php echo $u['role'] === 'admin' ? 'user' : 'admin'; ?>">
                                    <button type="submit" name="toggle_role" class="btn btn-sm btn-outline-warning">
                                        Toggle <?php echo $u['role'] === 'admin' ? 'to User' : 'to Admin'; ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">Current Admin</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
