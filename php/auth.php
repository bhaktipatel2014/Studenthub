<?php
declare(strict_types=1);

function studenthub_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('studenthub_session');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
    if (isset($_SESSION['user_id'], $_SESSION['last_activity'])
        && time() - (int)$_SESSION['last_activity'] > 1800) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    if (isset($_SESSION['user_id'])) $_SESSION['last_activity'] = time();
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
}

function studenthub_require_user(?string $role = null): array
{
    studenthub_session();
    if (empty($_SESSION['user_id'])) {
        header('Location: ../login.html?status=error&msg=' . rawurlencode('Please sign in to continue.'), true, 303);
        exit;
    }
    // A session must not outlive an administrator deactivation. This check is
    // deliberately central so protected pages and APIs have the same policy.
    require_once __DIR__ . '/db.php';
    try {
        $database = studenthub_mysqli();
        if (!$database) throw new RuntimeException('Database is unavailable.');
        $id = (int)$_SESSION['user_id'];
        $statement = $database->prepare('SELECT role, status FROM users WHERE id = ? LIMIT 1');
        $statement->bind_param('i', $id);
        $statement->execute();
        $account = $statement->get_result()->fetch_assoc();
        if (!$account || $account['status'] !== 'active') {
            $_SESSION = [];
            session_regenerate_id(true);
            header('Location: ../login.html?status=error&msg=' . rawurlencode('Your account is unavailable.'));
            exit;
        }
        $_SESSION['role'] = $account['role'];
    } catch (Throwable $exception) {
        error_log('StudentHub session verification failed: ' . $exception->getMessage());
        http_response_code(503);
        exit('Service temporarily unavailable.');
    }
    if ($role !== null && ($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Access denied.');
    }
    return $_SESSION;
}

function studenthub_verify_csrf(): bool
{
    studenthub_session();
    $submitted = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? '';
    return is_string($submitted) && hash_equals((string)$_SESSION['_csrf'], $submitted);
}

/** Used by JSON endpoints after obtaining their shared MySQLi connection. */
function studenthub_session_account_active(mysqli $database): bool
{
    if (empty($_SESSION['user_id'])) return false;
    $id = (int)$_SESSION['user_id'];
    $statement = $database->prepare('SELECT status FROM users WHERE id = ? LIMIT 1');
    $statement->bind_param('i', $id);
    $statement->execute();
    $account = $statement->get_result()->fetch_assoc();
    if (!$account || $account['status'] !== 'active') {
        $_SESSION = [];
        return false;
    }
    return true;
}
