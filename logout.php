<?php
// logout.php - Sign Out Handler
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

session_start();
require_once __DIR__ . '/includes/functions.php';
set_flash('info', 'You have been signed out.');
header('Location: index.php');
exit;
