<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

$registerUrl = '../register.html';
$redirect = static function (string $status, string $message = '') use ($registerUrl): void {
    $query = http_build_query(array_filter(['status' => $status, 'msg' => $message]));
    header('Location: ' . $registerUrl . '?' . $query);
    exit;
};
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . $registerUrl, true, 303);
    exit;
}

$name = trim((string)($_POST['full_name'] ?? ''));
$email = strtolower(trim((string)($_POST['email'] ?? '')));
$mobile = trim((string)($_POST['mobile'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$confirm = (string)($_POST['confirm_password'] ?? '');
$course = trim((string)($_POST['course'] ?? ''));
$year = trim((string)($_POST['year'] ?? ''));
$gender = trim((string)($_POST['gender'] ?? ''));
$errors = [];

if (!preg_match("/^[A-Za-z][A-Za-z .'-]{1,49}$/", $name)) $errors[] = 'Enter a valid name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $errors[] = 'Enter a valid email address.';
if (!preg_match('/^[0-9]{10}$/', $mobile)) $errors[] = 'Enter a 10-digit mobile number.';
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password)) $errors[] = 'Use 8+ characters with upper- and lowercase letters, a number and a symbol.';
if (!hash_equals($password, $confirm)) $errors[] = 'Passwords do not match.';
if (!in_array($course, ['B.Tech IT', 'B.Tech CSE', 'B.Tech CE', 'BCA', 'MCA'], true)) $errors[] = 'Select a valid course.';
if (!in_array($year, ['1st Year', '2nd Year', '3rd Year', '4th Year'], true)) $errors[] = 'Select a valid year.';
if (!in_array($gender, ['Female', 'Male', 'Other', 'Prefer not to say'], true)) $errors[] = 'Select a gender option.';
if (!isset($_POST['terms'])) $errors[] = 'Accept the terms to continue.';
if ($errors) $redirect('error', implode(' ', $errors));

$privateDir = __DIR__ . '/storage';
if (!is_dir($privateDir) && !mkdir($privateDir, 0750, true) && !is_dir($privateDir)) {
    $redirect('error', 'Registration storage is unavailable.');
}
$jsonFile = $privateDir . '/registrations.json';
$csvFile = $privateDir . '/registrations.csv';
$records = [];
if (is_file($jsonFile)) {
    $records = json_decode((string)file_get_contents($jsonFile), true);
    if (!is_array($records)) $records = [];
}
foreach ($records as $record) {
    if (isset($record['email']) && strtolower((string)$record['email']) === $email) {
        $redirect('error', 'An account with this email is already registered.');
    }
}

try {
    $db = studenthub_db();
    if ($db instanceof mysqli) {
        $check = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->bind_param('s', $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $check->close();
            $redirect('error', 'An account with this email is already registered.');
        }
        $check->close();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if ($hash === false) throw new RuntimeException('Password could not be protected.');
        $insert = $db->prepare('INSERT INTO users (full_name, email, mobile, course, year_level, gender, password_hash, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $role = 'student';
        $insert->bind_param('ssssssss', $name, $email, $mobile, $course, $year, $gender, $hash, $role);
        $insert->execute();
        $insert->close();
    } else {
        // Practical 7 fallback: store only profile fields; never persist plaintext passwords.
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        if ($hashedPassword === false) throw new RuntimeException('Password could not be protected.');
    }
} catch (mysqli_sql_exception $exception) {
    error_log('StudentHub registration database error: ' . $exception->getMessage());
    $redirect('error', 'Registration could not be saved. Check the database setup and try again.');
} catch (Throwable $exception) {
    error_log('StudentHub registration error: ' . $exception->getMessage());
    $redirect('error', 'Registration could not be completed. Please try again.');
}

$createdAt = date(DATE_ATOM);
$records[] = ['fullName' => $name, 'email' => $email, 'mobile' => $mobile, 'course' => $course, 'year' => $year, 'gender' => $gender, 'createdAt' => $createdAt];
$jsonWritten = file_put_contents($jsonFile, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), LOCK_EX);
$handle = fopen($csvFile, 'c+');
if ($jsonWritten === false || $handle === false) $redirect('error', 'Registration was received but file storage is unavailable.');
if (flock($handle, LOCK_EX)) {
    $isEmpty = filesize($csvFile) === 0;
    fseek($handle, 0, SEEK_END);
    if ($isEmpty) fputcsv($handle, ['fullName', 'email', 'mobile', 'course', 'year', 'gender', 'createdAt']);
    fputcsv($handle, [$name, $email, $mobile, $course, $year, $gender, $createdAt]);
    fflush($handle);
    flock($handle, LOCK_UN);
}
fclose($handle);
$redirect('ok');
