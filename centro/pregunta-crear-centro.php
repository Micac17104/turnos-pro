<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id  = $_SESSION['user_id'];
$patient_id = $_POST['patient_id'] ?? null;

if (!$patient_id) {
    die('Paciente no encontrado.');
}

$stmt = $pdo->prepare("
    INSERT INTO clinical_questions (center_id, professional_id, question_text, type, required)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->execute([
    $center_id,                 // center_id
    $center_id,                 // professional_id (centro como usuario válido)
    $_POST['question_text'],    // question_text
    $_POST['type'],             // type
    isset($_POST['required']) ? 1 : 0 // required
]);

header("Location: paciente-historia.php?id=" . $patient_id);
exit;


