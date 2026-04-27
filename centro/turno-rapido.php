<?php
session_start();
require __DIR__ . '/../pro/includes/db.php';


$center_id = $_SESSION['center_id'];

// Crear paciente automático
$stmt = $pdo->prepare("
    INSERT INTO clients (name, email, phone, dni, center_id)
    VALUES ('Paciente sin nombre', NULL, NULL, NULL, ?)
");
$stmt->execute([$center_id]);

$client_id = $pdo->lastInsertId();

// Redirigir a crear turno
header("Location: turno-nuevo.php?client_id=" . $client_id);
exit;
