<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Estadísticas';
$current = 'estadisticas';

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
$turnos_dia_labels = array_column($turnos_dia_raw, 'dia');
$turnos_dia_values = array_column($turnos_dia_raw, 'total');

$ingresos_dia_labels = array_column($ingresos_dia_raw, 'dia');
$ingresos_dia_values = array_column($ingresos_dia_raw, 'total');

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Estadísticas del mes</h1>

    <!-- TARJETAS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div class="bg-white p-5 rounded-xl shadow border">
            <p class="text-xs text-slate-500">Turnos del mes</p>
            <p class="text-2xl font-bold"><?= $turnos_mes ?></p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border">
            <p class="text-xs text-slate-500">Pacientes nuevos</p>
            <p class="text-2xl font-bold"><?= $pacientes_nuevos ?></p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border">
            <p class="text-xs text-slate-500">Evoluciones</p>
            <p class="text-2xl font-bold"><?= $evoluciones_mes ?></p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border">
            <p class="text-xs text-slate-500">Ingresos del mes</p>
            <p class="text-2xl font-bold text-green-600">$<?= number_format($ingresos_mes, 2, ',', '.') ?></p>
        </div>
    </div>

    <!-- GRÁFICOS SUPERIORES -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Turnos por estado</h3>
            <canvas id="chartEstados"></canvas>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Métodos de pago</h3>
            <canvas id="chartMetodos"></canvas>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Turnos pagados</h3>
            <p class="text-3xl font-bold"><?= $turnos_pagados ?></p>
            <p class="text-xs text-slate-500">Turnos con pago registrado este mes.</p>
        </div>

    </div>

    <!-- GRÁFICOS DE LÍNEA -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Turnos por día</h3>
            <canvas id="chartTurnosDia"></canvas>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Ingresos por día</h3>
            <canvas id="chartIngresosDia"></canvas>
        </div>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// CONFIGURACIÓN GLOBAL PARA MEJOR LECTURA
Chart.defaults.font.size = 13;
Chart.defaults.color = '#334155';

// TURNOS POR ESTADO
new Chart(document.getElementById('chartEstados'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($turnos_estado)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($turnos_estado)) ?>,
            backgroundColor: ['#3b82f6', '#10b981', '#ef4444'],
            borderColor: '#ffffff',
            borderWidth: 2
        }]
    },
    options: {
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// MÉTODOS DE PAGO
new Chart(document.getElementById('chartMetodos'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_keys($metodos_pago)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($metodos_pago)) ?>,
            backgroundColor: ['#0ea5e9', '#6366f1', '#f59e0b'],
            borderColor: '#ffffff',
            borderWidth: 2
        }]
    },
    options: {
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// TURNOS POR DÍA
new Chart(document.getElementById('chartTurnosDia'), {
    type: 'line',
    data: {
        labels: <?= json_encode($turnos_dia_labels) ?>,
        datasets: [{
            label: 'Turnos',
            data: <?= json_encode($turnos_dia_values) ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.15)',
            tension: 0.3,
            fill: true,
            borderWidth: 2
        }]
    }
});

// INGRESOS POR DÍA
new Chart(document.getElementById('chartIngresosDia'), {
    type: 'line',
    data: {
        labels: <?= json_encode($ingresos_dia_labels) ?>,
        datasets: [{
            label: 'Ingresos',
            data: <?= json_encode($ingresos_dia_values) ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.15)',
            tension: 0.3,
            fill: true,
            borderWidth: 2
        }]
    }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>