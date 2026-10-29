<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__) . '/db.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
studenthub_session();

function user_json(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if (empty($_SESSION['user_id'])) user_json(401, ['success' => false, 'message' => 'Sign in is required.']);
if (($_SESSION['role'] ?? '') !== 'admin') user_json(403, ['success' => false, 'message' => 'Administrator access is required.']);
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
    try {
        $database = studenthub_mysqli();
        if (!$database || !studenthub_session_account_active($database)) user_json(401, ['success' => false, 'message' => 'Your account is unavailable.']);
        $result = $database->query('SELECT id, full_name, email, role, status FROM users ORDER BY full_name LIMIT 100');
        user_json(200, ['success' => true, 'data' => ['users' => $result->fetch_all(MYSQLI_ASSOC)]]);
    } catch (Throwable $exception) { error_log('StudentHub user list failed: ' . $exception->getMessage()); user_json(500, ['success' => false, 'message' => 'Accounts could not be loaded.']); }
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') user_json(405, ['success' => false, 'message' => 'Use GET to list accounts or POST to update a status.']);
if (!studenthub_verify_csrf()) user_json(403, ['success' => false, 'message' => 'The security token expired. Refresh and try again.']);

try {
    $payload = json_decode((string)file_get_contents('php://input'), true);
    if (!is_array($payload)) user_json(400, ['success' => false, 'message' => 'Send valid JSON data.']);
    $userId = filter_var($payload['id'] ?? null, FILTER_VALIDATE_INT);
    $status = $payload['status'] ?? null;
    if (!$userId || !in_array($status, ['active', 'inactive'], true)) user_json(422, ['success' => false, 'message' => 'Select a valid account and status.']);
    if ($userId === (int)$_SESSION['user_id'] && $status === 'inactive') user_json(409, ['success' => false, 'message' => 'You cannot deactivate your own administrator account.']);

    $database = studenthub_mysqli();
    if (!$database) user_json(503, ['success' => false, 'message' => 'Database is not configured.']);
    if (!studenthub_session_account_active($database)) user_json(401, ['success' => false, 'message' => 'Your account is unavailable.']);
    $database->begin_transaction();
    $account = $database->prepare('SELECT id, role, status FROM users WHERE id = ? FOR UPDATE');
    $account->bind_param('i', $userId);
    $account->execute();
    $target = $account->get_result()->fetch_assoc();
    if (!$target) { $database->rollback(); user_json(404, ['success' => false, 'message' => 'Account not found.']); }
    if ($target['role'] === 'admin' && $target['status'] === 'active' && $status === 'inactive') {
        $admins = $database->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin' AND status = 'active' FOR UPDATE")->fetch_assoc();
        if ((int)$admins['total'] <= 1) { $database->rollback(); user_json(409, ['success' => false, 'message' => 'The last active administrator cannot be deactivated.']); }
    }
    $update = $database->prepare('UPDATE users SET status = ? WHERE id = ?');
    $update->bind_param('si', $status, $userId);
    $update->execute();
    $actor = (int)$_SESSION['user_id'];
    $action = 'update'; $entity = 'user'; $details = 'Set account status to ' . $status . '.';
    $audit = $database->prepare('INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)');
    $audit->bind_param('issis', $actor, $action, $entity, $userId, $details);
    $audit->execute();
    $database->commit();
    user_json(200, ['success' => true, 'message' => 'Account status updated.']);
} catch (Throwable $exception) {
    if (isset($database) && $database instanceof mysqli) $database->rollback();
    error_log('StudentHub user status update failed: ' . $exception->getMessage());
    user_json(500, ['success' => false, 'message' => 'Account status could not be updated.']);
}
