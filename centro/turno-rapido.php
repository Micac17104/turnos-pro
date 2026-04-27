<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id']; // CORREGIDO

// Crear paciente rápido con valores mínimos válidos
$stmt = $pdo->prepare("
    INSERT INTO clients (name, email, phone, dni, center_id)
    VALUES ('Paciente sin nombre', '', '', '', ?)
");
$stmt->execute([$center_id]);

$client_id = $pdo->lastInsertId();

// Redirigir a crear turno
header("Location: turno-nuevo.php?client_id=" . $client_id);
exit;
