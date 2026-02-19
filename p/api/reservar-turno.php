<?php
require __DIR__ . '/../../pro/includes/db.php';

header("Content-Type: application/json");

$user_id = $_POST['user_id'] ?? null;
$date    = $_POST['date'] ?? null;
$time    = $_POST['time'] ?? null;
$name    = $_POST['name'] ?? null;
$phone   = $_POST['phone'] ?? null;
$email   = $_POST['email'] ?? null;

/* Validación básica */
if (!$user_id || !$date || !$time || !$name || !$phone) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

/* No permitir días pasados */
if (strtotime($date) < strtotime(date("Y-m-d"))) {
    echo json_encode(["status" => "error", "message" => "No se pueden reservar días pasados"]);
    exit;
}

/* Obtener día de la semana */
$dayOfWeek = date('w', strtotime($date));

/* 1) Verificar que el profesional atiende ese día */
$stmt = $pdo->prepare("
    SELECT start_time, end_time, interval_minutes
    FROM professional_schedule
    WHERE user_id = ? AND day_of_week = ?
");
$stmt->execute([$user_id, $dayOfWeek]);
$schedule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$schedule) {
    echo json_encode(["status" => "error", "message" => "El profesional no atiende este día"]);
    exit;
}

/* 2) Verificar que el horario pertenece a su agenda */
$start = strtotime($schedule['start_time']);
$end   = strtotime($schedule['end_time']);
$interval = $schedule['interval_minutes'] * 60;

$validTimes = [];
for ($t = $start; $t < $end; $t += $interval) {
    $validTimes[] = date('H:i', $t);
}

if (!in_array($time, $validTimes)) {
    echo json_encode(["status" => "error", "message" => "Horario inválido"]);
    exit;
}

/* 3) Verificar excepciones */
$stmt = $pdo->prepare("
    SELECT * FROM professional_exceptions
    WHERE user_id = ? AND date = ?
");
$stmt->execute([$user_id, $date]);
$exceptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($exceptions as $ex) {
    if ($ex['type'] === 'block') {
        /* Día completo bloqueado */
        if (!$ex['start_time']) {
            echo json_encode(["status" => "error", "message" => "El día está bloqueado"]);
            exit;
        }

        /* Rango horario bloqueado */
        $ts = strtotime($ex['start_time']);
        $te = strtotime($ex['end_time']);
        $selected = strtotime($time);

        if ($selected >= $ts && $selected < $te) {
            echo json_encode(["status" => "error", "message" => "El horario está bloqueado"]);
            exit;
        }
    }
}

/* 4) Verificar si el turno ya está ocupado */
$stmt = $pdo->prepare("
    SELECT id FROM appointments
    WHERE user_id = ? AND date = ? AND time = ? AND status != 'cancelled'
");
$stmt->execute([$user_id, $date, $time]);

if ($stmt->fetch()) {
    echo json_encode(["status" => "error", "message" => "Turno ocupado"]);
    exit;
}

/* 5) Insertar turno */
$stmt = $pdo->prepare("
    INSERT INTO appointments (user_id, name, phone, email, date, time, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
");

$stmt->execute([
    $user_id,
    $name,
    $phone,
    $email,
    $date,
    $time
]);

echo json_encode(["status" => "success", "message" => "Turno reservado"]);