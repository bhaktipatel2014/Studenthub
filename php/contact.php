<?php
declare(strict_types=1);

$contactUrl = '../contact.html';
$redirect = static function (string $status, string $message = '') use ($contactUrl): void {
    $query = http_build_query(['status' => $status, 'msg' => $message]);
    header('Location: ' . $contactUrl . '?' . $query, true, 303);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . $contactUrl, true, 303);
    exit;
}

$readField = static function (string $key): string {
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
};
$cleanText = static function (string $value): string {
    $value = strip_tags($value);
    return trim((string)preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value));
};
$characterCount = static function (string $value): int {
    $count = preg_match_all('/./us', $value, $matches);
    return $count === false ? PHP_INT_MAX : $count;
};

$name = $cleanText($readField('name'));
$email = filter_var($readField('email'), FILTER_SANITIZE_EMAIL);
$subject = $cleanText($readField('subject'));
$message = $cleanText($readField('message'));
$errors = [];

if ($characterCount($name) < 2 || $characterCount($name) > 80 || !preg_match("/^[\p{L}\p{M}][\p{L}\p{M} .'-]*$/u", $name)) {
    $errors[] = 'Enter a valid name (2–80 characters).';
}
if (!is_string($email) || strlen($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid email address.';
}
if ($characterCount($subject) < 3 || $characterCount($subject) > 120) {
    $errors[] = 'Subject must be 3–120 characters.';
}
if ($characterCount($message) < 10 || $characterCount($message) > 3000) {
    $errors[] = 'Message must be 10–3000 characters.';
}
if ($errors) {
    $redirect('error', implode(' ', $errors));
}

$storageDir = __DIR__ . '/storage';
if (!is_dir($storageDir) && !mkdir($storageDir, 0750, true) && !is_dir($storageDir)) {
    error_log('StudentHub contact storage directory could not be created.');
    $redirect('error', 'Your message could not be saved. Please try again later.');
}

$jsonFile = $storageDir . '/contact_messages.json';
$handle = fopen($jsonFile, 'c+');
if ($handle === false || !flock($handle, LOCK_EX)) {
    if (is_resource($handle)) fclose($handle);
    error_log('StudentHub contact storage could not be opened or locked.');
    $redirect('error', 'Your message could not be saved. Please try again later.');
}

try {
    rewind($handle);
    $contents = stream_get_contents($handle);
    if ($contents === false) throw new RuntimeException('Unable to read contact storage.');
    $records = trim($contents) === '' ? [] : json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($records)) throw new RuntimeException('Contact storage must contain a JSON array.');

    $records[] = [
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
        'createdAt' => date(DATE_ATOM),
    ];
    $json = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    rewind($handle);
    if (!ftruncate($handle, 0) || fwrite($handle, $json) !== strlen($json) || !fflush($handle)) {
        throw new RuntimeException('Unable to write contact storage.');
    }
} catch (Throwable $exception) {
    error_log('StudentHub contact storage error: ' . $exception->getMessage());
    flock($handle, LOCK_UN);
    fclose($handle);
    $redirect('error', 'Your message could not be saved. Please try again later.');
}

flock($handle, LOCK_UN);
fclose($handle);
$redirect('ok', 'Your message has been sent successfully.');
