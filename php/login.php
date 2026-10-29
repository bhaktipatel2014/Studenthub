<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

studenthub_session();
$root = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/php/login.php'));
$return = static function (string $message) use ($root): void {
    header('Location: ' . $root . '/login.html?status=error&msg=' . rawurlencode($message), true, 303);
    exit;
};
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . $root . '/login.html', true, 303);
    exit;
}
$email = strtolower(trim((string)($_POST['email'] ?? '')));
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || strlen($password) > 128) {
    $return('Enter a valid email and password.');
}
try {
    $database = studenthub_mysqli();
    if (!$database) $return('Database login is not configured. Complete database setup and create an account first.');
    $statement = $database->prepare('SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1');
    $statement->bind_param('s', $email);
    $statement->execute();
    $user = $statement->get_result()->fetch_assoc();
    if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
        $return('Email or password is incorrect.');
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    $update = $database->prepare('UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?');
    $userId = (int)$user['id'];
    $update->bind_param('i', $userId);
    $update->execute();
    $destination = $user['role'] === 'admin' ? '/php/admin.php' : '/php/dashboard.php';
    header('Location: ' . $root . $destination, true, 303);
    exit;
} catch (Throwable $exception) {
    error_log('StudentHub sign-in failed: ' . $exception->getMessage());
    $return('Sign in is unavailable because the StudentHub database server is not running or cannot be reached. Start MySQL in XAMPP, then try again.');
}
