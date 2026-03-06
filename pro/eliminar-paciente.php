<?php
session_start();

require_once __DIR__ . '/includes/db.php';

// Validar sesión del profesional
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$pro_id = $_SESSION['user_id'];

// Validar que venga el ID del paciente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: pacientes.php?error=missing_id");
    exit();
}

$paciente_id = intval($_GET['id']);

// Eliminar el paciente SOLO si pertenece al profesional logueado
$stmt = $pdo->prepare("DELETE FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$paciente_id, $pro_id]);

header("Location: pacientes.php?success=deleted");
exit();