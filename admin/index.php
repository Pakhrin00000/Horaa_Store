<?php
// admin/index.php - Executive Dashboard Overview
$adminPageTitle = "Dashboard Overview";
require_once __DIR__ . '/../includes/admin_header.php';

// Stats Calculations
$totalRevenue = $db->query("SELECT SUM(final_amount) FROM orders WHERE payment_status = 'paid'")->fetchColumn() ?: 0.00;
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn() ?: 0;
$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn() ?: 0;

// Low Stock Warnings (< 10)
$lowStockStmt = $db->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.stock <= 10 ORDER BY p.stock ASC LIMIT 5");
$lowStockProducts = $lowStockStmt->fetchAll();

// Recent Orders
$recentOrdersStmt = $db->query("SELECT * FROM orders ORDER BY id DESC LIMIT 6");
$recentOrders = $recentOrdersStmt->fetchAll();
?>

<!-- 4 MAIN STATS WIDGETS -->
<div class="row g-4 mb-4">
    
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-label">Total Paid Revenue</div>
                <div class="stat-value text-cyan"><?php echo format_price($totalRevenue); ?></div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-sack-dollar text-cyan"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-value text-warning"><?php echo number_format($totalOrders); ?></div>
            </div>
            <div class="stat-icon" style="background: rgba(255,183,3,0.1); color: #ffb703;">
                <i class="fas fa-shopping-bag"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-label">Active Products</div>
                <div class="stat-value text-info"><?php echo number_format($totalProducts); ?></div>
            </div>
            <div class="stat-icon" style="background: rgba(0,240,255,0.1); color: #00f0ff;">
                <i class="fas fa-boxes-stacked"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-label">Registered Customers</div>
                <div class="stat-value text-magenta"><?php echo number_format($totalUsers); ?></div>
            </div>
            <div class="stat-icon" style="background: rgba(255,0,85,0.1); color: #ff0055;">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

</div>

<div class="row g-4 mb-4">
    
    <!-- RECENT ORDERS TABLE -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white mb-0 font-monospace"><i class="fas fa-clock text-cyan me-2"></i> Recent Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-outline-cyan">View All Orders</a>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $ord): ?>
                            <tr>
                                <td class="font-monospace fw-bold text-white"><?php echo sanitize($ord['order_number']); ?></td>
                                <td>
                                    <span class="text-white fw-bold d-block"><?php echo sanitize($ord['shipping_name']); ?></span>
                                    <small class="text-muted"><?php echo sanitize($ord['shipping_city']); ?></small>
                                </td>
                                <td class="font-monospace text-cyan fw-bold"><?php echo format_price($ord['final_amount']); ?></td>
                                <td>
                                    <span class="status-pill status-<?php echo strtolower($ord['order_status']); ?>">
                                        <?php echo strtoupper(sanitize($ord['order_status'])); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="order-detail.php?id=<?php echo $ord['id']; ?>" class="btn btn-sm btn-dark border-secondary">
                                        <i class="fas fa-eye text-cyan me-1"></i> Inspect
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- LOW STOCK ALERTS -->
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white mb-0 font-monospace"><i class="fas fa-triangle-exclamation text-magenta me-2"></i> Low Stock Inventory</h5>
                <a href="products.php" class="btn btn-sm btn-outline-magenta">Catalog</a>
            </div>

            <div class="list-group list-group-flush">
                <?php foreach ($lowStockProducts as $lp): ?>
                    <div class="list-group-item bg-dark border-secondary text-white d-flex justify-content-between align-items-center px-0 py-2">
                        <div>
                            <span class="d-block fw-bold text-truncate" style="max-width: 200px;"><?php echo sanitize($lp['name']); ?></span>
                            <small class="text-muted"><?php echo sanitize($lp['category_name']); ?></small>
                        </div>
                        <span class="badge bg-danger fs-6 font-monospace"><?php echo $lp['stock']; ?> left</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
