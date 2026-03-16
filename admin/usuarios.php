<?php
require __DIR__ . '/auth-admin.php';
require __DIR__ . '/../pro/includes/db.php';

$stmt = $pdo->query("
    SELECT id, name, email, account_type, is_active
    FROM users
    ORDER BY id DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="ml-72 p-8">

    <h1 class="text-3xl font-bold mb-6">Usuarios</h1>

    <table class="w-full bg-white shadow rounded-lg overflow-hidden">
        <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-3 text-left">Nombre</th>
                <th class="p-3 text-left">Email</th>
                <th class="p-3 text-left">Tipo</th>
                <th class="p-3 text-left">Estado</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($users as $u): ?>
                <tr class="border-b">
                    <td class="p-3"><?= $u['name'] ?></td>
                    <td class="p-3"><?= $u['email'] ?></td>
                    <td class="p-3"><?= ucfirst($u['account_type']) ?></td>
                    <td class="p-3">
                        <?= $u['is_active'] ? '<span class="text-emerald-600">Activo</span>' : '<span class="text-red-600">Suspendido</span>' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<?php include __DIR__ . '/../pro/includes/footer.php'; ?>