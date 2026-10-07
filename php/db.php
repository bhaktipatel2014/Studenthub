<?php
declare(strict_types=1);

/** Return null when MySQL is not configured (Practical 7 file-storage mode). */
function studenthub_db(): ?mysqli
{
    $localConfigPath = __DIR__ . '/config.local.php';
    $localConfig = is_file($localConfigPath) ? require $localConfigPath : [];
    $host = $localConfig['host'] ?? getenv('STUDENTHUB_DB_HOST');
    $name = $localConfig['database'] ?? getenv('STUDENTHUB_DB_NAME');
    $user = $localConfig['username'] ?? getenv('STUDENTHUB_DB_USER');
    $pass = $localConfig['password'] ?? getenv('STUDENTHUB_DB_PASSWORD');
    if (!$host || !$name || !$user) return null;

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $port = (int)($localConfig['port'] ?? (getenv('STUDENTHUB_DB_PORT') ?: 3306));
    $connection = new mysqli($host, $user, $pass ?: '', $name, $port);
    $connection->set_charset('utf8mb4');
    return $connection;
}
