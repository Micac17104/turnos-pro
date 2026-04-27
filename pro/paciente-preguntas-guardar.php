<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$patient_id = $_POST['patient_id'] ?? null;

if (!$patient_id) {
    die("Paciente no encontrado.");
}

// Verificar que el paciente pertenece al profesional
$stmt = $pdo->prepare("SELECT id FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$patient_id, $user_id]);
if (!$stmt->fetch()) {
    die("Paciente no pertenece a este profesional.");
}

// Obtener preguntas del profesional
$stmt = $pdo->prepare("
    SELECT id
    FROM clinical_questions
    WHERE professional_id = ?
");
$stmt->execute([$user_id]);
$preguntas = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!empty($preguntas)) {
    foreach ($preguntas as $qid) {
        $field = 'q_' . $qid;
        $answer = trim($_POST[$field] ?? '');

        // Ver si ya existe respuesta
        $stmt = $pdo->prepare("
            SELECT id
            FROM clinical_answers
            WHERE client_id = ? AND professional_id = ? AND question_id = ?
        ");
        $stmt->execute([$patient_id, $user_id, $qid]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            $stmt = $pdo->prepare("
                UPDATE clinical_answers
                SET answer = ?
                WHERE id = ?
            ");
            $stmt->execute([$answer, $existing]);
        } else {
            if ($answer !== '') {
                $stmt = $pdo->prepare("
                    INSERT INTO clinical_answers (client_id, professional_id, question_id, answer)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$patient_id, $user_id, $qid, $answer]);
            }
        }
    }
}

header("Location: paciente-historia.php?id=" . $patient_id);
exit;
