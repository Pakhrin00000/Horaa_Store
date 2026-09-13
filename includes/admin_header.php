<?php
// includes/admin_header.php - Admin Header Topbar
require_once __DIR__ . '/functions.php';
require_admin();
$adminUser = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($adminPageTitle) ? sanitize($adminPageTitle) . ' - HORAA Admin' : 'HORAA Admin Dashboard'; ?></title>
    <!-- Google Fonts Preconnect & Styles -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?php echo url('assets/css/admin.css?v=' . time()); ?>">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php include __DIR__ . '/admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <!-- Admin Topbar -->
            <header class="admin-topbar">
                <div class="d-flex align-items-center gap-3">
                    <h4 class="mb-0 text-white font-monospace fw-bold">
                        <i class="fas fa-terminal text-info me-2"></i><?php echo isset($adminPageTitle) ? sanitize($adminPageTitle) : 'Dashboard Overview'; ?>
                    </h4>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="<?php echo url('index.php'); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-external-link-alt me-1"></i> Visit Live Store
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-dark dropdown-toggle border-secondary" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield text-warning me-1"></i> <?php echo sanitize($adminUser['name']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo url('profile.php'); ?>"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo url('logout.php'); ?>"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>
            
            <div class="admin-content">
                <?php render_flash(); ?>
