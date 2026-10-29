<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__) . '/db.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
studenthub_session();
function event_json(int $status, array $body): never
{
    http_response_code($status);
    if (!array_key_exists('success', $body)) $body['success'] = $status >= 200 && $status < 300;
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function event_audit(mysqli $database, string $action, int $recordId, string $details): void
{
    $actorId = (int)($_SESSION['user_id'] ?? 0);
    $type = 'event';
    $statement = $database->prepare('INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)');
    $statement->bind_param('issis', $actorId, $action, $type, $recordId, $details);
    $statement->execute();
}
if (empty($_SESSION['user_id'])) event_json(401, ['error' => 'Sign in is required.']);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET' && ($_SESSION['role'] ?? '') !== 'admin') event_json(403, ['error' => 'Administrator access is required.']);
if ($method !== 'GET' && !studenthub_verify_csrf()) event_json(419, ['error' => 'The security token expired. Refresh and try again.']);
try {
    $database = studenthub_mysqli();
    if (!$database) event_json(503, ['error' => 'Database is not configured.']);
    if (!studenthub_session_account_active($database)) event_json(401, ['success' => false, 'error' => 'Your account is unavailable.']);
    if ($method === 'GET') {
        $query = '%' . trim((string)($_GET['q'] ?? '')) . '%';
        $category = trim((string)($_GET['category'] ?? ''));
        $statusFilter = trim((string)($_GET['status'] ?? ''));
        if ($statusFilter !== '' && !in_array($statusFilter, ['open', 'closed'], true)) event_json(422, ['success' => false, 'error' => 'Select a valid event status.']);
        $statement = $database->prepare('SELECT id, title, description, event_date AS date, category, poster_path, status FROM events WHERE (title LIKE ? OR description LIKE ? OR category LIKE ?) AND (? = \'\' OR category = ?) AND (? = \'\' OR status = ?) ORDER BY event_date DESC LIMIT 500');
        $statement->bind_param('sssssss', $query, $query, $query, $category, $category, $statusFilter, $statusFilter);
        $statement->execute();
        event_json(200, ['success' => true, 'message' => 'Events loaded successfully.', 'events' => $statement->get_result()->fetch_all(MYSQLI_ASSOC)]);
    }
    if ($method !== 'POST') event_json(405, ['error' => 'Use GET to list events or POST to change them.']);
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $date = trim((string)($_POST['date'] ?? ''));
    $category = trim((string)($_POST['category'] ?? ''));
    $status = (string)($_POST['status'] ?? 'open');
    $action = (string)($_POST['action'] ?? '');
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
    if ($action === 'delete') {
        if ($id < 1) event_json(422, ['error' => 'Select a valid event.']);
        $closed = 'closed';
        $statement = $database->prepare('UPDATE events SET status = ? WHERE id = ?');
        $statement->bind_param('si', $closed, $id);
        $statement->execute();
        if ($statement->affected_rows > 0) event_audit($database, 'update', $id, 'Closed event; registrations were preserved.');
        event_json(200, ['success' => true, 'message' => 'Event closed; registrations were preserved.']);
    }
    $dateValue = DateTime::createFromFormat('!Y-m-d', $date);
    if ($title === '' || strlen($title) > 160 || strlen($description) > 5000
        || !$dateValue || $dateValue->format('Y-m-d') !== $date || $category === '' || strlen($category) > 60
        || !in_array($status, ['open','closed'], true)) {
        event_json(422, ['error' => 'Check the title, description, date, category and status.']);
    }
    $posterPath = null;
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = $_FILES['poster'];
        if ($upload['error'] !== UPLOAD_ERR_OK || $upload['size'] > 2 * 1024 * 1024 || !is_uploaded_file($upload['tmp_name'])) {
            event_json(422, ['error' => 'Poster upload must be a valid image no larger than 2 MB.']);
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mime]) || getimagesize($upload['tmp_name']) === false) event_json(422, ['error' => 'Posters must be valid JPG, PNG or WebP images.']);
        $directory = dirname(__DIR__) . '/uploads/events';
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) event_json(500, ['error' => 'Poster storage is unavailable.']);
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($upload['tmp_name'], $directory . '/' . $filename)) event_json(500, ['error' => 'Poster could not be saved.']);
        $posterPath = 'php/uploads/events/' . $filename;
    }
    if ($id > 0) {
        if ($posterPath === null) {
            $statement = $database->prepare('UPDATE events SET title=?, description=?, event_date=?, category=?, status=? WHERE id=?');
            $statement->bind_param('sssssi', $title, $description, $date, $category, $status, $id);
        } else {
            $statement = $database->prepare('UPDATE events SET title=?, description=?, event_date=?, category=?, status=?, poster_path=? WHERE id=?');
            $statement->bind_param('ssssssi', $title, $description, $date, $category, $status, $posterPath, $id);
        }
        $statement->execute();
        event_audit($database, 'update', $id, 'Updated event: ' . $title . '.');
        event_json(200, ['message' => 'Event updated.']);
    }
    $statement = $database->prepare('INSERT INTO events (title, description, event_date, category, status, poster_path) VALUES (?, ?, ?, ?, ?, ?)');
    $statement->bind_param('ssssss', $title, $description, $date, $category, $status, $posterPath);
    $statement->execute();
    event_audit($database, 'create', (int)$database->insert_id, 'Created event: ' . $title . '.');
    event_json(201, ['message' => 'Event added.']);
} catch (mysqli_sql_exception $exception) {
    if ((int)$exception->getCode() === 1062) event_json(409, ['error' => 'An event with that title and date already exists.']);
    if ((int)$exception->getCode() === 1451) event_json(409, ['error' => 'This event has registrations and cannot be deleted.']);
    error_log('Event API failed: ' . $exception->getMessage());
    event_json(500, ['error' => 'Event records could not be loaded or saved.']);
} catch (Throwable $exception) {
    error_log('Event API failed: ' . $exception->getMessage());
    event_json(500, ['error' => 'Event records could not be loaded or saved.']);
}
