<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$client_id = $_POST['client_id'] ?? null;
$pack_id   = $_POST['pack_id'] ?? null;

if (!$client_id || !$pack_id) {
    die("Datos incompletos.");
}

// Verificar que el paciente pertenece al profesional
$stmt = $pdo->prepare("SELECT id FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$client_id, $user_id]);
if (!$stmt->fetch()) {
    die("Paciente no pertenece a este profesional.");
}

// Verificar que el pack pertenece al profesional
$stmt = $pdo->prepare("
    SELECT id FROM packs
    WHERE id = ? AND owner_type = 'professional' AND owner_id = ?
");
$stmt->execute([$pack_id, $user_id]);
if (!$stmt->fetch()) {
    die("Pack no pertenece a este profesional.");
}

// Asignar pack
$stmt = $pdo->prepare("
    INSERT INTO packs_clients (pack_id, client_id)
    VALUES (?, ?)
");
$stmt->execute([$pack_id, $client_id]);

header("Location: paciente-historia.php?id=" . $client_id);
exit;
