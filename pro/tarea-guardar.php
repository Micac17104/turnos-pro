<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /pro/agenda.php");
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$date = trim($_POST['date'] ?? '');
$time = trim($_POST['time'] ?? '');

if ($title === '' || $date === '' || $time === '') {
    header("Location: /pro/agenda.php?error=campos");
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO professional_tasks (user_id, title, description, date, time, completed)
    VALUES (?, ?, ?, ?, ?, 0)
");

try {
    $stmt->execute([
        $user_id,
        $title,
        $description,
        $date,
        $time
    ]);

    header("Location: /pro/agenda.php?ok=1");
    exit;

} catch (PDOException $e) {
    header("Location: /pro/agenda.php?error=db");
    exit;
}