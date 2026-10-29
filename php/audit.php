<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
$user = studenthub_require_user('admin');
$rows = [];
try {
    $database = studenthub_mysqli();
    if (!$database) throw new RuntimeException('Database is unavailable.');
    $statement = $database->prepare('SELECT a.created_at, u.full_name AS actor, a.action, a.entity_type, a.entity_id, a.details FROM audit_logs a JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 100');
    $statement->execute();
    $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $exception) { error_log('StudentHub audit view failed: ' . $exception->getMessage()); }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Audit log | StudentHub</title><link rel="stylesheet" href="../CSS/theme.css"></head><body><main class="container"><p class="hero-tag">STUDENTHUB · ADMIN</p><h1>Audit log</h1><p><a href="admin.php">Return to administration</a> · <a href="logout.php">Sign out</a></p><div style="overflow:auto"><table><thead><tr><th>Timestamp</th><th>Actor</th><th>Action</th><th>Entity</th><th>Details</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?= htmlspecialchars($row['created_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td><td><?= htmlspecialchars($row['actor'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td><td><?= htmlspecialchars($row['action'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td><td><?= htmlspecialchars($row['entity_type'] . ' #' . $row['entity_id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td><td><?= htmlspecialchars($row['details'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table></div><?php if (!$rows): ?><p>No audit entries are available.</p><?php endif; ?></main></body></html>
