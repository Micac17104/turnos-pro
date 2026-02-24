<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = "Dashboard";
$current = "dashboard";

// Cargar preferencias
$stmt = $pdo->prepare("SELECT dashboard_prefs FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$prefs_json = $stmt->fetchColumn();

$prefs = $prefs_json ? json_decode($prefs_json, true) : [
    "mostrar_tarjetas" => true,
    "mostrar_graficos" => true,
    "mostrar_proximos_turnos" => true,
    "mostrar_ultimos_pagos" => true
];

// --- ESTADÍSTICAS ---
$stats = [
    "turnos_mes" => 0,
    "pacientes_nuevos" => 0,
    "evoluciones_mes" => 0,
    "pagos_mes" => 0,
    "ingresos_mes" => 0
];

// Turnos del mes
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments
    WHERE user_id = ?
    AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['turnos_mes'] = (int)$stmt->fetchColumn();

// Pacientes nuevos
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clients
    WHERE user_id = ?
    AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['pacientes_nuevos'] = (int)$stmt->fetchColumn();

// Evoluciones
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clinical_records
    WHERE user_id = ?
    AND DATE_FORMAT(fecha, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['evoluciones_mes'] = (int)$stmt->fetchColumn();

// Pagos del mes
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments
    WHERE user_id = ?
    AND payment_status = 'pagado'
    AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['pagos_mes'] = (int)$stmt->fetchColumn();

// Ingresos del mes
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount),0) FROM appointments
    WHERE user_id = ?
    AND payment_status = 'pagado'
    AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['ingresos_mes'] = (float)$stmt->fetchColumn();

// --- MÉTODOS DE PAGO ---
$stmt = $pdo->prepare("
    SELECT payment_method, COUNT(*) AS total
    FROM appointments
    WHERE user_id = ?
    AND payment_status = 'pagado'
    AND payment_method IS NOT NULL
    GROUP BY payment_method
");
$stmt->execute([$user_id]);
$metodos_pago = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

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

// --- ÚLTIMOS PAGOS ---
$stmt = $pdo->prepare("
    SELECT a.date, a.time, a.amount, c.name AS paciente
    FROM appointments a
    LEFT JOIN clients c ON c.id = a.client_id
    WHERE a.user_id = ?
      AND a.payment_status = 'pagado'
    ORDER BY a.date DESC, a.time DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 p-8">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>

        <a href="dashboard-preferencias.php"
           class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
            Preferencias
        </a>
    </div>

    <!-- TARJETAS -->
    <?php if ($prefs['mostrar_tarjetas']): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">

        <div class="bg-white p-5 rounded-xl shadow border">
            <div class="text-sm text-slate-500">Turnos del mes</div>
            <div class="text-2xl font-bold"><?= $stats['turnos_mes'] ?></div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border">
            <div class="text-sm text-slate-500">Pacientes nuevos</div>
            <div class="text-2xl font-bold"><?= $stats['pacientes_nuevos'] ?></div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border">
            <div class="text-sm text-slate-500">Evoluciones</div>
            <div class="text-2xl font-bold"><?= $stats['evoluciones_mes'] ?></div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border">
            <div class="text-sm text-slate-500">Pagos</div>
            <div class="text-2xl font-bold"><?= $stats['pagos_mes'] ?></div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border">
            <div class="text-sm text-slate-500">Ingresos</div>
            <div class="text-2xl font-bold">$<?= number_format($stats['ingresos_mes'], 2) ?></div>
        </div>

    </div>
    <?php endif; ?>

    <!-- GRÁFICOS -->
    <?php if ($prefs['mostrar_graficos']): ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Turnos por método de pago</h3>
            <canvas id="chartTurnos"></canvas>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Ingresos por método de pago</h3>
            <canvas id="chartIngresos"></canvas>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
    Chart.defaults.font.size = 13;
    Chart.defaults.color = '#334155';

    new Chart(document.getElementById('chartTurnos'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($metodos_pago)) ?>,
            datasets: [{
                label: 'Turnos',
                data: <?= json_encode(array_values($metodos_pago)) ?>,
                backgroundColor: '#3b82f6'
            }]
        }
    });

    new Chart(document.getElementById('chartIngresos'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($metodos_pago)) ?>,
            datasets: [{
                label: 'Ingresos',
                data: <?= json_encode(array_values($metodos_pago)) ?>,
                backgroundColor: '#10b981'
            }]
        }
    });
    </script>

    <?php endif; ?>

    <!-- ÚLTIMOS PAGOS -->
    <?php if ($prefs['mostrar_ultimos_pagos']): ?>
    <div class="bg-white p-6 rounded-xl shadow border mb-8">
        <h2 class="text-xl font-semibold mb-4">Últimos pagos</h2>

        <?php if (empty($pagos)): ?>
            <p class="text-slate-500">No hay pagos recientes.</p>
        <?php else: ?>
            <ul class="space-y-2">
                <?php foreach ($pagos as $p): ?>
                    <li class="p-3 bg-slate-100 rounded-lg">
                        <strong>$<?= number_format($p['amount'], 2) ?></strong>
                        — <?= $p['date'] ?> <?= $p['time'] ?><br>
                        <span class="text-slate-600"><?= $p['paciente'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>