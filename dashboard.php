<?php
// --- FIX DEFINITIVO PARA RAILWAY ---
$path = __DIR__ . '/../sessions';

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

if (!is_writable($path)) {
    @chmod($path, 0777);
}

session_save_path($path);
session_start();
// -----------------------------------

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Dashboard';
$current    = 'dashboard';

// --- CARGAR PREFERENCIAS DEL USUARIO ---
$stmt = $pdo->prepare("SELECT dashboard_prefs FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$prefs_json = $stmt->fetchColumn();

$prefs = $prefs_json ? json_decode($prefs_json, true) : [
    "mostrar_tarjetas"         => true,
    "mostrar_graficos"         => true,
    "mostrar_proximos_turnos"  => true,
    "mostrar_ultimos_pagos"    => true
];

// --- ESTADÍSTICAS DEL MES ---
$stats = [];

// Turnos del mes
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments
    WHERE user_id = ?
      AND date IS NOT NULL
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['turnos_mes'] = $stmt->fetchColumn() ?: 0;

// Pacientes nuevos
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clients
    WHERE user_id = ?
      AND created_at IS NOT NULL
      AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['pacientes_nuevos'] = $stmt->fetchColumn() ?: 0;

// Evoluciones del mes
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clinical_records
    WHERE user_id = ?
      AND fecha IS NOT NULL
      AND DATE_FORMAT(fecha, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['evoluciones_mes'] = $stmt->fetchColumn() ?: 0;

// Turnos pagados
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments
    WHERE user_id = ?
      AND payment_status = 'pagado'
      AND date IS NOT NULL
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['pagos_mes'] = $stmt->fetchColumn() ?: 0;

// Ingresos del mes
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount),0) FROM appointments
    WHERE user_id = ?
      AND payment_status = 'pagado'
      AND amount IS NOT NULL
      AND date IS NOT NULL
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['ingresos_mes'] = $stmt->fetchColumn() ?: 0;

// --- GRÁFICO: TURNOS POR ESTADO ---
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) AS total
    FROM appointments
    WHERE user_id = ?
      AND date IS NOT NULL
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
    GROUP BY status
");
$stmt->execute([$user_id]);
$turnos_estado_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

if (empty($turnos_estado_raw)) {
    $turnos_estado_raw = ['Sin datos' => 0];
}

$turnos_estado = [];
foreach ($turnos_estado_raw as $estado => $total) {
    $label = match ($estado) {
        'pending'   => 'Pendiente',
        'confirmed' => 'Confirmado',
        'cancelled' => 'Cancelado',
        default     => ucfirst($estado),
    };
    $turnos_estado[$label] = $total;
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
$metodos_pago_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

if (empty($metodos_pago_raw)) {
    $metodos_pago_raw = ['Sin datos' => 0];
}

$metodos_pago = [];
foreach ($metodos_pago_raw as $metodo => $total) {
    $label = match ($metodo) {
        'efectivo'      => 'Efectivo',
        'transferencia' => 'Transferencia',
        'mercado_pago'  => 'Mercado Pago',
        default         => ucfirst($metodo),
    };
    $metodos_pago[$label] = $total;
}

// --- PRÓXIMOS TURNOS ---
$stmt = $pdo->prepare("
    SELECT a.*, c.name AS paciente
    FROM appointments a
    LEFT JOIN clients c ON c.id = a.client_id
    WHERE a.user_id = ?
      AND a.date IS NOT NULL
      AND a.date >= CURDATE()
    ORDER BY a.date ASC, a.time ASC
    LIMIT 5
");
$stmt->execute([$user_id]);
$proximos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// CHECKPOINT VISUAL
echo "<h1 style='padding:20px;background:#d1ffd1'>PARTE 1 OK</h1>";
exit;