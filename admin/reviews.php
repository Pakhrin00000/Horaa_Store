<?php
// admin/reviews.php - Moderate Customer Reviews
$adminPageTitle = "Product Reviews";
require_once __DIR__ . '/../includes/admin_header.php';

// Handle Moderation Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_review_status'])) {
    $revId = (int)$_POST['review_id'];
    $newStatus = trim($_POST['status']);
    
    if ($newStatus === 'delete') {
        $del = $db->prepare("DELETE FROM reviews WHERE id = ?");
        $del->execute([$revId]);
        set_flash('success', 'Review deleted.');
    } else {
        $upd = $db->prepare("UPDATE reviews SET status = ? WHERE id = ?");
        $upd->execute([$newStatus, $revId]);
        set_flash('success', 'Review status updated.');
    }
    header('Location: reviews.php');
    exit;
}

$stmt = $db->query("SELECT r.*, p.name AS product_name FROM reviews r JOIN products p ON r.product_id = p.id ORDER BY r.id DESC");
$reviews = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white font-monospace mb-0"><i class="fas fa-star text-gold me-2"></i> Product Reviews (<?php echo count($reviews); ?>)</h4>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table align-middle">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>User</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $rev): ?>
                    <tr>
                        <td>
                            <a href="../product.php?id=<?php echo $rev['product_id']; ?>" target="_blank" class="text-cyan fw-bold text-decoration-none">
                                <?php echo sanitize($rev['product_name']); ?>
                            </a>
                        </td>
                        <td class="text-white fw-bold"><?php echo sanitize($rev['user_name']); ?></td>
                        <td>
                            <div class="text-gold small">
                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <i class="fas fa-star<?php echo $s <= $rev['rating'] ? '' : '-half-alt opacity-25'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </td>
                        <td class="small text-muted" style="max-width: 250px;"><?php echo sanitize($rev['comment']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $rev['status'] === 'approved' ? 'success' : ($rev['status'] === 'pending' ? 'warning text-dark' : 'danger'); ?> font-monospace">
                                <?php echo strtoupper(sanitize($rev['status'])); ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <form action="reviews.php" method="POST" class="d-inline">
                                <input type="hidden" name="review_id" value="<?php echo $rev['id']; ?>">
                                <?php if ($rev['status'] !== 'approved'): ?>
                                    <button type="submit" name="status" value="approved" class="btn btn-sm btn-outline-success me-1">Approve</button>
                                <?php endif; ?>
                                <button type="submit" name="status" value="delete" onclick="return confirm('Delete review?');" class="btn btn-sm btn-outline-danger">Delete</button>
                                <input type="hidden" name="update_review_status" value="1">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
