<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$prefs = [
    "mostrar_tarjetas" => isset($_POST['mostrar_tarjetas']),
    "mostrar_graficos" => isset($_POST['mostrar_graficos']),
    "mostrar_proximos_turnos" => isset($_POST['mostrar_proximos_turnos']),
    "mostrar_ultimos_pagos" => isset($_POST['mostrar_ultimos_pagos'])
];

$stmt = $pdo->prepare("UPDATE users SET dashboard_prefs = ? WHERE id = ?");
$stmt->execute([json_encode($prefs), $user_id]);

header("Location: /turnos-pro/pro/dashboard.php?prefs_ok=1");
exit;