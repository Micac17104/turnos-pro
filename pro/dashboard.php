<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/auth-profesional.php';




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

/* ============================
   ALERTA DE SUSCRIPCIÓN
============================ */
$stmt = $pdo->prepare("SELECT subscription_end, is_active FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$sub = $stmt->fetch(PDO::FETCH_ASSOC);

$alerta = null;

if ($sub) {
    if (!empty($sub['subscription_end'])) {
        $hoy = new DateTime();
        $fin = new DateTime($sub['subscription_end']);
        $diff = (int)$hoy->diff($fin)->format('%r%a');

        if ($diff <= 5 && $diff >= 0) {
            $alerta = "Tu suscripción vence en $diff día" . ($diff == 1 ? '' : 's') . ".";
        } elseif ($diff < 0) {
            $alerta = "Tu suscripción está vencida. Debés renovarla para seguir usando el sistema.";
        }
    }
}

/* ============================
   FUNCIONES DE FECHAS
============================ */
function ultimos_6_meses() {
    $meses = [];
    for ($i = 5; $i >= 0; $i--) {
        $meses[] = date('Y-m', strtotime("-$i months"));
    }
    return $meses;
}

function formatear_mes($ym) {
    $meses = [
        "01" => "Ene", "02" => "Feb", "03" => "Mar", "04" => "Abr",
        "05" => "May", "06" => "Jun", "07" => "Jul", "08" => "Ago",
        "09" => "Sep", "10" => "Oct", "11" => "Nov", "12" => "Dic"
    ];

    $anio = substr($ym, 0, 4);
    $mes  = substr($ym, 5, 2);

    return $meses[$mes] . " " . $anio;
}

$meses = ultimos_6_meses();

/* ============================
   TURNOS POR MES
============================ */
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(date, '%Y-%m') AS mes, COUNT(*) AS total
    FROM appointments
    WHERE user_id = ?
      AND date IS NOT NULL
    GROUP BY mes
");
$stmt->execute([$user_id]);
$turnos_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$turnos_mes = [];
foreach ($meses as $m) {
    $turnos_mes[$m] = isset($turnos_raw[$m]) ? (int)$turnos_raw[$m] : 0;
}

/* ============================
   INGRESOS POR MES
============================ */
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

$ingresos_mes = [];
foreach ($meses as $m) {
    $ingresos_mes[$m] = isset($ingresos_raw[$m]) ? (float)$ingresos_raw[$m] : 0;
}

$labels = array_map('formatear_mes', $meses);

/* ============================
   TURNOS POR MÉTODO DE PAGO
============================ */
$stmt = $pdo->prepare("
    SELECT payment_method, COUNT(*) AS total
    FROM appointments
    WHERE user_id = ?
      AND payment_status = 'pagado'
      AND payment_method IS NOT NULL
    GROUP BY payment_method
");
$stmt->execute([$user_id]);
$turnos_metodo = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

/* ============================
   INGRESOS POR MÉTODO DE PAGO
============================ */
$stmt = $pdo->prepare("
    SELECT payment_method, SUM(amount) AS total
    FROM appointments
    WHERE user_id = ?
      AND payment_status = 'pagado'
      AND amount IS NOT NULL
      AND payment_method IS NOT NULL
    GROUP BY payment_method
");
$stmt->execute([$user_id]);
$ingresos_metodo = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

/* ============================
   PRÓXIMOS TURNOS
============================ */
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

/* ============================
   ÚLTIMOS PAGOS
============================ */
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

    <?php if ($alerta): ?>
        <div class="mb-4 px-4 py-3 rounded-lg 
            <?= strpos($alerta, 'vencida') !== false ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800' ?>">
            <?= $alerta ?>
            &nbsp;|&nbsp;
            <a href="/pro/planes.php" class="underline font-semibold">Pagar suscripción</a>
            &nbsp;|&nbsp;
            <a href="/cancelar-suscripcion.php" class="underline">Cancelar</a>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>

        <a href="dashboard-preferencias.php"
           class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
            Preferencias
        </a>
    </div>

    <?php
require __DIR__ . '/../pro/includes/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT subscription_end, mp_subscription_status FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$today = strtotime(date('Y-m-d'));
$end   = strtotime($user['subscription_end']);
$dias_restantes = ($end - $today) / 86400;

// Mostrar aviso solo si está en prueba (primer mes)
if ($user['mp_subscription_status'] === 'active') {

    if ($dias_restantes <= 3 && $dias_restantes > 0) {

        $mensaje = "";

        if ($dias_restantes == 3) {
            $mensaje = "Tu período de prueba termina en 3 días. Si no agregás un método de pago, tu cuenta será suspendida.";
        }

        if ($dias_restantes == 2) {
            $mensaje = "Tu período de prueba termina en 2 días. Agregá un método de pago para evitar la suspensión.";
        }

        if ($dias_restantes == 1) {
            $mensaje = "Tu período de prueba termina mañana. Si no agregás un método de pago, tu cuenta será suspendida.";
        }

        if ($mensaje !== "") {
            echo "
            <div class='bg-yellow-100 text-yellow-800 p-4 rounded-lg mb-4 border border-yellow-300'>
                <strong>Atención:</strong> $mensaje
            </div>";
        }
    }
}
?>

    <!-- TARJETAS -->
    <?php if ($prefs['mostrar_tarjetas']): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">

        <div class="bg-white p-5 rounded-xl shadow border">
            <div class="text-sm text-slate-500">Turnos del mes</div>
            <div class="text-2xl font-bold"><?= $turnos_mes[$meses[5]] ?></div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border">
            <div class="text-sm text-slate-500">Pacientes nuevos</div>
            <div class="text-2xl font-bold">
                <?php
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM clients
                    WHERE user_id = ?
                    AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
                ");
                $stmt->execute([$user_id]);
                echo $stmt->fetchColumn();
                ?>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border">
            <div class="text-sm text-slate-500">Evoluciones</div>
            <div class="text-2xl font-bold">
                <?php
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM clinical_records
                    WHERE user_id = ?
                    AND DATE_FORMAT(fecha, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
                ");
                $stmt->execute([$user_id]);
                echo $stmt->fetchColumn();
                ?>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border">
            <div class="text-sm text-slate-500">Pagos</div>
            <div class="text-2xl font-bold">
                <?php
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM appointments
                    WHERE user_id = ?
                    AND payment_status = 'pagado'
                    AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
                ");
                $stmt->execute([$user_id]);
                echo $stmt->fetchColumn();
                ?>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border">
            <div class="text-sm text-slate-500">Ingresos</div>
            <div class="text-2xl font-bold">$<?= number_format($ingresos_mes[$meses[5]], 2) ?></div>
        </div>

    </div>
    <?php endif; ?>

    <!-- GRÁFICOS -->
    <?php if ($prefs['mostrar_graficos']): ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Turnos por mes</h3>
            <canvas id="chartTurnosMes"></canvas>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Ingresos por mes</h3>
            <canvas id="chartIngresosMes"></canvas>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Turnos por método de pago</h3>
            <canvas id="chartTurnosMetodo"></canvas>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="font-semibold mb-3 text-sm">Ingresos por método de pago</h3>
            <canvas id="chartIngresosMetodo"></canvas>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    Chart.defaults.font.size = 13;
    Chart.defaults.color = '#334155';

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
        }
    });

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
        }
    });

    new Chart(document.getElementById('chartTurnosMetodo'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($turnos_metodo)) ?>,
            datasets: [{
                label: 'Turnos',
                data: <?= json_encode(array_values($turnos_metodo)) ?>,
                backgroundColor: '#3b82f6',
                borderColor: '#1d4ed8',
                borderWidth: 2
            }]
        }
    });

    new Chart(document.getElementById('chartIngresosMetodo'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($ingresos_metodo)) ?>,
            datasets: [{
                label: 'Ingresos',
                data: <?= json_encode(array_values($ingresos_metodo)) ?>,
                backgroundColor: '#10b981',
                borderColor: '#059669',
                borderWidth: 2
            }]
        }
    });
    </script>

    <?php endif; ?>

    <!-- PRÓXIMOS TURNOS -->
    <?php if ($prefs['mostrar_proximos_turnos']): ?>
    <div class="bg-white p-6 rounded-xl shadow border mb-8">
        <h2 class="text-xl font-semibold mb-4">Próximos turnos</h2>

        <?php if (empty($proximos)): ?>
            <p class="text-slate-500">No hay turnos próximos.</p>
        <?php else: ?>
            <ul class="space-y-2">
                <?php foreach ($proximos as $t): ?>
                    <li class="p-3 bg-slate-100 rounded-lg">
                        <strong><?= $t['date'] ?></strong> - <?= $t['time'] ?><br>
                        <span class="text-slate-600"><?= $t['paciente'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
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