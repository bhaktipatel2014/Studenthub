<?php
declare(strict_types=1);

// Return to the existing contact page with a short success or error message.
$formUrl = '../contact.html';
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

// Trim user text and remove markup/control characters before storing it.
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
    // Prefix spreadsheet formula characters so submitted text remains plain data.
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

$name = $readText('name');
$email = strtolower($readText('email'));
$subject = $readText('subject');
$message = $readText('message');
$errors = [];

if ($name === '') $errors[] = 'Name is required.';
elseif ($length($name) < 2 || $length($name) > 80 || !preg_match("/^[\p{L}\p{M}][\p{L}\p{M} .'-]*$/u", $name)) $errors[] = 'Enter a valid name (2–80 letters).';
if ($email === '') $errors[] = 'Email is required.';
elseif (strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) $errors[] = 'Please enter a valid email address.';
if ($subject === '') $errors[] = 'Subject is required.';
elseif ($length($subject) < 3 || $length($subject) > 120) $errors[] = 'Subject must be 3–120 characters.';
if ($message === '') $errors[] = 'Message is required.';
elseif ($length($message) < 10 || $length($message) > 3000) $errors[] = 'Message must be 10–3000 characters.';

if ($errors) $redirect('error', implode(' ', $errors));

$storageDirectory = __DIR__ . '/storage';
if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0750, true) && !is_dir($storageDirectory)) {
    error_log('StudentHub could not create contact storage.');
    $redirect('error', 'Your message could not be saved. Please try again later.');
}

$headers = ['name', 'email', 'subject', 'message', 'created_at'];
$row = [$csvSafe($name), $email, $csvSafe($subject), $csvSafe($message), date(DATE_ATOM)];
if (!$saveCsv($storageDirectory . '/contacts.csv', $headers, $row)) {
    error_log('StudentHub could not save a contact CSV row.');
    $redirect('error', 'Your message could not be saved. Please check the storage folder and try again.');
}

$redirect('ok', 'Your message has been submitted successfully.');
