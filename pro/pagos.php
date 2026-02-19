<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Pagos';
$current = 'pagos';

// Obtener pagos
$stmt = $pdo->prepare("
    SELECT a.*, COALESCE(c.name, a.name) AS paciente
    FROM appointments a
    LEFT JOIN clients c ON c.id = a.client_id
    WHERE a.user_id = ?
      AND a.payment_status IS NOT NULL
    ORDER BY a.date DESC, a.time DESC
");
$stmt->execute([$user_id]);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">



    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Pagos</h1>

    <div class="bg-white p-6 rounded-xl shadow border">

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500 border-b">
                    <th class="py-2">Fecha</th>
                    <th>Paciente</th>
                    <th>Estado</th>
                    <th>Método</th>
                    <th>Monto</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($pagos as $p): ?>
                    <tr class="border-b">
                        <td class="py-3">
                            <?= $p['date'] ?> <?= substr($p['time'], 0, 5) ?>
                        </td>

                        <td><?= h($p['paciente']) ?></td>

                        <td>
                            <?php if ($p['payment_status'] === 'pagado'): ?>
                                <span class="text-green-600 font-medium">Pagado</span>
                            <?php else: ?>
                                <span class="text-red-600 font-medium">Pendiente</span>
                            <?php endif; ?>
                        </td>

                        <td><?= h($p['payment_method'] ?: '-') ?></td>

                        <td>
                            $<?= number_format($p['amount'] ?: 0, 2, ',', '.') ?>
                        </td>

                        <td>
                            <a href="/turnos-pro/pro/pago-editar.php?id=<?= $p['id'] ?>"
                               class="text-blue-600 hover:underline">
                                Editar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>

    </div>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>