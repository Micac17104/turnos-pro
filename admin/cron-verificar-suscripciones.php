<?php
// Cron: verificar suscripciones
// NO incluir auth-admin.php

require __DIR__ . '/../pro/includes/db.php';

$today = date('Y-m-d');
$three_days = date('Y-m-d', strtotime('+3 days'));

// 1) Aviso 3 días antes
$stmt = $pdo->prepare("
    SELECT id, name, email 
    FROM users 
    WHERE subscription_end = ?
    AND is_active = 1
");
$stmt->execute([$three_days]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $u) {
    mail($u['email'], "Tu suscripción vence pronto", 
        "Hola {$u['name']}, tu suscripción vence en 3 días.");
}

// 2) Aviso día del vencimiento
$stmt = $pdo->prepare("
    SELECT id, name, email 
    FROM users 
    WHERE subscription_end = ?
    AND is_active = 1
");
$stmt->execute([$today]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $u) {
    mail($u['email'], "Tu suscripción venció", 
        "Hola {$u['name']}, tu suscripción venció hoy.");
}

// 3) Marcar vencidos
$stmt = $pdo->prepare("
    UPDATE users
    SET is_active = 0
    WHERE subscription_end < ?
");
$stmt->execute([$today]);

echo "Cron ejecutado correctamente: " . date('Y-m-d H:i:s');
