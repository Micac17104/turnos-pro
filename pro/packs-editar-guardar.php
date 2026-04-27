<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$pack_id = $_POST['id'] ?? null;
$name = trim($_POST['name']);
$total = intval($_POST['total_sessions']);
$price = floatval($_POST['price']);
$active = intval($_POST['active']);

if (!$pack_id) {
    die("Pack inválido.");
}

// Verificar que el pack pertenece al profesional
$stmt = $pdo->prepare("
    SELECT id FROM packs
    WHERE id = ? AND owner_type = 'professional' AND owner_id = ?
");
$stmt->execute([$pack_id, $user_id]);
if (!$stmt->fetch()) {
    die("No tenés permiso para editar este pack.");
}

// Actualizar pack
$stmt = $pdo->prepare("
    UPDATE packs
    SET name = ?, total_sessions = ?, price = ?, active = ?
    WHERE id = ?
");
$stmt->execute([$name, $total, $price, $active, $pack_id]);

header("Location: packs.php?edit=1");
exit;
