<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id']; 

// Crear paciente rápido con DNI único
$random_dni = 'RAPIDO-' . time();

$stmt = $pdo->prepare("
    INSERT INTO clients (name, email, phone, dni, center_id)
    VALUES ('Paciente sin nombre', '', '', ?, ?)
");
$stmt->execute([$random_dni, $center_id]);

$client_id = $pdo->lastInsertId();

// Redirigir al archivo correcto de creación de turnos
header("Location: centro-turnos-nuevo.php?client_id=" . $client_id);
exit;
