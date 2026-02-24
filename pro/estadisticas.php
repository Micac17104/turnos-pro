<?php
// /pro/estadisticas.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Estadísticas';
$current    = 'estadisticas';

// --- TURNOS POR MES ---
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(date, '%Y-%m') AS mes, COUNT(*) AS total
    FROM appointments
    WHERE user_id = ?
      AND date IS NOT NULL
    GROUP BY mes
    ORDER BY mes ASC
");
$stmt->execute([$user_id]);
$turnos_mes_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

if (empty($turnos_mes_raw)) {
    $turnos_mes_raw = ['Sin datos' => 0];
}

// --- INGRESOS POR MES ---
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(date, '%Y-%m') AS mes, COALESCE(SUM(amount),0) AS total
    FROM appointments
    WHERE user_id = ?
      AND payment_status = 'pagado'
      AND amount IS NOT NULL
      AND date IS NOT NULL
    GROUP BY mes
    ORDER BY mes ASC
");
$stmt->execute([$user_id]);
$ingresos_mes_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

if (empty($ingresos_mes_raw)) {
    $ingresos_mes_raw = ['Sin datos' => 0];
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Estadísticas</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- TURNOS POR MES -->
        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Turnos por mes</h3>
            <canvas id="chartTurnosMes"></canvas>
        </div>

        <!-- INGRESOS POR MES -->
        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Ingresos por mes</h3>
            <canvas id="chartIngresosMes"></canvas>
        </div>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// CONFIGURACIÓN GLOBAL
Chart.defaults.font.size = 13;
Chart.defaults.color = '#334155';

// --- TURNOS POR MES ---
new Chart(document.getElementById('chartTurnosMes'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_keys($turnos_mes_raw)) ?>,
        datasets: [{
            label: 'Turnos',
            data: <?= json_encode(array_values($turnos_mes_raw)) ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.2)',
            borderWidth: 2,
            tension: 0.3
        }]
    },
    options: {
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// --- INGRESOS POR MES ---
new Chart(document.getElementById('chartIngresosMes'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($ingresos_mes_raw)) ?>,
        datasets: [{
            label: 'Ingresos',
            data: <?= json_encode(array_values($ingresos_mes_raw)) ?>,
            backgroundColor: '#10b981',
            borderColor: '#059669',
            borderWidth: 2
        }]
    },
    options: {
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>