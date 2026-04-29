<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../pro/includes/auth-centro.php';
require __DIR__ . '/../pro/includes/db.php';

$center_id  = $_SESSION['user_id'];
$patient_id = $_POST['patient_id'] ?? null;
$texto      = trim($_POST['texto'] ?? '');

if (!$patient_id || $texto === '') {
    die("Datos incompletos.");
}

// Verificar que el paciente pertenece al centro
$stmt = $pdo->prepare("SELECT id FROM clients WHERE id = ? AND center_id = ?");
$stmt->execute([$patient_id, $center_id]);

if (!$stmt->fetch()) {
    die("No tenés permiso para este paciente.");
}

// Guardar evolución
$stmt = $pdo->prepare("
    INSERT INTO evoluciones (client_id, professional_id, texto, created_at)
    VALUES (?, ?, ?, NOW())
");
$stmt->execute([$patient_id, $center_id, $texto]);

header("Location: paciente-historia.php?id=" . $patient_id);
exit;
