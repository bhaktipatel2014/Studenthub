<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__) . '/db.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
studenthub_session();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 403);
    echo json_encode(['error' => 'Administrator access is required.']);
    exit;
}
try {
    $database = studenthub_mysqli();
    if (!$database) throw new RuntimeException('Database is not configured.');
    if (!studenthub_session_account_active($database)) {
        http_response_code(401);
        echo json_encode(['error' => 'Your account is unavailable.']);
        exit;
    }
    $counts = [];
    foreach (['students' => 'students', 'events' => 'events', 'registrations' => 'registrations', 'users' => 'users'] as $key => $table) {
        $result = $database->query("SELECT COUNT(*) AS total FROM {$table}");
        $counts[$key] = (int)$result->fetch_assoc()['total'];
    }
    $userStates = $database->query("SELECT SUM(status = 'active') AS active_users, SUM(status = 'inactive') AS inactive_users FROM users")->fetch_assoc();
    $counts['active_users'] = (int)$userStates['active_users'];
    $counts['inactive_users'] = (int)$userStates['inactive_users'];
    $registrations = $database->query('SELECT s.full_name AS student_name, e.title AS event_title, r.status, r.registered_at FROM registrations r JOIN students s ON s.id = r.student_id JOIN events e ON e.id = r.event_id ORDER BY r.registered_at DESC LIMIT 10');
    $events = $database->query('SELECT title, event_date, status FROM events ORDER BY event_date DESC LIMIT 8');
    $audit = $database->query('SELECT action, entity_type, details, created_at FROM audit_logs ORDER BY created_at DESC LIMIT 10');
    echo json_encode([
        'counts' => $counts,
        'recent_registrations' => $registrations->fetch_all(MYSQLI_ASSOC),
        'recent_events' => $events->fetch_all(MYSQLI_ASSOC),
        'audit_logs' => $audit->fetch_all(MYSQLI_ASSOC),
    ], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $exception) {
    error_log('Admin dashboard API failed: ' . $exception->getMessage());
    http_response_code(503);
    echo json_encode(['error' => 'Dashboard information is unavailable.']);
}
