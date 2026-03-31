<?php
// /pro/turnos-manana.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = "Turnos de mañana";
$current = "agenda";

$mañana = date("Y-m-d", strtotime("+1 day"));

// Obtener turnos del profesional
$stmt = $pdo->prepare("
    SELECT t.id, t.date, t.time, c.name, c.phone, c.email
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

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">Turnos de mañana</h1>

        <?php if (isset($_GET['sent']) && $_GET['sent'] == 1): ?>
    <div class="mb-4 p-3 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg">
        ✔ Recordatorio enviado correctamente
    </div>
<?php endif; ?>

<?php if (isset($_GET['sent']) && $_GET['sent'] == 0): ?>
    <div class="mb-4 p-3 bg-red-100 text-red-800 border border-red-300 rounded-lg">
        ❌ Error al enviar el recordatorio
    </div>
<?php endif; ?>

        <button
            onclick="window.location.href='agenda.php'"
            class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm hover:bg-slate-300">
            ← Volver
        </button>
    </div>

    <?php if (empty($turnos)): ?>
        <p class="text-slate-500">No hay turnos para mañana.</p>
    <?php else: ?>

        <button
            onclick="window.location.href='recordatorios-manana.php'"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm shadow hover:bg-emerald-700 mb-6">
            Enviar recordatorios de mañana
        </button>

        <div class="space-y-4 mt-6">
            <?php foreach ($turnos as $t): ?>
                <?php
                $telefono = preg_replace('/\D/', '', $t['phone']);
                $mensaje = urlencode("Hola {$t['name']}! Te recordamos tu turno mañana a las {$t['time']}.");
                ?>
                <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <p><strong>Paciente:</strong> <?= h($t['name']) ?></p>
                    <p><strong>Hora:</strong> <?= h($t['time']) ?></p>
                    <p><strong>Teléfono:</strong> <?= h($t['phone']) ?></p>
                    <p><strong>Email:</strong> <?= h($t['email']) ?></p>

                    <div class="flex gap-3 mt-3">
                        <a href="https://wa.me/549<?= $telefono ?>?text=<?= $mensaje ?>"
                           target="_blank"
                           class="px-3 py-1 bg-green-600 text-white rounded text-sm">
                           WhatsApp
                        </a>

                        <button
                            onclick="window.location.href='enviar-recordatorio.php?turno_id=<?= $t['id'] ?>'"
                            class="px-3 py-1 bg-blue-600 text-white rounded text-sm">
                            Email
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>