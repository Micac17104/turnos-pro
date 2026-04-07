<?php
require __DIR__ . '/auth-admin.php';
require __DIR__ . '/../pro/includes/db.php';

// Obtener usuarios con estado de suscripción
$stmt = $pdo->query("
    SELECT 
        id,
        name,
        email,
        account_type,
        subscription_start,
        subscription_end,
        is_active,
        mp_subscription_status
    FROM users
    ORDER BY subscription_end ASC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="p-8 ml-64"> <!-- FIX: evita que el sidebar tape contenido -->

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

                <?php
                // Calcular estado real
                $today = strtotime(date('Y-m-d'));
                $end   = strtotime($u['subscription_end']);

                if ($u['mp_subscription_status'] === 'inactive') {
                    $estado = "<span class='text-red-600 font-semibold'>Cancelada por el usuario</span>";
                } elseif ($end < $today) {
                    $estado = "<span class='text-orange-600 font-semibold'>Vencida</span>";
                } elseif ($u['is_active'] == 0) {
                    $estado = "<span class='text-red-600 font-semibold'>Suspendida</span>";
                } elseif (empty($u['subscription_start'])) {
                    $estado = "<span class='text-gray-600 font-semibold'>Sin suscripción</span>";
                } else {
                    $estado = "<span class='text-green-600 font-semibold'>Activa</span>";
                }
                ?>

                <tr class="border-b">
                    <td class="p-3"><?= htmlspecialchars($u['name']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="p-3"><?= ucfirst($u['account_type']) ?></td>
                    <td class="p-3"><?= $u['subscription_start'] ?></td>
                    <td class="p-3"><?= $u['subscription_end'] ?></td>
                    <td class="p-3"><?= $estado ?></td>

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

</div>

<?php include __DIR__ . '/../pro/includes/footer.php'; ?>
