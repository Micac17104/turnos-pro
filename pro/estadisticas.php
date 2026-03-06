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

/**
 * Genera los últimos 6 meses en formato YYYY-MM
 */
function ultimos_6_meses() {
    $meses = [];
    for ($i = 5; $i >= 0; $i--) {
        $meses[] = date('Y-m', strtotime("-$i months"));
    }
    return $meses;
}

/**
 * Convierte YYYY-MM a "Ene 2026"
 */
function formatear_mes($ym) {
    $timestamp = strtotime($ym . "-01");
    return ucfirst(strftime("%b %Y", $timestamp));
}

$meses = ultimos_6_meses();

// --- TURNOS POR MES ---
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(date, '%Y-%m') AS mes, COUNT(*) AS total
    FROM appointments
    WHERE user_id = ?
      AND date IS NOT NULL
    GROUP BY mes
");
$stmt->execute([$user_id]);
$turnos_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Rellenar con 0
$turnos_mes = [];
foreach ($meses as $m) {
    $turnos_mes[$m] = isset($turnos_raw[$m]) ? (int)$turnos_raw[$m] : 0;
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
");
$stmt->execute([$user_id]);
$ingresos_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Rellenar con 0
$ingresos_mes = [];
foreach ($meses as $m) {
    $ingresos_mes[$m] = isset($ingresos_raw[$m]) ? (float)$ingresos_raw[$m] : 0;
}

// Etiquetas formateadas
$labels = array_map('formatear_mes', $meses);

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
Chart.defaults.font.size = 13;
Chart.defaults.color = '#334155';

// TURNOS POR MES
new Chart(document.getElementById('chartTurnosMes'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Turnos',
            data: <?= json_encode(array_values($turnos_mes)) ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.2)',
            borderWidth: 2,
            tension: 0.3
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom' } }
    }
});

// INGRESOS POR MES
new Chart(document.getElementById('chartIngresosMes'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Ingresos',
            data: <?= json_encode(array_values($ingresos_mes)) ?>,
            backgroundColor: '#10b981',
            borderColor: '#059669',
            borderWidth: 2
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>