<?php
require __DIR__ . '/auth-admin.php';
require __DIR__ . '/../pro/includes/db.php';

// Consultas
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_prof = $pdo->query("SELECT COUNT(*) FROM users WHERE account_type='professional'")->fetchColumn();
$total_centros = $pdo->query("SELECT COUNT(*) FROM users WHERE account_type='center'")->fetchColumn();
$total_activos = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();
?>

<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="ml-72 p-8">

    <h1 class="text-3xl font-bold mb-6">Dashboard Admin</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="text-sm text-slate-500">Usuarios totales</h3>
            <div class="text-3xl font-bold"><?= $total_users ?></div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="text-sm text-slate-500">Profesionales</h3>
            <div class="text-3xl font-bold"><?= $total_prof ?></div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="text-sm text-slate-500">Centros</h3>
            <div class="text-3xl font-bold"><?= $total_centros ?></div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <h3 class="text-sm text-slate-500">Activos</h3>
            <div class="text-3xl font-bold text-emerald-600"><?= $total_activos ?></div>
        </div>

    </div>

</div>

<?php include __DIR__ . '/../pro/includes/footer.php'; ?>