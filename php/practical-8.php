<?php
declare(strict_types=1);

/* Practical 8 PDO examples. This page is intentionally separate from the
 * later administrator APIs so it cannot alter their authentication workflow. */
require_once __DIR__ . '/db.php';

function p8_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$message = '';
$messageType = 'info';
$students = $events = $registrations = [];

try {
    $database = studenthub_db();
    if (!$database instanceof PDO) {
        throw new RuntimeException('Database is not configured.');
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $action = (string)($_POST['action'] ?? '');
        if ($action === 'student') {
            $name = trim((string)($_POST['full_name'] ?? ''));
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            $phone = trim((string)($_POST['phone'] ?? ''));
            if ($name === '' || mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || strlen($phone) > 30) {
                throw new InvalidArgumentException('Enter a valid student name, email and optional phone number.');
            }
            $statement = $database->prepare('INSERT INTO students (student_code, full_name, email, phone, course, year_level, department) VALUES (:code, :name, :email, :phone, :course, :year, :department)');
            $statement->execute([
                'code' => 'P8' . strtoupper(bin2hex(random_bytes(5))), 'name' => $name, 'email' => $email,
                'phone' => $phone ?: null, 'course' => 'Practical 8', 'year' => 1, 'department' => 'Practical 8',
            ]);
            $message = 'Student added using a PDO prepared statement.';
            $messageType = 'success';
        } elseif ($action === 'event') {
            $title = trim((string)($_POST['title'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $eventDate = trim((string)($_POST['event_date'] ?? ''));
            $location = trim((string)($_POST['location'] ?? ''));
            $date = DateTime::createFromFormat('!Y-m-d', $eventDate);
            if ($title === '' || strlen($title) > 160 || !$date || $date->format('Y-m-d') !== $eventDate || strlen($description) > 5000 || strlen($location) > 160) {
                throw new InvalidArgumentException('Enter a title and a valid event date.');
            }
            $statement = $database->prepare('INSERT INTO events (title, description, event_date, location, category) VALUES (:title, :description, :event_date, :location, :category)');
            $statement->execute(['title' => $title, 'description' => $description, 'event_date' => $eventDate, 'location' => $location ?: null, 'category' => 'Practical 8']);
            $message = 'Event added using a PDO prepared statement.';
            $messageType = 'success';
        } elseif ($action === 'registration') {
            $studentId = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT);
            $eventId = filter_var($_POST['event_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$studentId || !$eventId) throw new InvalidArgumentException('Select a valid student and event.');
            $exists = $database->prepare('SELECT (SELECT COUNT(*) FROM students WHERE id = :student_id) + (SELECT COUNT(*) FROM events WHERE id = :event_id)');
            $exists->execute(['student_id' => $studentId, 'event_id' => $eventId]);
            if ((int)$exists->fetchColumn() !== 2) throw new InvalidArgumentException('The selected student or event no longer exists.');
            $statement = $database->prepare('INSERT INTO registrations (student_id, event_id) VALUES (:student_id, :event_id)');
            $statement->execute(['student_id' => $studentId, 'event_id' => $eventId]);
            $message = 'Student registered for the event.';
            $messageType = 'success';
        } elseif ($action === 'procedure') {
            $studentId = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$studentId) throw new InvalidArgumentException('Select a valid student.');
            $statement = $database->prepare('CALL GetStudentRegistrations(:student_id)');
            $statement->execute(['student_id' => $studentId]);
            $registrations = $statement->fetchAll();
            $statement->closeCursor();
            $message = 'Stored procedure completed for the selected student.';
            $messageType = 'success';
        }
    }
    $students = $database->query('SELECT id, full_name, email FROM students ORDER BY full_name LIMIT 100')->fetchAll();
    $events = $database->query('SELECT id, title, event_date FROM events ORDER BY event_date LIMIT 100')->fetchAll();
    if (!$registrations) {
        $registrations = $database->query('SELECT r.id AS registration_id, r.status, r.registered_at, s.full_name, s.email, e.title, e.description, e.event_date, e.category, e.location FROM registrations r JOIN students s ON s.id=r.student_id JOIN events e ON e.id=r.event_id ORDER BY r.registered_at DESC LIMIT 100')->fetchAll();
    }
} catch (PDOException $exception) {
    error_log('StudentHub Practical 8 database operation failed: ' . $exception->getMessage());
    $message = (int)$exception->getCode() === 23000 ? 'That email or student-event registration already exists.' : 'The database operation could not be completed.';
    $messageType = 'error';
} catch (Throwable $exception) {
    error_log('StudentHub Practical 8 validation failed: ' . $exception->getMessage());
    $message = $exception instanceof InvalidArgumentException ? $exception->getMessage() : 'The database is not configured or is unavailable.';
    $messageType = 'error';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Practical 8 | StudentHub</title><link rel="stylesheet" href="../CSS/theme.css"></head>
<body><main class="container"><h1>Practical 8: PDO database operations</h1><p><a href="../index.html">StudentHub home</a></p>
<?php if ($message !== ''): ?><p role="status" class="<?= p8_escape($messageType) ?>"><?= p8_escape($message) ?></p><?php endif; ?>
<section><h2>Add student</h2><form method="post"><input type="hidden" name="action" value="student"><label>Name <input required name="full_name" maxlength="100"></label><label>Email <input required type="email" name="email" maxlength="190"></label><label>Phone <input name="phone" maxlength="30"></label><button>Add student</button></form></section>
<section><h2>Add event</h2><form method="post"><input type="hidden" name="action" value="event"><label>Title <input required name="title" maxlength="160"></label><label>Description <textarea name="description"></textarea></label><label>Date <input required type="date" name="event_date"></label><label>Location <input name="location" maxlength="160"></label><button>Add event</button></form></section>
<section><h2>Register for event</h2><form method="post"><input type="hidden" name="action" value="registration"><label>Student <select required name="student_id"><option value="">Select</option><?php foreach ($students as $student): ?><option value="<?= (int)$student['id'] ?>"><?= p8_escape($student['full_name']) ?> (<?= p8_escape($student['email']) ?>)</option><?php endforeach; ?></select></label><label>Event <select required name="event_id"><option value="">Select</option><?php foreach ($events as $event): ?><option value="<?= (int)$event['id'] ?>"><?= p8_escape($event['title']) ?> — <?= p8_escape($event['event_date']) ?></option><?php endforeach; ?></select></label><button>Register</button></form></section>
<section><h2>Stored procedure: student registrations</h2><form method="post"><input type="hidden" name="action" value="procedure"><label>Student <select required name="student_id"><option value="">Select</option><?php foreach ($students as $student): ?><option value="<?= (int)$student['id'] ?>"><?= p8_escape($student['full_name']) ?></option><?php endforeach; ?></select></label><button>Run procedure</button></form></section>
<section><h2>Registrations</h2><table><thead><tr><th>Student</th><th>Event</th><th>Date</th><th>Status</th><th>Registered</th></tr></thead><tbody><?php foreach ($registrations as $registration): ?><tr><td><?= p8_escape($registration['full_name']) ?></td><td><?= p8_escape($registration['title']) ?></td><td><?= p8_escape($registration['event_date']) ?></td><td><?= p8_escape($registration['status']) ?></td><td><?= p8_escape($registration['registered_at']) ?></td></tr><?php endforeach; ?></tbody></table></section>
</main></body></html>
