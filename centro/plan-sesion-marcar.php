<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$sesion_id = $_GET['id'];
$plan_id = $_GET['plan'];
$paciente = $_GET['paciente'];

$stmt = $pdo->prepare("
    UPDATE plan_sesiones
    SET realizada = 1, realizada_at = NOW()
    WHERE id = ?
");
$stmt->execute([$sesion_id]);

header("Location: planes-estetica.php?id=" . $paciente);
exit;
