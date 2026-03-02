<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

// Datos del formulario
$name  = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');

// Validación básica
if ($name === '' || $phone === '') {
    die("Datos incompletos.");
}

// Verificar si ya existe un paciente con ese email para este profesional
if ($email !== '') {
    $stmt = $pdo->prepare("
        SELECT id FROM clients 
        WHERE email = ? AND user_id = ?
    ");
    $stmt->execute([$email, $user_id]);
    $existe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existe) {
        redirect("pacientes.php?msg=existe");
        exit;
    }
}

// Insertar paciente SIN cuenta (password NULL)
$stmt = $pdo->prepare("
    INSERT INTO clients (user_id, name, phone, email, password)
    VALUES (?, ?, ?, ?, NULL)
");
$stmt->execute([$user_id, $name, $phone, $email]);

redirect("pacientes.php");