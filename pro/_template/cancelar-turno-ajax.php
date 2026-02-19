<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    http_response_code(403);
    exit("NO_AUTH");
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit("NO_ID");
}

$turno_id = intval($_GET['id']);

// Verificar que el turno pertenece al profesional
$stmt = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND user_id = ?");
$stmt->execute([$turno_id, $user_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    http_response_code(404);
    exit("NOT_FOUND");
}

// Cancelar turno
$stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
$stmt->execute([$turno_id]);

echo "OK";