<?php
// Sesión normal (sin session_save_path)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$prefs = [
    "mostrar_tarjetas"         => isset($_POST['mostrar_tarjetas']),
    "mostrar_graficos"         => isset($_POST['mostrar_graficos']),
    "mostrar_proximos_turnos"  => isset($_POST['mostrar_proximos_turnos']),
    "mostrar_ultimos_pagos"    => isset($_POST['mostrar_ultimos_pagos'])
];

$stmt = $pdo->prepare("UPDATE users SET dashboard_prefs = ? WHERE id = ?");
$stmt->execute([json_encode($prefs), $user_id]);

redirect("dashboard.php?prefs_ok=1");