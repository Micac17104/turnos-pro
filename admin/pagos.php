<?php
require __DIR__ . '/auth-admin.php';
require __DIR__ . '/../pro/includes/db.php';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';

// Obtener todos los usuarios con suscripción
$stmt = $pdo->query("
    SELECT id, email, account_type, subscription_end, is_active
    FROM users
    ORDER BY subscription_end ASC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="ml-72 p-8">

    <h1 class="text-3xl font-bold mb-6">Pagos y Suscripciones</h1>

    <table class="w-full bg-white shadow rounded-xl border">
        <thead>
            <tr class="bg-slate-100 text-left">
                <th class="p-3">Email</th>
                <th class="p-3">Tipo</th>
                <th class="p-3">Vence</th>
                <th class="p-3">Estado</th>
                <th class="p-3">Acciones</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ($users as $u): ?>
            <tr class="border-t">
                <td class="p-3"><?= $u['email'] ?></td>
                <td class="p-3"><?= $u['account_type'] ?></td>
                <td class="p-3"><?= $u['subscription_end'] ?></td>
                <td class="p-3">
                    <?php if ($u['is_active']): ?>
                        <span class="text-emerald-600 font-semibold">Activo</span>
                    <?php else: ?>
                        <span class="text-red-600 font-semibold">Vencido</span>
                    <?php endif; ?>
                </td>
                <td class="p-3">
                    <a href="marcar-pago.php?id=<?= $u['id'] ?>" 
                       class="px-3 py-1 bg-blue-600 text-white rounded">
                       Marcar pago
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>

</div>

<?php include __DIR__ . '/../pro/includes/footer.php'; ?>