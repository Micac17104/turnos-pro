<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';

// Verificar login del paciente
if (!isset($_SESSION['paciente_id'])) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "NO_AUTH"]);
    exit;
}

$paciente_id = $_SESSION['paciente_id'];
$turno_id = $_GET['id'] ?? null;

if (!$turno_id) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "NO_ID"]);
    exit;
}

// Verificar que el turno pertenece al paciente
$stmt = $pdo->prepare("
    SELECT * FROM appointments 
    WHERE id = ? AND client_id = ?
");
$stmt->execute([$turno_id, $paciente_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "NOT_FOUND"]);
    exit;
}

// Cancelar turno
$stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
$stmt->execute([$turno_id]);

echo json_encode(["status" => "ok"]);