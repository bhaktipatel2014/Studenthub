<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
$message = 'Database is not configured. Practical 7 file storage is ready.';
$connected = false;
$failed = false;
$database = null;

try {
    $database = studenthub_db();
    if ($database instanceof PDO) {
        // Exercise a real PDO prepared statement without depending on seeded rows.
        // This keeps the check useful immediately after importing the schema.
        $statement = $database->prepare('SELECT :health_check');
        $statement->execute(['health_check' => 1]);
        if ((int)$statement->fetchColumn() !== 1) {
            throw new RuntimeException('The PDO prepared statement returned an unexpected result.');
        }
        $message = 'MySQL connection and PDO prepared statement successful. StudentHub is ready for database operations.';
        $connected = true;
    }
} catch (Throwable $exception) {
    $failed = true;
    error_log('StudentHub database connection check failed: ' . $exception->getMessage());
    $message = 'Database connection failed. Check the local credentials and PDO MySQL configuration.';
}
http_response_code($failed ? 503 : 200);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Database connection | StudentHub</title>
  <link rel="stylesheet" href="../CSS/theme.css">
</head>
<body>
  <main class="container">
    <h1>StudentHub database connection</h1>
    <p role="status"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <p><a href="../register.html">Return to registration</a></p>
  </main>
</body>
</html>
