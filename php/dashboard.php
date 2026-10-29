<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
$user = studenthub_require_user();
$root = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/php/dashboard.php'));
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard | StudentHub</title><link rel="stylesheet" href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>/CSS/theme.css"></head>
<body><main class="container"><p class="hero-tag">STUDENT PORTAL</p><h1>Welcome, <?= htmlspecialchars($user['full_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1><p>You are signed in as a student. Your session expires after 30 minutes of inactivity.</p><nav aria-label="Student links"><a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>/profile.html">Profile</a> · <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>/attendance.html">Attendance</a> · <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>/timetable.html">Timetable</a> · <a href="logout.php">Sign out</a></nav></main></body></html>
