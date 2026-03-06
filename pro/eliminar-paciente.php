<?php
session_start();
require_once '../includes/db.php';

// Validar sesión del profesional
if (!isset($_SESSION['pro_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$pro_id = $_SESSION['pro_id'];

// Validar que venga el ID del paciente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: pacientes.php?error=missing_id");
    exit();
}

$paciente_id = intval($_GET['id']);

// Eliminar el paciente SOLO si pertenece al profesional logueado
$stmt = $conn->prepare("DELETE FROM pacientes WHERE id = ? AND pro_id = ?");
$stmt->bind_param("ii", $paciente_id, $pro_id);

if ($stmt->execute()) {
    header("Location: pacientes.php?success=deleted");
    exit();
} else {
    header("Location: pacientes.php?error=delete_failed");
    exit();
}