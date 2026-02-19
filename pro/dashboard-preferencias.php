<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Preferencias del Dashboard';
$current = 'dashboard';

$stmt = $pdo->prepare("SELECT dashboard_prefs FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$prefs_json = $stmt->fetchColumn();

$prefs = $prefs_json ? json_decode($prefs_json, true) : [
    "mostrar_tarjetas" => true,
    "mostrar_graficos" => true,
    "mostrar_proximos_turnos" => true,
    "mostrar_ultimos_pagos" => true
];

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <h1 class="text-2xl font-semibold mb-6">Preferencias del Dashboard</h1>

    <form method="POST" action="dashboard-preferencias-guardar.php"
          class="bg-white p-6 rounded-xl shadow border max-w-xl">

        <label class="flex items-center gap-3 mb-4">
            <input type="checkbox" name="mostrar_tarjetas"
                   <?= $prefs['mostrar_tarjetas'] ? 'checked' : '' ?>>
            <span>Mostrar tarjetas principales</span>
        </label>

        <label class="flex items-center gap-3 mb-4">
            <input type="checkbox" name="mostrar_graficos"
                   <?= $prefs['mostrar_graficos'] ? 'checked' : '' ?>>
            <span>Mostrar gráficos</span>
        </label>

        <label class="flex items-center gap-3 mb-4">
            <input type="checkbox" name="mostrar_proximos_turnos"
                   <?= $prefs['mostrar_proximos_turnos'] ? 'checked' : '' ?>>
            <span>Mostrar próximos turnos</span>
        </label>

        <label class="flex items-center gap-3 mb-6">
            <input type="checkbox" name="mostrar_ultimos_pagos"
                   <?= $prefs['mostrar_ultimos_pagos'] ? 'checked' : '' ?>>
            <span>Mostrar últimos pagos</span>
        </label>

        <button class="px-4 py-2 bg-slate-900 text-white rounded-lg">
            Guardar preferencias
        </button>

    </form>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>