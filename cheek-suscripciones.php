<?php
require __DIR__ . '/pro/includes/db.php';

$today = date('Y-m-d');

$stmt = $pdo->prepare("
    UPDATE users
    SET is_active = 0
    WHERE subscription_end IS NOT NULL
      AND subscription_end < ?
");
$stmt->execute([$today]);

echo "Suscripciones revisadas.";