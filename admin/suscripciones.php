<?php
require __DIR__ . '/auth-admin.php';
require __DIR__ . '/../pro/includes/db.php';

// Filtros
$q        = $_GET['q']        ?? '';
$f_estado = $_GET['estado']   ?? '';
$f_tipo   = $_GET['tipo']     ?? '';

// Obtener usuarios
$stmt = $pdo->query("
    SELECT 
        id,
        name,
        email,
        account_type,
        subscription_start,
        subscription_end,
        is_active,
        mp_subscription_status,
        last_payment
    FROM users
    ORDER BY subscription_end ASC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="p-8 ml-64">

    <h1 class="text-3xl font-bold mb-6">Panel de Suscripciones</h1>

    <!-- Filtros -->
    <form method="get" class="mb-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-sm font-semibold mb-1">Buscar</label>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                   class="border rounded px-2 py-1 w-64"
                   placeholder="Nombre o email">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Estado</label>
            <select name="estado" class="border rounded px-2 py-1">
                <option value="">Todos</option>
                <option value="activa"     <?= $f_estado === 'activa' ? 'selected' : '' ?>>Activa</option>
                <option value="vencida"    <?= $f_estado === 'vencida' ? 'selected' : '' ?>>Vencida</option>
                <option value="cancelada"  <?= $f_estado === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                <option value="suspendida" <?= $f_estado === 'suspendida' ? 'selected' : '' ?>>Suspendida</option>
                <option value="sin"        <?= $f_estado === 'sin' ? 'selected' : '' ?>>Sin suscripción</option>
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

    <table class="w-full bg-white shadow rounded-lg overflow-hidden">
        <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-3 text-left">Nombre</th>
                <th class="p-3 text-left">Email</th>
                <th class="p-3 text-left">Tipo</th>
                <th class="p-3 text-left">Inicio</th>
                <th class="p-3 text-left">Vence</th>
                <th class="p-3 text-left">Estado</th>
                <th class="p-3 text-left">Último pago</th>
                <th class="p-3 text-left">Acción</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $today = strtotime(date('Y-m-d'));

            foreach ($users as $u):

                $end = $u['subscription_end'] ? strtotime($u['subscription_end']) : null;

                // Estado lógico
                if ($u['mp_subscription_status'] === 'inactive') {
                    $status_key = 'cancelada';
                    $estado = "<span class='text-red-600 font-semibold'>Cancelada por el usuario</span>";
                } elseif ($end !== null && $end < $today) {
                    $status_key = 'vencida';
                    $estado = "<span class='text-orange-600 font-semibold'>Vencida</span>";
                } elseif ($u['is_active'] == 0) {
                    $status_key = 'suspendida';
                    $estado = "<span class='text-red-600 font-semibold'>Suspendida</span>";
                } elseif (empty($u['subscription_start'])) {
                    $status_key = 'sin';
                    $estado = "<span class='text-gray-600 font-semibold'>Sin suscripción</span>";
                } else {
                    $status_key = 'activa';
                    $estado = "<span class='text-green-600 font-semibold'>Activa</span>";
                }

                // Filtro por texto
                if ($q) {
                    $q_l = mb_strtolower($q);
                    $name_l  = mb_strtolower($u['name']);
                    $email_l = mb_strtolower($u['email']);
                    if (strpos($name_l, $q_l) === false && strpos($email_l, $q_l) === false) {
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
                <tr class="border-b">
                    <td class="p-3"><?= htmlspecialchars($u['name']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="p-3"><?= ucfirst($u['account_type']) ?></td>
                    <td class="p-3"><?= $u['subscription_start'] ?></td>
                    <td class="p-3"><?= $u['subscription_end'] ?></td>
                    <td class="p-3"><?= $estado ?></td>
                    <td class="p-3"><?= $u['last_payment'] ?: '-' ?></td>

                    <td class="p-3">
                        <div class="flex flex-wrap gap-2">

                            <a href="marcar-pago.php?id=<?= $u['id'] ?>"
                               class="px-3 py-1 text-sm bg-emerald-600 text-white rounded hover:bg-emerald-700">
                               Marcar pago
                            </a>

                            <a href="admin_acciones.php?action=activar&id=<?= $u['id'] ?>"
                               class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">
                               Activar
                            </a>

                            <a href="admin_acciones.php?action=desactivar&id=<?= $u['id'] ?>"
                               class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                               Desactivar
                            </a>

                            <a href="admin_acciones.php?action=sumar_mes&id=<?= $u['id'] ?>"
                               class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                               +1 mes
                            </a>

                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mt-4">
        <a href="exportar-suscripciones.php"
           class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Exportar CSV
        </a>
    </div>

</div>

<?php include __DIR__ . '/../pro/includes/footer.php'; ?>
