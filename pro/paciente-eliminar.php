<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$user_id = $_SESSION['user_id'];
$id = require_param($_GET, 'id');

// Validar que el paciente pertenece al profesional
$stmt = $pdo->prepare("
    SELECT id FROM clients
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$id, $user_id]);

if (!$stmt->fetch()) {
    die("No tienes permiso para eliminar este paciente.");
}

// Eliminar paciente
$stmt = $pdo->prepare("DELETE FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);

redirect("pacientes.php");
