<?php
require __DIR__ . '/auth-admin.php';
require __DIR__ . '/../pro/includes/db.php';

$stmt = $pdo->query("
    SELECT id, name, email, account_type, subscription_start, subscription_end, is_active
    FROM users
    ORDER BY subscription_end ASC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Suscripciones - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 p-8">

    <h1 class="text-3xl font-bold mb-6">Panel de Suscripciones</h1>

    <table class="w-full bg-white shadow rounded-lg overflow-hidden">
        <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-3 text-left">Nombre</th>
                <th class="p-3 text-left">Email</th>
                <th class="p-3 text-left">Tipo</th>
                <th class="p-3 text-left">Inicio</th>
                <th class="p-3 text-left">Vence</th>
                <th class="p-3 text-left">Estado</th>
                <th class="p-3 text-left">Acción</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($users as $u): ?>
                <tr class="border-b">
                    <td class="p-3"><?= $u['name'] ?></td>
                    <td class="p-3"><?= $u['email'] ?></td>
                    <td class="p-3"><?= ucfirst($u['account_type']) ?></td>
                    <td class="p-3"><?= $u['subscription_start'] ?></td>
                    <td class="p-3"><?= $u['subscription_end'] ?></td>
                    <td class="p-3">
                        <?php if ($u['is_active']): ?>
                            <span class="text-green-600 font-semibold">Activo</span>
                        <?php else: ?>
                            <span class="text-red-600 font-semibold">Suspendido</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3">
                        <a href="marcar-pago.php?id=<?= $u['id'] ?>"
                           class="bg-emerald-600 text-white px-3 py-1 rounded hover:bg-emerald-700">
                           Marcar pago
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>