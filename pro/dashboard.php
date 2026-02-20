<?php
// --- FIX DEFINITIVO PARA RAILWAY ---
$path = __DIR__ . '/../sessions';

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

if (!is_writable($path)) {
    chmod($path, 0777);
}

session_save_path($path);
session_start();
// -----------------------------------

// VALIDAR SESIÓN DEL PROFESIONAL
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Dashboard';
$current = 'dashboard';

// --- CARGAR PREFERENCIAS DEL USUARIO ---
$stmt = $pdo->prepare("SELECT dashboard_prefs FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$prefs_json = $stmt->fetchColumn();

$prefs = $prefs_json ? json_decode($prefs_json, true) : [
    "mostrar_tarjetas" => true,
    "mostrar_graficos" => true,
    "mostrar_proximos_turnos" => true,
    "mostrar_ultimos_pagos" => true
];

// --- ESTADÍSTICAS DEL MES ---
$stats = [];

// Turnos del mes
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments
    WHERE user_id = ?
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['turnos_mes'] = $stmt->fetchColumn() ?: 0;

// Pacientes nuevos
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clients
    WHERE user_id = ?
      AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['pacientes_nuevos'] = $stmt->fetchColumn() ?: 0;

// Evoluciones del mes
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clinical_records
    WHERE user_id = ?
      AND DATE_FORMAT(fecha, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['evoluciones_mes'] = $stmt->fetchColumn() ?: 0;

// Turnos pagados
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments
    WHERE user_id = ?
      AND payment_status = 'pagado'
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
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$stats['ingresos_mes'] = $stmt->fetchColumn() ?: 0;

// --- GRÁFICO: TURNOS POR ESTADO ---
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) AS total
    FROM appointments
    WHERE user_id = ?
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
    GROUP BY status
");
$stmt->execute([$user_id]);
$turnos_estado_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

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
    SELECT a.*, COALESCE(c.name, a.name) AS paciente
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
    SELECT a.*, COALESCE(c.name, a.name) AS paciente
    FROM appointments a
    LEFT JOIN clients c ON c.id = a.client_id
    WHERE a.user_id = ?
      AND a.payment_status = 'pagado'
    ORDER BY a.date DESC, a.time DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>

        <a href="dashboard-preferencias.php"
           class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm hover:bg-slate-300">
            ⚙️ Personalizar Dashboard
        </a>
    </div>

    <!-- TARJETAS -->
    <?php if ($prefs['mostrar_tarjetas']): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

        <div class="bg-white p-6 rounded-xl shadow border">
            <p class="text-sm text-slate-500">Turnos del mes</p>
            <p class="text-3xl font-bold"><?= $stats['turnos_mes'] ?></p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <p class="text-sm text-slate-500">Pacientes nuevos</p>
            <p class="text-3xl font-bold"><?= $stats['pacientes_nuevos'] ?></p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <p class="text-sm text-slate-500">Ingresos del mes</p>
            <p class="text-3xl font-bold text-green-600">$<?= number_format($stats['ingresos_mes'], 2, ',', '.') ?></p>
        </div>

    </div>
    <?php endif; ?>

    <!-- GRÁFICOS -->
    <?php if ($prefs['mostrar_graficos']): ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Turnos por estado</h3>
            <canvas id="chartEstados"></canvas>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Métodos de pago</h3>
            <canvas id="chartPagos"></canvas>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Ingresos del mes</h3>
            <canvas id="chartIngresos"></canvas>
        </div>

    </div>
    <?php endif; ?>

    <!-- PRÓXIMOS TURNOS -->
    <?php if ($prefs['mostrar_proximos_turnos']): ?>
    <div class="bg-white p-6 rounded-xl shadow border mb-10">
        <h3 class="text-lg font-semibold mb-4">Próximos turnos</h3>

        <?php if (!$proximos): ?>
            <p class="text-slate-500">No hay turnos próximos.</p>
        <?php endif; ?>

        <?php foreach ($proximos as $t): ?>
            <div class="p-4 border rounded-lg mb-3 bg-slate-50">
                <p class="font-medium"><?= $t['date'] ?> — <?= substr($t['time'], 0, 5) ?> hs</p>
                <p class="text-sm text-slate-600"><?= h($t['paciente']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ÚLTIMOS PAGOS -->
    <?php if ($prefs['mostrar_ultimos_pagos']): ?>
    <div class="bg-white p-6 rounded-xl shadow border mb-10">
        <h3 class="text-lg font-semibold mb-4">Últimos pagos</h3>

        <?php if (!$pagos): ?>
            <p class="text-slate-500">No hay pagos registrados.</p>
        <?php endif; ?>

        <?php foreach ($pagos as $p): ?>
            <div class="p-4 border rounded-lg mb-3 bg-slate-50">
                <p class="font-medium"><?= $p['date'] ?> — <?= substr($p['time'], 0, 5) ?> hs</p>
                <p class="text-sm text-slate-600"><?= h($p['paciente']) ?></p>
                <p class="text-sm text-green-600 font-semibold">$<?= number_format($p['amount'], 2, ',', '.') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// CONFIGURACIÓN GLOBAL
Chart.defaults.font.size = 13;
Chart.defaults.color = '#334155';

// --- GRÁFICO ESTADOS ---
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

// --- GRÁFICO MÉTODOS DE PAGO ---
new Chart(document.getElementById('chartPagos'), {
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

// --- GRÁFICO INGRESOS ---
new Chart(document.getElementById('chartIngresos'), {
    type: 'bar',
    data: {
        labels: ['Ingresos'],
        datasets: [{
            label: 'Monto total',
            data: [<?= $stats['ingresos_mes'] ?>],
            backgroundColor: '#10b981',
            borderColor: '#059669',
            borderWidth: 2
        }]
    },
    options: {
        plugins: {
            legend: { display: false }
        }
    }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>