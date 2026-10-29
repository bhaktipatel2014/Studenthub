<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__) . '/db.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
studenthub_session();
function student_json(int $status, array $body): never
{
    http_response_code($status);
    if (!array_key_exists('success', $body)) $body['success'] = $status >= 200 && $status < 300;
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function student_audit(mysqli $database, string $action, int $recordId, string $details): void
{
    $actorId = (int)($_SESSION['user_id'] ?? 0);
    $type = 'student';
    $statement = $database->prepare('INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)');
    $statement->bind_param('issis', $actorId, $action, $type, $recordId, $details);
    $statement->execute();
}
if (empty($_SESSION['user_id'])) student_json(401, ['success' => false, 'error' => 'Sign in is required.']);
if (($_SESSION['role'] ?? '') !== 'admin') {
    student_json(403, ['error' => 'Administrator access is required.']);
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET' && !studenthub_verify_csrf()) {
    student_json(419, ['error' => 'The security token expired. Refresh and try again.']);
}
try {
    $database = studenthub_mysqli();
    if (!$database) student_json(503, ['error' => 'Database is not configured.']);
    if (!studenthub_session_account_active($database)) student_json(401, ['success' => false, 'error' => 'Your account is unavailable.']);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET') {
        $query = '%' . trim((string)($_GET['q'] ?? '')) . '%';
        $course = trim((string)($_GET['course'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 25)));
        $offset = ($page - 1) * $perPage;
        $where = ' WHERE (full_name LIKE ? OR email LIKE ? OR course LIKE ? OR department LIKE ?) AND (? = \'\' OR course = ?)';
        $count = $database->prepare('SELECT COUNT(*) AS total FROM students' . $where);
        $count->bind_param('ssssss', $query, $query, $query, $query, $course, $course);
        $count->execute();
        $total = (int)$count->get_result()->fetch_assoc()['total'];
        if (($_GET['format'] ?? '') === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="studenthub-students.csv"');
            $export = $database->prepare('SELECT student_code, full_name, email, course, year_level, department, status FROM students' . $where . ' ORDER BY full_name');
            $export->bind_param('ssssss', $query, $query, $query, $query, $course, $course);
            $export->execute();
            $out = fopen('php://output', 'wb');
            fputcsv($out, ['student_code', 'full_name', 'email', 'course', 'year_level', 'department', 'status']);
            $exportRows = $export->get_result();
            while ($row = $exportRows->fetch_assoc()) {
                foreach ($row as &$value) if (is_string($value) && preg_match('/^[=+@-]/', ltrim($value))) $value = "'" . $value;
                unset($value);
                fputcsv($out, $row);
            }
            fclose($out);
            exit;
        }
        $statement = $database->prepare('SELECT id, student_code, full_name AS name, email, course, year_level AS year, department, status FROM students' . $where . ' ORDER BY full_name LIMIT ? OFFSET ?');
        $statement->bind_param('ssssssii', $query, $query, $query, $query, $course, $course, $perPage, $offset);
        $statement->execute();
        student_json(200, ['success' => true, 'students' => $statement->get_result()->fetch_all(MYSQLI_ASSOC), 'pagination' => ['page' => $page, 'per_page' => $perPage, 'total' => $total]]);
    }
    $payload = json_decode((string)file_get_contents('php://input'), true);
    if (!is_array($payload)) student_json(400, ['error' => 'Send valid JSON data.']);
    $action = (string)($payload['action'] ?? '');
    if ($action === 'delete') {
        $id = filter_var($payload['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) student_json(422, ['error' => 'Select a valid student record.']);
        $status = 'inactive';
        $statement = $database->prepare('UPDATE students SET status = ? WHERE id = ?');
        $statement->bind_param('si', $status, $id);
        $statement->execute();
        if ($statement->affected_rows > 0) student_audit($database, 'update', $id, 'Deactivated student record.');
        student_json(200, ['success' => true, 'message' => 'Student record deactivated; registrations were preserved.']);
    }
    $name = trim((string)($payload['name'] ?? ''));
    $email = strtolower(trim((string)($payload['email'] ?? '')));
    $code = strtoupper(trim((string)($payload['student_code'] ?? '')));
    $course = trim((string)($payload['course'] ?? ''));
    $year = filter_var($payload['year'] ?? null, FILTER_VALIDATE_INT);
    $department = trim((string)($payload['department'] ?? ''));
    $status = (string)($payload['status'] ?? 'active');
    if ($name === '' || mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($email) > 190 || !preg_match('/^[A-Z0-9-]{3,24}$/', $code)
        || !in_array($course, ['B.Tech IT','B.Tech CSE','B.Tech CE','BCA','MCA'], true)
        || !$year || $year < 1 || $year > 4 || $department === '' || mb_strlen($department) > 100
        || !in_array($status, ['active','inactive'], true)) {
        student_json(422, ['error' => 'Check the name, email, student code, course, year, department and status.']);
    }
    $id = filter_var($payload['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
    if ($id > 0) {
        $statement = $database->prepare('UPDATE students SET student_code=?, full_name=?, email=?, course=?, year_level=?, department=?, status=? WHERE id=?');
        $statement->bind_param('ssssissi', $code, $name, $email, $course, $year, $department, $status, $id);
        $statement->execute();
        student_audit($database, 'update', $id, 'Updated student ' . $code . '.');
        student_json(200, ['message' => 'Student record updated.']);
    }
    $statement = $database->prepare('INSERT INTO students (student_code, full_name, email, course, year_level, department, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $statement->bind_param('ssssiss', $code, $name, $email, $course, $year, $department, $status);
    $statement->execute();
    student_audit($database, 'create', (int)$database->insert_id, 'Created student ' . $code . '.');
    student_json(201, ['message' => 'Student record added.']);
} catch (mysqli_sql_exception $exception) {
    if ((int)$exception->getCode() === 1062) student_json(409, ['error' => 'Student code or email already exists.']);
    if ((int)$exception->getCode() === 1451) student_json(409, ['error' => 'This student has registrations and cannot be deleted.']);
    error_log('Student API failed: ' . $exception->getMessage());
    student_json(500, ['error' => 'Student records could not be loaded or saved.']);
} catch (Throwable $exception) {
    error_log('Student API failed: ' . $exception->getMessage());
    student_json(500, ['error' => 'Student records could not be loaded or saved.']);
}
