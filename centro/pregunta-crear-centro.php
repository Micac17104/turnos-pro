<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id'];

$text = trim($_POST['question_text'] ?? '');
$type = $_POST['type'] ?? 'text';
$required = isset($_POST['required']) ? 1 : 0;
$patient_id = $_POST['patient_id'] ?? null;

if (!$text) {
    die("La pregunta no puede estar vacía.");
}

// Insertar pregunta del centro
$stmt = $pdo->prepare("
    INSERT INTO clinical_questions (center_id, question_text, type, required)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$center_id, $text, $type, $required]);

// Volver a la historia clínica del paciente
if ($patient_id) {
    header("Location: paciente-historia.php?id=" . $patient_id);
} else {
    header("Location: pacientes.php");
}

exit;
