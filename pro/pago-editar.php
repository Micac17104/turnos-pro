<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Editar pago';
$current = 'pagos';

$id = require_param($_GET, 'id', 'Pago no encontrado.');

$stmt = $pdo->prepare("
    SELECT a.*, COALESCE(c.name, a.name) AS paciente
    FROM appointments a
    LEFT JOIN clients c ON c.id = a.client_id
    WHERE a.id = ? AND a.user_id = ?
");
$stmt->execute([$id, $user_id]);
$pago = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pago) {
    die("Pago no encontrado.");
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Editar pago</h1>

    <form method="POST" action="/turnos-pro/pro/pago-guardar.php"
          class="bg-white p-6 rounded-xl shadow border max-w-lg">

        <input type="hidden" name="id" value="<?= $id ?>">

        <p class="text-sm text-slate-600 mb-4">
            Paciente: <strong><?= h($pago['paciente']) ?></strong><br>
            Fecha: <?= $pago['date'] ?> <?= substr($pago['time'], 0, 5) ?>
        </p>

        <label class="text-sm font-medium text-slate-700">Estado del pago</label>
        <select name="payment_status"
                class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-4">
            <option value="pendiente" <?= $pago['payment_status']=='pendiente'?'selected':'' ?>>Pendiente</option>
            <option value="pagado" <?= $pago['payment_status']=='pagado'?'selected':'' ?>>Pagado</option>
        </select>

        <label class="text-sm font-medium text-slate-700">Método de pago</label>
        <select name="payment_method"
                class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-4">
            <option value="">Seleccionar</option>
            <option value="efectivo" <?= $pago['payment_method']=='efectivo'?'selected':'' ?>>Efectivo</option>
            <option value="transferencia" <?= $pago['payment_method']=='transferencia'?'selected':'' ?>>Transferencia</option>
            <option value="mercado_pago" <?= $pago['payment_method']=='mercado_pago'?'selected':'' ?>>Mercado Pago</option>
        </select>

        <label class="text-sm font-medium text-slate-700">Monto</label>
        <input type="number" step="0.01" name="amount"
               value="<?= $pago['amount'] ?>"
               class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-6">

        <div class="flex justify-end gap-3">
            <a href="/turnos-pro/pro/pagos.php"
               class="px-4 py-2 bg-slate-200 rounded-lg text-slate-700 text-sm">
                Cancelar
            </a>

            <button class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm">
                Guardar cambios
            </button>
        </div>

    </form>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>