<?php
declare(strict_types=1);

$dataDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Data';
$jsonFile = $dataDir . DIRECTORY_SEPARATOR . 'contacts.json';
$csvFile = $dataDir . DIRECTORY_SEPARATOR . 'contacts.csv';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../contact.html');
    exit;
}

function clean(string $value): string
{
    return trim(htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8'));
}

$name = clean($_POST['name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$subject = clean($_POST['subject'] ?? '');
$message = clean($_POST['message'] ?? '');

$errors = [];
if (strlen($name) < 3) {
    $errors[] = 'Name is required.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required.';
}
if (strlen($subject) < 3) {
    $errors[] = 'Subject is required.';
}
if (strlen($message) < 10) {
    $errors[] = 'Message must be at least 10 characters.';
}

if ($errors) {
    header('Location: ../contact.html?status=error&msg=' . urlencode(implode(' ', $errors)));
    exit;
}

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0775, true);
}

$records = [];
if (is_file($jsonFile)) {
    $records = json_decode((string) file_get_contents($jsonFile), true) ?: [];
}

$records[] = [
    'name' => $name,
    'email' => $email,
    'subject' => $subject,
    'message' => $message,
    'createdAt' => date('c'),
];

file_put_contents($jsonFile, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

$needHeader = !is_file($csvFile);
$handle = fopen($csvFile, 'a');
if ($handle) {
    if (flock($handle, LOCK_EX)) {
        if ($needHeader) {
            fputcsv($handle, ['name', 'email', 'subject', 'message', 'createdAt']);
        }
        fputcsv($handle, [$name, $email, $subject, $message, date('c')]);
        flock($handle, LOCK_UN);
    }
    fclose($handle);
}

header('Location: ../contact.html?status=ok');
exit;
