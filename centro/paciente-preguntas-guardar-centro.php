<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/db.php';

require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id'];
$patient_id = $_POST['patient_id'] ?? null;

if (!$patient_id) {
    die("Paciente inválido.");
}

// Obtener todas las preguntas del centro
$stmt = $pdo->prepare("
    SELECT id
    FROM clinical_questions
    WHERE center_id = ?
");
$stmt->execute([$center_id]);
$preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Guardar cada respuesta
foreach ($preguntas as $q) {
    $qid = $q['id'];
    $field = "q_" . $qid;
    $answer = trim($_POST[$field] ?? '');

    // Verificar si ya existe respuesta
    $stmt = $pdo->prepare("
        SELECT id
        FROM clinical_answers
        WHERE client_id = ? AND center_id = ? AND question_id = ?
    ");
    $stmt->execute([$patient_id, $center_id, $qid]);
    $existe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existe) {
        // Update
        $stmt = $pdo->prepare("
            UPDATE clinical_answers
            SET answer = ?
            WHERE id = ?
        ");
        $stmt->execute([$answer, $existe['id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare("
            INSERT INTO clinical_answers (client_id, center_id, question_id, answer)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$patient_id, $center_id, $qid, $answer]);
    }
}

header("Location: paciente-historia.php?id=" . $patient_id . "&ok=1");
exit;
