<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id'];
$patient_id = $_POST['patient_id'];

// Crear pregunta del centro (professional_id = 0)
$stmt = $pdo->prepare("
    INSERT INTO clinical_questions (center_id, professional_id, question_text, type, required)
    VALUES (?, 0, ?, ?, ?)
");

$stmt->execute([
    $center_id,
    $_POST['question_text'],
    $_POST['type'],
    isset($_POST['required']) ? 1 : 0
]);

header("Location: paciente-historia.php?id=" . $patient_id);
exit;
