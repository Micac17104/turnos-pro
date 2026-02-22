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

$page_title = 'Estadísticas';
$current    = 'estadisticas';

$mes_actual = date('Y-m');

// Turnos del mes
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments
    WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
");
$stmt->execute([$user_id, $mes_actual]);
$turnos_mes = $stmt->fetchColumn() ?: 0;

// Pacientes nuevos
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clients
    WHERE user_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?
");
$stmt->execute([$user_id, $mes_actual]);
$pacientes_nuevos = $stmt->fetchColumn() ?: 0;

// Evoluciones
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clinical_records
    WHERE user_id = ? AND DATE_FORMAT(fecha, '%Y-%m') = ?
");
$stmt->execute([$user_id, $mes_actual]);
$evoluciones_mes = $stmt->fetchColumn() ?: 0;

// Turnos pagados
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments
    WHERE user_id = ? AND payment_status = 'pagado'
      AND DATE_FORMAT(date, '%Y-%m') = ?
");
$stmt->execute([$user_id, $mes_actual]);
$turnos_pagados = $stmt->fetchColumn() ?: 0;

// Ingresos del mes
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount),0) FROM appointments
    WHERE user_id = ? AND payment_status = 'pagado'
      AND amount IS NOT NULL
      AND DATE_FORMAT(date, '%Y-%m') = ?
");
$stmt->execute([$user_id, $mes_actual]);
$ingresos_mes = $stmt->fetchColumn() ?: 0;

// Turnos por estado
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) AS total
    FROM appointments
    WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
    GROUP BY status
");
$stmt->execute([$user_id, $mes_actual]);
$turnos_estado_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Traducir estados
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

// Turnos por día
$stmt = $pdo->prepare("
    SELECT DATE(date) AS dia, COUNT(*) AS total
    FROM appointments
    WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
    GROUP BY DATE(date)
    ORDER BY dia ASC
");
$stmt->execute([$user_id, $mes_actual]);
$turnos_dia_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ingresos por día
$stmt = $pdo->prepare("
    SELECT DATE(date) AS dia, COALESCE(SUM(amount),0) AS total
    FROM appointments
    WHERE user_id = ? AND payment_status = 'pagado'
      AND amount IS NOT NULL
      AND DATE_FORMAT(date, '%Y-%m') = ?
    GROUP BY DATE(date)
    ORDER BY dia ASC
");
$stmt->execute([$user_id, $mes_actual]);
$ingresos_dia_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Métodos de pago
$stmt = $pdo->prepare("
    SELECT payment_method, COUNT(*) AS total
    FROM appointments
    WHERE user_id = ?
      AND payment_status = 'pagado'
      AND payment_method IS NOT NULL
      AND DATE_FORMAT(date, '%Y-%m') = ?
    GROUP BY payment_method
");
$stmt->execute([$user_id, $mes_actual]);
$metodos_pago_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Traducir métodos
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

// Normalizar arrays
$turnos_dia_labels   = array_column($turnos_dia_raw, 'dia');
$turnos_dia_values   = array_column($turnos_dia_raw, 'total');
$ingresos_dia_labels = array_column($ingresos_dia_raw, 'dia');
$ingresos_dia_values = array_column($ingresos_dia_raw, 'total');

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<!-- HTML Y JS SE MANTIENEN IGUAL -->

<?php require __DIR__ . '/includes/footer.php'; ?>