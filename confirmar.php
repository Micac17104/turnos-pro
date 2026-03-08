<?php
require __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'confirmed' WHERE id = ?");
    $stmt->execute([$id]);
}

echo "Tu turno fue confirmado. ¡Gracias!";