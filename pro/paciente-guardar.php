<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

// Datos del formulario
$name            = trim($_POST['name'] ?? '');
$dni             = trim($_POST['dni'] ?? '');
$phone           = trim($_POST['phone'] ?? '');
$email           = trim($_POST['email'] ?? '');
$is_recurring    = isset($_POST['is_recurring']) ? 1 : 0;
$recurring_day   = trim($_POST['recurring_day'] ?? '');
$recurring_time  = trim($_POST['recurring_time'] ?? '');
$recurring_until = trim($_POST['recurring_until'] ?? '');

// Validación básica
if ($name === '' || $phone === '' || $dni === '') {
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

// Insertar paciente con datos de recurrencia
$stmt = $pdo->prepare("
    INSERT INTO clients (user_id, name, dni, phone, email, password, is_recurring, recurring_day, recurring_time, recurring_until)
    VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?, ?)
");
$stmt->execute([
    $user_id,
    $name,
    $dni,
    $phone,
    $email,
    $is_recurring,
    $recurring_day !== '' ? $recurring_day : null,
    $recurring_time !== '' ? $recurring_time : null,
    $recurring_until !== '' ? $recurring_until : null
]);

redirect("pacientes.php");
