<?php
// includes/header.php - Global Navigation & Header
require_once __DIR__ . '/functions.php';
$currentUser = current_user();
$cartCount = get_cart_count();
$wishlistCount = get_wishlist_count();

// Fetch categories for menu
global $db;
$catStmt = $db->query("SELECT * FROM categories ORDER BY id ASC");
$navCategories = $catStmt->fetchAll();

$isOrderSuccessPage = (basename($_SERVER['PHP_SELF']) === 'order-success.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' - HORAA STORE' : 'HORAA STORE - Gaming • Tech • Digital'; ?></title>
    <meta name="description" content="HORAA STORE - Premium Gaming Gear, Esports Merchandise, Custom PCs, Laptops, Components, Peripherals & Digital Products.">
    
    <!-- Google Fonts Preconnect & Styles -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Pro CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom Esports Dark CSS -->
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css?v=' . time()); ?>">
</head>
<body class="<?php echo $isOrderSuccessPage ? 'bg-dark-confirmation' : ''; ?>">

<?php if ($isOrderSuccessPage): ?>
    <!-- GREEN TOP NAVBAR HEADER MATCHING BOTTOM SCREENSHOT -->
    <header class="py-2.5 px-3 border-bottom border-success border-opacity-25" style="background: #0f5132; box-shadow: 0 4px 20px rgba(0,0,0,0.4);">
        <div class="container-fluid container-xl d-flex align-items-center justify-content-between gap-3">
            
            <!-- Brand Logo (HORAA ESPORTS) -->
            <a href="index.php" class="d-flex align-items-center gap-2 text-white text-decoration-none fw-bold fs-4 font-monospace">
                <img src="https://horaaesports.com.np/images/HORAA-ESPORTSBlack.png" onerror="this.onerror=null;this.src='assets/images/horaa-logo.png';" alt="HORAA Esports" style="height: 30px; filter: brightness(0) invert(1); object-fit: contain;">
            </a>

            <!-- Nav Links -->
            <div class="d-none d-md-flex align-items-center gap-4">
                <a href="index.php" class="text-white text-decoration-none small fw-semibold hover-opacity">Home</a>
                <a href="shop.php" class="text-white text-decoration-none small fw-semibold hover-opacity">Products</a>
                <a href="profile.php" class="text-white text-decoration-none small fw-semibold hover-opacity">My Orders</a>
            </div>

            <!-- Search Bar -->
            <div class="d-none d-lg-flex align-items-center" style="max-width: 280px; width: 100%;">
                <form action="shop.php" method="GET" class="w-100 input-group input-group-sm">
                    <input type="text" name="search" class="form-control bg-dark text-white border-secondary border-opacity-50" placeholder="Search products..." style="background: rgba(0,0,0,0.3) !important;">
                    <button type="submit" class="btn btn-dark border-secondary border-opacity-50 text-white"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <!-- Action Buttons: Cart & User Account -->
            <div class="d-flex align-items-center gap-3">
                <a href="cart.php" class="text-white text-decoration-none d-flex align-items-center gap-1 small fw-semibold">
                    <i class="fas fa-shopping-cart"></i> Cart
                    <span class="badge rounded-pill bg-success border border-light ms-1" style="font-size: 0.7rem;"><?php echo $cartCount; ?></span>
                </a>

                <div class="dropdown">
                    <button class="btn btn-sm text-white dropdown-toggle border-0 d-flex align-items-center gap-1 fw-semibold" type="button" data-bs-toggle="dropdown" style="background: rgba(0,0,0,0.2);">
                        <i class="fas fa-user-circle fs-6"></i>
                        <span><?php echo $currentUser ? sanitize(explode(' ', $currentUser['name'])[0]) : 'apioff-official'; ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-box me-2"></i> My Orders</a></li>
                        <li><a class="dropdown-item" href="shop.php"><i class="fas fa-store me-2"></i> Shop Products</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sign Out</a></li>
                    </ul>
                </div>
            </div>

        </div>
    </header>
<?php else: ?>
    <!-- MAIN HEADER NAV -->
    <header class="horaa-header py-3">
        <div class="container d-flex align-items-center justify-content-between gap-3">
            
            <!-- Brand Logo -->
            <a href="index.php" class="brand-logo p-2 px-3 bg-white rounded-3 shadow-sm d-inline-block">
                <img src="https://horaaesports.com.np/images/HORAA-ESPORTSBlack.png" onerror="this.onerror=null;this.src='assets/images/horaa-logo.png';" alt="HORAA Esports" style="height: 34px; width: auto; object-fit: contain; display: block;">
            </a>

            <!-- Global Live Search -->
            <div class="search-box d-none d-md-block">
                <input type="text" id="global-search-input" placeholder="Search PCs, Keyboards, Jerseys, Digital Keys..." autocomplete="off">
                <button type="button" class="search-btn"><i class="fas fa-search"></i></button>
                <div id="search-dropdown" class="search-results-dropdown"></div>
            </div>

            <!-- Action Buttons: Wishlist, Cart, User Account -->
            <div class="d-flex align-items-center gap-2 gap-md-3">
                
                <!-- Wishlist Icon -->
                <a href="wishlist.php" class="nav-action-btn" title="Wishlist">
                    <i class="fas fa-heart fa-lg text-magenta"></i>
                    <span class="d-none d-lg-inline">Wishlist</span>
                    <span id="nav-wishlist-count" class="badge-counter"><?php echo $wishlistCount; ?></span>
                </a>

                <!-- Cart Icon -->
                <a href="cart.php" class="nav-action-btn" title="Shopping Cart">
                    <i class="fas fa-shopping-cart fa-lg text-cyan"></i>
                    <span class="d-none d-lg-inline">Cart</span>
                    <span id="nav-cart-count" class="badge-counter"><?php echo $cartCount; ?></span>
                </a>

                <!-- User Profile / Auth Dropdown -->
                <div class="dropdown">
                    <button class="nav-action-btn border-0 bg-transparent dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg text-cyan"></i>
                        <span class="d-none d-lg-inline">
                            <?php echo $currentUser ? sanitize(explode(' ', $currentUser['name'])[0]) : 'Account'; ?>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end bg-glass border-secondary">
                        <?php if ($currentUser): ?>
                            <li class="px-3 py-2 border-bottom border-secondary text-purple small">
                                Signed in as: <strong><?php echo sanitize($currentUser['email']); ?></strong>
                            </li>
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-gear me-2"></i> My Profile & Orders</a></li>
                            <li><a class="dropdown-item" href="wishlist.php"><i class="fas fa-heart me-2"></i> Saved Items</a></li>
                            <?php if (is_admin()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-gold fw-bold" href="admin/index.php"><i class="fas fa-gauge-high me-2"></i> Admin Dashboard</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-magenta" href="logout.php"><i class="fas fa-right-from-bracket me-2"></i> Sign Out</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="login.php"><i class="fas fa-key me-2"></i> Sign In</a></li>
                            <li><a class="dropdown-item" href="register.php"><i class="fas fa-user-plus me-2"></i> Create Account</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CATEGORIES MEGA MENU NAVIGATION -->
        <nav class="main-nav mt-2">
            <div class="container d-flex align-items-center justify-content-between overflow-x-auto text-nowrap">
                <a href="index.php" class="nav-link-custom <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-house me-1"></i> Home
                </a>
                <a href="shop.php" class="nav-link-custom <?php echo basename($_SERVER['PHP_SELF']) == 'shop.php' && !isset($_GET['cat']) ? 'active' : ''; ?>">
                    <i class="fas fa-store me-1"></i> Shop All
                </a>
                <?php foreach (array_slice($navCategories, 0, 8) as $cat): ?>
                    <a href="shop.php?cat=<?php echo $cat['id']; ?>" class="nav-link-custom <?php echo (isset($_GET['cat']) && $_GET['cat'] == $cat['id']) ? 'active' : ''; ?>">
                        <i class="<?php echo sanitize($cat['icon']); ?> me-1"></i> <?php echo sanitize($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
                <a href="shop.php?digital=1" class="nav-link-custom text-magenta fw-bold">
                    <i class="fas fa-key me-1"></i> Digital Store
                </a>
            </div>
        </nav>
    </header>
<?php endif; ?>

    <!-- MAIN BODY CONTAINER WRAPPER -->
    <main class="flex-grow-1 py-4">
        <div class="container">
            <?php render_flash(); ?>
