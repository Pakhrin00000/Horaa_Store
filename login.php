<?php
// login.php - HORAA STORE Customer Sign In
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        set_flash('error', 'Please enter both email and password.');
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Merge Guest Session Cart into User Cart
            $sessionId = get_session_cart_id();
            $mergeStmt = $db->prepare("UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
            $mergeStmt->execute([$user['id'], $sessionId]);

            set_flash('success', 'Welcome back, ' . $user['name'] . '!');

            if ($user['role'] === 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: profile.php');
            }
            exit;
        } else {
            set_flash('error', 'Invalid email address or password.');
        }
    }
}

$pageTitle = "Sign In - HORAA STORE";
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-card">
    <div class="text-center mb-4">
        <div class="brand-logo justify-content-center mb-3">
            <img src="assets/images/horaa-logo.png" alt="HORAA Esports" class="brand-logo-img" style="height: 52px;">
        </div>
        <h4 class="text-white font-monospace">SIGN IN TO YOUR ACCOUNT</h4>
        <small class="text-muted">Access your orders, saved wishlist & instant digital keys</small>
    </div>

    <form action="login.php" method="POST">
        <div class="mb-3">
            <label class="form-label-cyber">Email Address</label>
            <input type="email" name="email" class="form-control form-control-cyber" required placeholder="gamer@horaa.com" value="<?php echo sanitize($_POST['email'] ?? ''); ?>">
        </div>

        <div class="mb-4">
            <label class="form-label-cyber">Password</label>
            <input type="password" name="password" class="form-control form-control-cyber" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn-cyan w-100 py-3 mb-4 justify-content-center fs-5">
            <i class="fas fa-right-to-bracket me-2"></i> Sign In
        </button>
    </form>

    <div class="text-center text-muted small">
        Don't have an account yet? <a href="register.php" class="text-cyan fw-bold ms-1">Create Account</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
