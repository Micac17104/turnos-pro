<?php
// /pro/recordatorios-manana.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$mañana = date("Y-m-d", strtotime("+1 day"));

$stmt = $pdo->prepare("
    SELECT t.time, c.name, c.phone
    FROM appointments t
    JOIN clients c ON c.id = t.client_id
    WHERE t.user_id = ? AND t.date = ?
    ORDER BY t.time ASC
");
$stmt->execute([$user_id, $mañana]);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Recordatorios de mañana</h1>

    <?php if (empty($turnos)): ?>
        <p class="text-slate-500">No hay turnos para mañana.</p>
    <?php else: ?>

        <p class="text-slate-600 mb-4">Hacé clic en cada enlace para enviar el recordatorio por WhatsApp.</p>

        <div class="space-y-3">
            <?php foreach ($turnos as $t): ?>
                <?php
                $telefono = preg_replace('/\D/', '', $t['phone']);
                $mensaje = urlencode("Hola {$t['name']}! Te recordamos tu turno mañana a las {$t['time']}.");
                ?>
                <a href="https://wa.me/549<?= $telefono ?>?text=<?= $mensaje ?>"
                   target="_blank"
                   class="block text-blue-600 underline">
                   Enviar a <?= h($t['name']) ?> (<?= h($t['time']) ?>)
                </a>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>