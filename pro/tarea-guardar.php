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

if ($title === '' || $date === '') {
    header("Location: /pro/agenda.php?error=campos");
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO tasks (user_id, title, description, date)
    VALUES (?, ?, ?, ?)
");

try {
    $stmt->execute([$user_id, $title, $description, $date]);
    header("Location: /pro/agenda.php?ok=1");
    exit;

} catch (PDOException $e) {
    header("Location: /pro/agenda.php?error=db");
    exit;
}