<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$professional_id = $user_id;
$text = trim($_POST['question_text'] ?? '');
$type = $_POST['type'] ?? 'text';
$required = isset($_POST['required']) ? 1 : 0;

if (!$text) {
    die("La pregunta no puede estar vacía.");
}

$stmt = $pdo->prepare("
    INSERT INTO clinical_questions (professional_id, question_text, type, required)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$professional_id, $text, $type, $required]);

// Volver a la historia clínica del paciente
$patient_id = $_GET['id'] ?? $_POST['patient_id'] ?? null;

if ($patient_id) {
    header("Location: paciente-historia.php?id=" . $patient_id);
} else {
    header("Location: pacientes.php");
}

exit;
