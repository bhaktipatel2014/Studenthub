<?php
declare(strict_types=1);

// Send the visitor back to the existing form with a message. POST/Redirect/GET
// prevents the browser from submitting the same registration twice on refresh.
$formUrl = '../register.html';
$redirect = static function (string $status, string $message) use ($formUrl): void {
    header('Location: ' . $formUrl . '?' . http_build_query([
        'status' => $status,
        'msg' => $message,
    ]), true, 303);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . $formUrl, true, 303);
    exit;
}

// Read scalar form values only, trim them, and strip markup/control characters
// before validation or storage. Passwords are handled separately and unchanged.
$readText = static function (string $key): string {
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) return '';
    $value = trim(strip_tags($value));
    $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
    return trim($cleaned ?? '');
};
$length = static function (string $value): int {
    $count = preg_match_all('/./us', $value);
    return $count === false ? PHP_INT_MAX : $count;
};
$csvSafe = static function (string $value): string {
    // Stop spreadsheet programs from treating user text as a formula.
    return preg_match('/^[\t\r\n ]*[=+@-]/', $value) === 1 ? "'" . $value : $value;
};
$saveCsv = static function (string $path, array $headers, array $row): bool {
    $handle = fopen($path, 'c+');
    if ($handle === false) return false;
    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        return false;
    }

    $fileInfo = fstat($handle);
    $saved = $fileInfo !== false && fseek($handle, 0, SEEK_END) === 0;
    if ($saved && (int)$fileInfo['size'] === 0) {
        $saved = fputcsv($handle, $headers, ',', '"', '') !== false;
    }
    if ($saved) $saved = fputcsv($handle, $row, ',', '"', '') !== false;
    if ($saved) $saved = fflush($handle);

    flock($handle, LOCK_UN);
    fclose($handle);
    return $saved;
};

$name = $readText('full_name');
$email = strtolower($readText('email'));
$mobile = $readText('mobile');
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$confirmation = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
$course = $readText('course');
$year = $readText('year');
$gender = $readText('gender');
$termsAccepted = $readText('terms') === 'yes';
$errors = [];

if ($name === '') $errors[] = 'Name is required.';
elseif ($length($name) < 2 || $length($name) > 50 || !preg_match("/^[\p{L}\p{M}][\p{L}\p{M} .'-]*$/u", $name)) $errors[] = 'Enter a valid name using 2–50 letters.';
if ($email === '') $errors[] = 'Email is required.';
elseif (strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) $errors[] = 'Please enter a valid email address.';
if ($mobile === '') $errors[] = 'Mobile number is required.';
elseif (!preg_match('/^[0-9]{10}$/', $mobile)) $errors[] = 'Mobile number must contain 10 digits.';
if ($password === '') $errors[] = 'Password is required.';
elseif (strlen($password) > 128 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,128}$/', $password)) $errors[] = 'Password must be 8–128 characters and include uppercase, lowercase, a number, and a symbol.';
if ($confirmation === '') $errors[] = 'Please confirm your password.';
elseif (!hash_equals($password, $confirmation)) $errors[] = 'Passwords do not match.';
if (!in_array($course, ['B.Tech IT', 'B.Tech CSE', 'B.Tech CE', 'BCA', 'MCA'], true)) $errors[] = 'Please select a valid course.';
if (!in_array($year, ['1st Year', '2nd Year', '3rd Year', '4th Year'], true)) $errors[] = 'Please select a valid year.';
if (!in_array($gender, ['Female', 'Male', 'Other', 'Prefer not to say'], true)) $errors[] = 'Please select a gender option.';
if (!$termsAccepted) $errors[] = 'Please accept the terms and conditions.';

if ($errors) $redirect('error', implode(' ', $errors));

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
if ($passwordHash === false) $redirect('error', 'The password could not be protected. Please try again.');

// Practical 9: use MySQLi prepared statements when a database is configured.
// Without one, Practical 7 remains usable through the private CSV storage path.
require_once __DIR__ . '/db.php';
$database = null;
try {
    $database = studenthub_mysqli();
} catch (Throwable $exception) {
    error_log('StudentHub database connection failed: ' . $exception->getMessage());
    $redirect('error', 'Registration is unavailable because the StudentHub database server is not running or cannot be reached. Start MySQL in XAMPP, then try again.');
}

if ($database instanceof mysqli) {
    $transactionStarted = false;
    try {
        $database->begin_transaction();
        $transactionStarted = true;
        $emailCheck = $database->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $emailCheck->bind_param('s', $email);
        $emailCheck->execute();
        $emailCheck->store_result();
        if ($emailCheck->num_rows > 0) {
            $database->rollback();
            $transactionStarted = false;
            $redirect('error', 'An account with this email address already exists.');
        }

        $userInsert = $database->prepare('INSERT INTO users '
            . '(full_name, email, mobile, course, year_level, gender, password_hash, role) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, \'student\')');
        $userInsert->bind_param('sssssss', $name, $email, $mobile, $course, $year, $gender, $passwordHash);
        $userInsert->execute();
        $userId = $database->insert_id;
        $studentCode = 'SH' . strtoupper(bin2hex(random_bytes(6)));
        $yearNumber = (int)$year[0];
        $department = $course === 'B.Tech IT' ? 'Information Technology' : $course;
        $studentInsert = $database->prepare('INSERT INTO students '
            . '(user_id, student_code, full_name, email, course, year_level, department) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $studentInsert->bind_param('issssis', $userId, $studentCode, $name, $email, $course, $yearNumber, $department);
        $studentInsert->execute();
        $database->commit();
        $transactionStarted = false;
        $redirect('ok', 'Registration successful! Your details have been saved successfully.');
    } catch (mysqli_sql_exception $exception) {
        if ($transactionStarted) $database->rollback();
        if ((int)$exception->getCode() === 1062) {
            $redirect('error', 'An account with this email address already exists.');
        }
        error_log('StudentHub database registration failed: ' . $exception->getMessage());
        $redirect('error', 'Registration could not be saved. Please try again later.');
    } catch (Throwable $exception) {
        if ($transactionStarted) $database->rollback();
        error_log('StudentHub database registration failed: ' . $exception->getMessage());
        $redirect('error', 'Registration could not be saved. Please try again later.');
    }
}

$storageDirectory = __DIR__ . '/storage';
if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0750, true) && !is_dir($storageDirectory)) {
    error_log('StudentHub could not create registration storage.');
    $redirect('error', 'Registration could not be saved. Please try again later.');
}

$headers = ['full_name', 'email', 'mobile', 'password_hash', 'course', 'year', 'gender', 'created_at'];
$row = [
    $csvSafe($name), $email, $mobile, $passwordHash, $csvSafe($course),
    $csvSafe($year), $csvSafe($gender), date(DATE_ATOM),
];
if (!$saveCsv($storageDirectory . '/registrations.csv', $headers, $row)) {
    error_log('StudentHub could not save a registration CSV row.');
    $redirect('error', 'Registration could not be saved. Please check the storage folder and try again.');
}

$redirect('ok', 'Registration successful! Your details have been saved successfully.');
