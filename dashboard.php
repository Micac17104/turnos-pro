<?php
// --- SESIONES ---
$path = __DIR__ . '/sessions';
if (!is_dir($path)) mkdir($path, 0777, true);
session_save_path($path);
session_start();

// --- INCLUDES CORRECTOS ---
require __DIR__ . '/pro/includes/auth.php';
require __DIR__ . '/pro/includes/db.php';
require __DIR__ . '/pro/includes/helpers.php';

// --- CONFIG ---
$page_title = "Dashboard";
$current    = "dashboard";

// --- PREFERENCIAS DEL USUARIO ---
$stmt = $pdo->prepare("SELECT dashboard_prefs FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$prefs_json = $stmt->fetchColumn();

$prefs = $prefs_json ? json_decode($prefs_json, true) : [
    "mostrar_tarjetas"         => true,
    "mostrar_graficos"         => true,
    "mostrar_proximos_turnos"  => true,
    "mostrar_ultimos_pagos"    => true
];

// --- ESTADÍSTICAS ---
$stats = [
    "turnos_mes"       => 0,
    "pacientes_nuevos" => 0,
    "evoluciones_mes"  => 0,
    "pagos_mes"        => 0,
    "ingresos_mes"     => 0
];

// Turnos del mes
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments
    WHERE user_id = ?
      AND date IS NOT NULL
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['turnos_mes'] = (int) $stmt->fetchColumn();

// Pacientes nuevos
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clients
    WHERE user_id = ?
      AND created_at IS NOT NULL
      AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['pacientes_nuevos'] = (int) $stmt->fetchColumn();

// Evoluciones
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clinical_records
    WHERE user_id = ?
      AND fecha IS NOT NULL
      AND DATE_FORMAT(fecha, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['evoluciones_mes'] = (int) $stmt->fetchColumn();

// Turnos pagados
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments
    WHERE user_id = ?
      AND payment_status = 'pagado'
      AND date IS NOT NULL
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['pagos_mes'] = (int) $stmt->fetchColumn();

// Ingresos
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount),0) FROM appointments
    WHERE user_id = ?
      AND payment_status = 'pagado'
      AND amount IS NOT NULL
      AND date IS NOT NULL
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['ingresos_mes'] = (float) $stmt->fetchColumn();

// --- GRÁFICO: ESTADO DE TURNOS ---
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) AS total
    FROM appointments
    WHERE user_id = ?
      AND date IS NOT NULL
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
    GROUP BY status
");
$stmt->execute([$user_id]);
$raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$turnos_estado = [];
foreach ($raw ?: ['Sin datos' => 0] as $estado => $total) {
    $turnos_estado[
        match ($estado) {
            'pending'   => 'Pendiente',
            'confirmed' => 'Confirmado',
            'cancelled' => 'Cancelado',
            default     => ucfirst($estado),
        }
    ] = (int) $total;
}

// --- GRÁFICO: MÉTODOS DE PAGO ---
$stmt = $pdo->prepare("
    SELECT payment_method, COUNT(*) AS total
    FROM appointments
    WHERE user_id = ?
      AND payment_status = 'pagado'
      AND payment_method IS NOT NULL
");
$stmt->execute([$user_id]);
$raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$metodos_pago = [];
foreach ($raw ?: ['Sin datos' => 0] as $metodo => $total) {
    $metodos_pago[
        match ($metodo) {
            'efectivo'      => 'Efectivo',
            'transferencia' => 'Transferencia',
            'mercado_pago'  => 'Mercado Pago',
            default         => ucfirst($metodo),
        }
    ] = (int) $total;
}

// --- PRÓXIMOS TURNOS ---
$stmt = $pdo->prepare("
    SELECT a.*, c.name AS paciente
    FROM appointments a
    LEFT JOIN clients c ON c.id = a.client_id
    WHERE a.user_id = ?
      AND a.date >= CURDATE()
    ORDER BY a.date ASC, a.time ASC
    LIMIT 5
");
$stmt->execute([$user_id]);
$proximos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- HTML DEL DASHBOARD ---
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .cards { display: flex; gap: 20px; }
        .card { padding: 20px; background: #f5f5f5; border-radius: 10px; width: 200px; }
        .section { margin-top: 40px; }
    </style>
</head>
<body>

<h1>Dashboard</h1>

<?php if ($prefs['mostrar_tarjetas']): ?>
<div class="cards">
    <div class="card"><strong>Turnos del mes:</strong> <?= $stats['turnos_mes'] ?></div>
    <div class="card"><strong>Pacientes nuevos:</strong> <?= $stats['pacientes_nuevos'] ?></div>
    <div class="card"><strong>Evoluciones:</strong> <?= $stats['evoluciones_mes'] ?></div>
    <div class="card"><strong>Pagos:</strong> <?= $stats['pagos_mes'] ?></div>
    <div class="card"><strong>Ingresos:</strong> $<?= number_format($stats['ingresos_mes'], 2) ?></div>
</div>
<?php endif; ?>

<div class="section">
    <h2>Próximos turnos</h2>
    <?php if (empty($proximos)): ?>
        <p>No hay turnos próximos.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($proximos as $t): ?>
                <li><?= $t['date'] ?> - <?= $t['time'] ?> - <?= $t['paciente'] ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

</body>
</html>