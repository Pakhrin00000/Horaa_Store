<?php
// register.php - Customer Account Registration
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        set_flash('error', 'Please fill in all required fields.');
    } elseif ($password !== $confirmPassword) {
        set_flash('error', 'Password confirmation does not match.');
    } elseif (strlen($password) < 6) {
        set_flash('error', 'Password must be at least 6 characters long.');
    } else {
        // Check duplicate email
        $chk = $db->prepare("SELECT id FROM users WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            set_flash('error', 'An account with this email address already exists.');
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $ins = $db->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'user')");
            $ins->execute([$name, $email, $hashedPassword, $phone]);
            $newId = $db->lastInsertId();

            $_SESSION['user_id'] = $newId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'user';

            // Merge Guest Session Cart into User Cart
            $sessionId = get_session_cart_id();
            $mergeStmt = $db->prepare("UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
            $mergeStmt->execute([$newId, $sessionId]);

            set_flash('success', 'Account created successfully! Welcome to HORAA STORE.');
            header('Location: profile.php');
            exit;
        }
    }
}

$pageTitle = "Create Account - HORAA STORE";
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-card">
    <div class="text-center mb-4">
        <div class="brand-logo justify-content-center mb-3">
            <img src="assets/images/horaa-logo.png" alt="HORAA Esports" class="brand-logo-img" style="height: 52px;">
        </div>
        <h4 class="text-white font-monospace">CREATE GAMER ACCOUNT</h4>
        <small class="text-muted">Join HORAA STORE for exclusive esports drops & deals</small>
    </div>

    <form action="register.php" method="POST">
        <div class="mb-3">
            <label class="form-label-cyber">Full Name *</label>
            <input type="text" name="name" class="form-control form-control-cyber" required placeholder="Alex Mercer" value="<?php echo sanitize($_POST['name'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label-cyber">Email Address *</label>
            <input type="email" name="email" class="form-control form-control-cyber" required placeholder="name@example.com" value="<?php echo sanitize($_POST['email'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label-cyber">Phone Number</label>
            <input type="text" name="phone" class="form-control form-control-cyber" placeholder="+1 (555) 000-0000" value="<?php echo sanitize($_POST['phone'] ?? ''); ?>">
        </div>

        <div class="row g-2 mb-4">
            <div class="col-6">
                <label class="form-label-cyber">Password *</label>
                <input type="password" name="password" class="form-control form-control-cyber" required placeholder="••••••••">
            </div>
            <div class="col-6">
                <label class="form-label-cyber">Confirm *</label>
                <input type="password" name="confirm_password" class="form-control form-control-cyber" required placeholder="••••••••">
            </div>
        </div>

        <button type="submit" class="btn-magenta w-100 py-3 mb-3 justify-content-center fs-5">
            <i class="fas fa-user-plus me-2"></i> Register Account
        </button>
    </form>

    <div class="text-center text-muted small">
        Already registered? <a href="login.php" class="text-cyan fw-bold">Sign In Here</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
