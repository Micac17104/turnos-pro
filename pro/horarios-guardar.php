<?php
// --- FIX DEFINITIVO PARA RAILWAY ---
$path = __DIR__ . '/../sessions';

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

if (!is_writable($path)) {
    @chmod($path, 0777);
}

session_save_path($path);
session_start();
// -----------------------------------

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$enabled  = $_POST['enabled']  ?? [];
$start    = $_POST['start']    ?? [];
$end      = $_POST['end']      ?? [];
$interval = $_POST['interval'] ?? [];

// Borrar todos los horarios actuales del profesional
$stmt = $pdo->prepare("DELETE FROM professional_schedule WHERE user_id = ?");
$stmt->execute([$user_id]);

// Insertar solo los días habilitados
$stmt = $pdo->prepare("
    INSERT INTO professional_schedule (user_id, day_of_week, start_time, end_time, interval_minutes)
    VALUES (?, ?, ?, ?, ?)
");

foreach ($enabled as $dow => $val) {

    if (empty($start[$dow]) || empty($end[$dow]) || empty($interval[$dow])) {
        continue;
    }

    $stmt->execute([
        $user_id,
        (int)$dow,
        $start[$dow],
        $end[$dow],
        (int)$interval[$dow]
    ]);
}

// Redirección corregida (ruta relativa)
redirect("horarios.php?ok=1");