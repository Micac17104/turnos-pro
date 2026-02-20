<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';

if (!isset($_SESSION['paciente_id'])) {
    header("Location: /turnos-pro/public/login-paciente.php");
    exit;
}

$paciente_id = $_SESSION['paciente_id'];

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$name || !$email) {
    die("Datos incompletos.");
}

// Obtener registro actual
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$paciente_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

// Si el paciente está en user_id = 0, verificar si el profesional ya lo cargó
if ($paciente['user_id'] == 0) {

    $stmt2 = $pdo->prepare("
        SELECT * FROM clients
        WHERE email = ? AND user_id != 0
    ");
    $stmt2->execute([$email]);
    $clienteProfesional = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($clienteProfesional) {

        // Actualizar el registro del profesional
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt3 = $pdo->prepare("
                UPDATE clients
                SET name = ?, email = ?, phone = ?, password = ?
                WHERE id = ?
            ");
            $stmt3->execute([$name, $email, $phone, $hash, $clienteProfesional['id']]);

        } else {
            $stmt3 = $pdo->prepare("
                UPDATE clients
                SET name = ?, email = ?, phone = ?
                WHERE id = ?
            ");
            $stmt3->execute([$name, $email, $phone, $clienteProfesional['id']]);
        }

        // Actualizar sesión al registro correcto
        $_SESSION['paciente_id'] = $clienteProfesional['id'];
        $_SESSION['paciente_nombre'] = $name;

        header("Location: /turnos-pro/public/paciente-perfil.php?ok=1");
        exit;
    }
}

// Si no hay registro del profesional, actualizar el actual
if ($password !== '') {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        UPDATE clients
        SET name = ?, email = ?, phone = ?, password = ?
        WHERE id = ?
    ");
    $stmt->execute([$name, $email, $phone, $hash, $paciente_id]);

} else {
    $stmt = $pdo->prepare("
        UPDATE clients
        SET name = ?, email = ?, phone = ?
        WHERE id = ?
    ");
    $stmt->execute([$name, $email, $phone, $paciente_id]);
}

$_SESSION['paciente_nombre'] = $name;

header("Location: /turnos-pro/public/paciente-perfil.php?ok=1");
exit;