<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id  = $_SESSION['user_id'];
$patient_id = $_POST['patient_id'] ?? null;

if (!$patient_id) {
    die("Paciente no encontrado.");
}

// Guardar respuestas
foreach ($_POST as $key => $value) {

    if (strpos($key, 'q_') !== 0) {
        continue;
    }

    $question_id = str_replace('q_', '', $key);

    // Ver si ya existe respuesta
    $stmt = $pdo->prepare("
        SELECT id 
        FROM clinical_answers
        WHERE client_id = ? AND question_id = ?
    ");
    $stmt->execute([$patient_id, $question_id]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        // Actualizar respuesta existente
        $stmt = $pdo->prepare("
            UPDATE clinical_answers
            SET answer = ?, professional_id = ?
            WHERE id = ?
        ");
        $stmt->execute([$value, $center_id, $exists]);

    } else {
        // Insertar nueva respuesta
        $stmt = $pdo->prepare("
            INSERT INTO clinical_answers (client_id, question_id, answer, professional_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$patient_id, $question_id, $value, $center_id]);
    }
}

header("Location: paciente-historia.php?id=" . $patient_id);
exit;
