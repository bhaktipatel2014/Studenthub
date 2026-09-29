<?php
declare(strict_types=1);

$dataDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Data';
$file = $dataDir . DIRECTORY_SEPARATOR . 'registrations.json';
$csv = $dataDir . DIRECTORY_SEPARATOR . 'registrations.csv';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.html');
    exit;
}

function clean(string $value): string
{
    return trim(htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8'));
}

$fullName = clean($_POST['fullName'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$mobile = clean($_POST['mobile'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirmPassword'] ?? '';
$course = clean($_POST['course'] ?? '');
$year = clean($_POST['year'] ?? '');
$gender = clean($_POST['gender'] ?? '');
$terms = isset($_POST['terms']);

$errors = [];

if (!preg_match('/^[A-Za-z][A-Za-z ]{2,49}$/', $fullName)) {
    $errors[] = 'Invalid name.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email.';
}
if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    $errors[] = 'Invalid mobile number.';
}
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/', $password)) {
    $errors[] = 'Password is not strong enough.';
}
if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}
if ($course === '' || $year === '' || $gender === '') {
    $errors[] = 'Course, year and gender are required.';
}
if (!$terms) {
    $errors[] = 'Terms must be accepted.';
}

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0775, true);
}

if ($errors) {
    header('Location: ../register.html?status=error&msg=' . urlencode(implode(' ', $errors)));
    exit;
}

$records = [];
if (is_file($file)) {
    $json = file_get_contents($file);
    $records = json_decode($json, true) ?: [];
}

$records[] = [
    'fullName' => $fullName,
    'email' => $email,
    'mobile' => $mobile,
    'course' => $course,
    'year' => $year,
    'gender' => $gender,
    'createdAt' => date('c'),
];

$written = file_put_contents(
    $file,
    json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    LOCK_EX
);

$csvLine = [$fullName, $email, $mobile, $course, $year, $gender, date('c')];
$needHeader = !is_file($csv);
$handle = fopen($csv, 'a');
if ($handle) {
    if (flock($handle, LOCK_EX)) {
        if ($needHeader) {
            fputcsv($handle, ['fullName', 'email', 'mobile', 'course', 'year', 'gender', 'createdAt']);
        }
        fputcsv($handle, $csvLine);
        flock($handle, LOCK_UN);
    }
    fclose($handle);
}

if ($written === false) {
    header('Location: ../register.html?status=error&msg=' . urlencode('Could not save registration file.'));
    exit;
}

header('Location: ../register.html?status=ok');
exit;
