<?php
// /pro/agenda.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// -----------------------------------

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$enabled  = $_POST['enabled']  ?? [];
$start    = $_POST['start']    ?? [];
$end      = $_POST['end']      ?? [];
$interval = $_POST['interval'] ?? [];

// Borrar todos los horarios actuales del profesional
$stmt = $pdo->prepare("DELETE FROM schedules WHERE user_id = ?");
$stmt->execute([$user_id]);

// Insertar solo los días habilitados
$stmt = $pdo->prepare("
    INSERT INTO schedules (user_id, day_of_week, start_time, end_time, slot_duration)
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