<?php
// /pro/dashboard.php

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = "Dashboard";
$current = "dashboard";

// Cargar preferencias del usuario
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

// INCLUDES DEL PANEL
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<!-- CONTENIDO PRINCIPAL -->
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
                        <strong><?= $t['date'] ?></strong> - <?= $t['time'] ?>  
                        <br>
                        <span class="text-slate-600"><?= $t['paciente'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>