<?php
require __DIR__ . '/auth-admin.php';
require __DIR__ . '/../pro/includes/db.php';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';

// Filtros
$q        = $_GET['q']        ?? '';
$f_estado = $_GET['estado']   ?? '';
$f_tipo   = $_GET['tipo']     ?? '';

$stmt = $pdo->query("
    SELECT id, email, account_type, subscription_end, is_active, mp_subscription_status, last_payment
    FROM users
    ORDER BY subscription_end ASC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="ml-72 p-8">

    <h1 class="text-3xl font-bold mb-6">Pagos y Suscripciones</h1>

    <!-- Filtros -->
    <form method="get" class="mb-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-sm font-semibold mb-1">Buscar</label>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                   class="border rounded px-2 py-1 w-64"
                   placeholder="Email">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Estado</label>
            <select name="estado" class="border rounded px-2 py-1">
                <option value="">Todos</option>
                <option value="activa"     <?= $f_estado === 'activa' ? 'selected' : '' ?>>Activa</option>
                <option value="vencida"    <?= $f_estado === 'vencida' ? 'selected' : '' ?>>Vencida</option>
                <option value="cancelada"  <?= $f_estado === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                <option value="suspendida" <?= $f_estado === 'suspendida' ? 'selected' : '' ?>>Suspendida</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Tipo</label>
            <select name="tipo" class="border rounded px-2 py-1">
                <option value="">Todos</option>
                <option value="professional" <?= $f_tipo === 'professional' ? 'selected' : '' ?>>Professional</option>
                <option value="center"       <?= $f_tipo === 'center' ? 'selected' : '' ?>>Center</option>
                <option value="admin"        <?= $f_tipo === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <div>
            <button class="bg-slate-900 text-white px-4 py-2 rounded">Filtrar</button>
        </div>
    </form>

    <table class="w-full bg-white shadow rounded-xl border">
        <thead>
            <tr class="bg-slate-100 text-left">
                <th class="p-3">Email</th>
                <th class="p-3">Tipo</th>
                <th class="p-3">Vence</th>
                <th class="p-3">Estado</th>
                <th class="p-3">Último pago</th>
                <th class="p-3">Acciones</th>
            </tr>
        </thead>
        <tbody>

        <?php
        $today = strtotime(date('Y-m-d'));

        foreach ($users as $u):

            $end = $u['subscription_end'] ? strtotime($u['subscription_end']) : null;

            if ($u['is_active'] == 1) {

    // SI ESTÁ ACTIVO → SIEMPRE ACTIVO (aunque MP diga inactive)
    $status_key = 'activa';
    $estado = "<span class='text-green-600 font-semibold'>Activa</span>";

} elseif ($end !== null && $end < $today) {

    $status_key = 'vencida';
    $estado = "<span class='text-orange-600 font-semibold'>Vencida</span>";

} elseif ($u['mp_subscription_status'] === 'inactive') {

    $status_key = 'cancelada';
    $estado = "<span class='text-red-600 font-semibold'>Cancelada por el usuario</span>";

} else {

    $status_key = 'suspendida';
    $estado = "<span class='text-red-600 font-semibold'>Suspendida</span>";
}

            // Filtro por texto
            if ($q) {
                $q_l = mb_strtolower($q);
                $email_l = mb_strtolower($u['email']);
                if (strpos($email_l, $q_l) === false) {
                    continue;
                }
            }

            // Filtro por estado
            if ($f_estado && $f_estado !== $status_key) {
                continue;
            }

            // Filtro por tipo
            if ($f_tipo && $f_tipo !== $u['account_type']) {
                continue;
            }
        ?>
            <tr class="border-t">
                <td class="p-3"><?= $u['email'] ?></td>
                <td class="p-3"><?= $u['account_type'] ?></td>
                <td class="p-3"><?= $u['subscription_end'] ?></td>
                <td class="p-3"><?= $estado ?></td>
                <td class="p-3"><?= $u['last_payment'] ?: '-' ?></td>

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
