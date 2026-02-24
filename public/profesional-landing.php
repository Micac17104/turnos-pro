<?php
require __DIR__ . '/../config.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    die("Profesional no encontrado.");
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE slug = ?");
$stmt->execute([$slug]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Profesional no encontrado.");
}

$user_id = $user['id'];

header("Location: /public/profesional.php?user_id=" . $user_id);
exit;