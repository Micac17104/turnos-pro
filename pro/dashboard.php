<?php
// DEBUG (podés quitarlo cuando funcione)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- SESIONES ---
$path = __DIR__ . '/../sessions'; // dashboard está en /pro → subir 1 nivel
if (!is_dir($path)) mkdir($path, 0777, true);
session_save_path($path);
session_start();

// --- INCLUDES ---
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

// --- CONFIG ---
$page_title = "Dashboard";
$current    = "dashboard";

// --- PREFERENCIAS ---
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

// --- ESTADO DE TURNOS ---
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

// --- MÉTODOS DE PAGO (CORREGIDO) ---
$stmt = $pdo->prepare("
    SELECT payment_method, COUNT(*) AS total
    FROM appointments
    WHERE user_id = ?
      AND payment_status = 'pagado'
      AND payment_method IS NOT NULL
    GROUP BY payment_method
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

?>

<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<div class="content">
    <h1>Dashboard</h1>

    <div class="cards">
        <div class="card"><strong>Turnos del mes:</strong> <?= $stats['turnos_mes'] ?></div>
        <div class="card"><strong>Pacientes nuevos:</strong> <?= $stats['pacientes_nuevos'] ?></div>
        <div class="card"><strong>Evoluciones:</strong> <?= $stats['evoluciones_mes'] ?></div>
        <div class="card"><strong>Pagos:</strong> <?= $stats['pagos_mes'] ?></div>
        <div class="card"><strong>Ingresos:</strong> $<?= number_format($stats['ingresos_mes'], 2) ?></div>
    </div>

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

<?php include __DIR__ . '/layout/footer.php'; ?>