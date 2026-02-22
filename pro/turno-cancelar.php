<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$turno_id = $_GET['id'] ?? null;

if (!$turno_id) {
    die("Turno no encontrado.");
}

// Verificar que el turno pertenece al profesional
$stmt = $pdo->prepare("
    SELECT id 
    FROM appointments
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$turno_id, $user_id]);
$turno = $stmt->fetch();

if (!$turno) {
    die("No tienes permiso para cancelar este turno.");
}

// Cancelar turno
$stmt = $pdo->prepare("
    UPDATE appointments
    SET status = 'cancelled'
    WHERE id = ?
");
$stmt->execute([$turno_id]);

// Redirección corregida (ruta relativa)
redirect('agenda.php?cancel=1');