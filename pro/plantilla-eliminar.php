<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$user_id = $_SESSION['user_id'];
$id = require_param($_GET, 'id');

// Validar que la plantilla pertenece al profesional
$stmt = $pdo->prepare("
    SELECT id FROM clinical_templates
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$id, $user_id]);

if (!$stmt->fetch()) {
    die("No tienes permiso para eliminar esta plantilla.");
}

// Eliminar plantilla
$stmt = $pdo->prepare("DELETE FROM clinical_templates WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);

redirect('historia-plantillas.php?deleted=1');