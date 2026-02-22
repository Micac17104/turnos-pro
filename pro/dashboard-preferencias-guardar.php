<?php
// --- FIX SESSIONS (igual que en agenda.php) ---
$path = __DIR__ . '/../sessions';

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

if (!is_writable($path)) {
    @chmod($path, 0777);
}

session_save_path($path);
session_start();
// ----------------------------------------------------

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

// Ruta corregida (sin /turnos-pro/pro/)
redirect("dashboard.php?prefs_ok=1");