<?php
// includes/admin_sidebar.php - Admin Navigation Drawer
$currentAdminFile = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="admin-brand justify-content-center py-3">
        <a href="index.php" title="HORAA Store Admin Dashboard" class="d-inline-block p-2 px-3 bg-white rounded-3 shadow-sm text-decoration-none">
            <img src="https://horaaesports.com.np/images/HORAA-ESPORTSBlack.png" onerror="this.onerror=null;this.src='../assets/images/horaa-logo.png';" alt="HORAA Store Logo" style="height: 34px; width: auto; object-fit: contain; display: block;">
        </a>
    </div>
    
    <ul class="admin-menu">
        <li>
            <a href="index.php" class="<?php echo $currentAdminFile == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line text-cyan"></i> Dashboard Overview
            </a>
        </li>
        <li>
            <a href="products.php" class="<?php echo in_array($currentAdminFile, ['products.php', 'product-add.php', 'product-edit.php']) ? 'active' : ''; ?>">
                <i class="fas fa-boxes-stacked text-warning"></i> Products Catalog
            </a>
        </li>
        <li>
            <a href="categories.php" class="<?php echo $currentAdminFile == 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-layer-group text-info"></i> Categories
            </a>
        </li>
        <li>
            <a href="orders.php" class="<?php echo in_array($currentAdminFile, ['orders.php', 'order-detail.php']) ? 'active' : ''; ?>">
                <i class="fas fa-shopping-bag text-success"></i> Orders & Logistics
            </a>
        </li>
        <li>
            <a href="users.php" class="<?php echo $currentAdminFile == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users text-primary"></i> Customer Accounts
            </a>
        </li>
        <li>
            <a href="coupons.php" class="<?php echo $currentAdminFile == 'coupons.php' ? 'active' : ''; ?>">
                <i class="fas fa-ticket text-magenta"></i> Coupons & Discounts
            </a>
        </li>
        <li>
            <a href="reviews.php" class="<?php echo $currentAdminFile == 'reviews.php' ? 'active' : ''; ?>">
                <i class="fas fa-star text-gold"></i> Product Reviews
            </a>
        </li>
    </ul>

    <div class="mt-auto p-3 border-top border-secondary">
        <a href="<?php echo url('logout.php'); ?>" class="btn btn-sm btn-outline-danger w-100">
            <i class="fas fa-power-off me-2"></i> Log Out
        </a>
    </div>
</aside>
