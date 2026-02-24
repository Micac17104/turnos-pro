<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->prepare("
        INSERT INTO tasks (user_id, title, description, date)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $user_id,
        $_POST['title'],
        $_POST['description'],
        $_POST['date']
    ]);

    header("Location: /pro/agenda.php?ok=1");
    exit;
}

header("Location: /pro/agenda.php?error=1");
exit;