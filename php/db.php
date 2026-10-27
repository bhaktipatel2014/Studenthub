<?php
declare(strict_types=1);

/** Read local-only configuration shared by PDO (Practical 8) and MySQLi (Practical 9). */
function studenthub_db_config(): array
{
    $localConfigPath = __DIR__ . '/config.local.php';
    $localConfig = is_file($localConfigPath) ? require $localConfigPath : [];
    if (!is_array($localConfig)) $localConfig = [];
    $host = $localConfig['host'] ?? getenv('STUDENTHUB_DB_HOST');
    $name = $localConfig['database'] ?? getenv('STUDENTHUB_DB_NAME');
    $user = $localConfig['username'] ?? getenv('STUDENTHUB_DB_USER');
    $pass = $localConfig['password'] ?? getenv('STUDENTHUB_DB_PASSWORD');
    if (!$host || !$name || !$user) return [];

    $port = (int)($localConfig['port'] ?? (getenv('STUDENTHUB_DB_PORT') ?: 3306));
    return ['host' => (string)$host, 'database' => (string)$name, 'username' => (string)$user,
        'password' => (string)($pass ?: ''), 'port' => $port];
}

/** Return null when MySQL is not configured (Practical 7 file-storage mode). */
function studenthub_db(): ?PDO
{
    $config = studenthub_db_config();
    if (!$config) return null;

    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    return new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

/** Return null when MySQL is not configured; otherwise open a strict MySQLi connection. */
function studenthub_mysqli(): ?mysqli
{
    $config = studenthub_db_config();
    if (!$config) return null;

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $database = new mysqli($config['host'], $config['username'], $config['password'],
        $config['database'], $config['port']);
    $database->set_charset('utf8mb4');
    return $database;
}
