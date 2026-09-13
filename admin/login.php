<?php
// admin/login.php - Admin Sign In Page
require_once __DIR__ . '/../includes/functions.php';

if (is_admin()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        set_flash('success', 'Admin session authenticated.');
        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Sign In - HORAA STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-dark text-white d-flex align-items-center justify-content-center min-vh-100">
    <div class="auth-card w-100" style="max-width: 420px;">
        <div class="text-center mb-4">
            <div class="brand-logo justify-content-center mb-3">
                <img src="../assets/images/horaa-logo.png" alt="HORAA Esports" style="height: 52px; width: auto; object-fit: contain;">
            </div>
            <h5 class="text-white font-monospace">CONTROL CENTER LOGIN</h5>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger font-monospace small"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label class="form-label-cyber">Admin Email</label>
                <input type="email" name="email" class="form-control form-control-cyber" required placeholder="admin@horaa.com">
            </div>

            <div class="mb-4">
                <label class="form-label-cyber">Password</label>
                <input type="password" name="password" class="form-control form-control-cyber" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn-cyan w-100 py-3 justify-content-center">
                <i class="fas fa-key me-2"></i> Authenticate Admin
            </button>
        </form>
    </div>
</body>
</html>
