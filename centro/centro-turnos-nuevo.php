<?php
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id'];

$errors = [];
$success = "";

// Obtener profesionales del centro
$stmt = $pdo->prepare("
    SELECT id, name 
    FROM users
    WHERE account_type='professional'
    AND parent_center_id=?
    ORDER BY name
");
$stmt->execute([$center_id]);
$profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener pacientes del centro
$stmt = $pdo->prepare("
    SELECT id, name 
    FROM clients
    WHERE center_id = ?
    ORDER BY name
");
$stmt->execute([$center_id]);
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $prof_id = $_POST['professional_id'] ?? '';
    $client_id = $_POST['client_id'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $status = $_POST['status'] ?? 'pending';
    $motivo = trim($_POST['motivo'] ?? '');

    if ($prof_id === '' || $client_id === '' || $date === '' || $time === '') {
        $errors[] = "Todos los campos son obligatorios.";
    }

    // Validar profesional del centro
    $stmt = $pdo->prepare("
        SELECT id FROM users 
        WHERE id=? AND parent_center_id=? AND account_type='professional'
    ");
    $stmt->execute([$prof_id, $center_id]);
    if (!$stmt->fetch()) {
        $errors[] = "Profesional inválido.";
    }

    // Validar paciente del centro
    $stmt = $pdo->prepare("
        SELECT id FROM clients
        WHERE id=? AND center_id=?
    ");
    $stmt->execute([$client_id, $center_id]);
    if (!$stmt->fetch()) {
        $errors[] = "Paciente inválido.";
    }

    // Validar turno libre
    $stmt = $pdo->prepare("
        SELECT id FROM appointments
        WHERE professional_id = ? AND date = ? AND time = ? AND status IN ('confirmed','pending')
    ");
    $stmt->execute([$prof_id, $date, $time]);

    if ($stmt->fetch()) {
        $errors[] = "Ese horario ya está ocupado para ese profesional.";
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO appointments (professional_id, client_id, center_id, date, time, status, motivo)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$prof_id, $client_id, $center_id, $date, $time, $status, $motivo]);

        // Enviar email
        $stmt = $pdo->prepare("SELECT name, email FROM clients WHERE id = ?");
        $stmt->execute([$client_id]);
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($paciente && !empty($paciente['email'])) {
            require __DIR__ . '/../auth/mailer.php';

            $asunto = "Nuevo turno asignado - TurnosAura";
            $mensaje = "
                Hola {$paciente['name']},<br><br>
                Se te asignó un turno:<br><br>
                <strong>Fecha:</strong> " . date('d/m/Y', strtotime($date)) . "<br>
                <strong>Hora:</strong> " . substr($time, 0, 5) . " hs<br><br>
                Gracias por usar TurnosAura.
            ";

            enviarEmail($paciente['email'], $asunto, $mensaje);
        }

        $success = "Turno creado correctamente.";
    }
}
?>
