<?php
require __DIR__ . '/auth-admin.php';
require __DIR__ . '/../pro/includes/db.php';

$stmt = $pdo->query("
    SELECT l.*, u.email 
    FROM subscription_logs l
    LEFT JOIN users u ON u.id = l.user_id
    ORDER BY l.created_at DESC
    LIMIT 300
");

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="p-8 ml-64">
    <h1 class="text-3xl font-bold mb-6">Historial de Suscripciones</h1>

    <table class="w-full bg-white shadow rounded-lg overflow-hidden">
        <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-3">Fecha</th>
                <th class="p-3">Usuario</th>
                <th class="p-3">Acción</th>
                <th class="p-3">Detalles</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($logs as $l): ?>
            <tr class="border-b">
                <td class="p-3"><?= $l['created_at'] ?></td>
                <td class="p-3"><?= $l['email'] ?></td>
                <td class="p-3"><?= $l['action'] ?></td>
                <td class="p-3"><?= $l['details'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../pro/includes/footer.php'; ?>
