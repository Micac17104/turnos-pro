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
    SELECT t.id, t.fecha, t.hora, c.name, c.phone, c.email
    FROM appointments t
    JOIN clients c ON c.id = t.client_id
    WHERE t.user_id = ? AND t.fecha = ?
    ORDER BY t.hora ASC
");
$stmt->execute([$user_id, $mañana]);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">Turnos de mañana</h1>

        <a href="agenda.php"
           class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 text-sm hover:bg-slate-300">
            ← Volver
        </a>
    </div>

    <?php if (empty($turnos)): ?>
        <p class="text-slate-500">No hay turnos para mañana.</p>
    <?php else: ?>

        <a href="recordatorios-manana.php"
           class="px-4 py-2 bg-emerald-600 text-white rounded-lg mb-6 inline-block">
           Enviar recordatorios de mañana
        </a>

        <div class="space-y-4 mt-6">
            <?php foreach ($turnos as $t): ?>
                <?php
                $telefono = preg_replace('/\D/', '', $t['phone']);
                $mensaje = urlencode("Hola {$t['name']}! Te recordamos tu turno mañana a las {$t['hora']}.");
                ?>
                <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <p><strong>Paciente:</strong> <?= h($t['name']) ?></p>
                    <p><strong>Hora:</strong> <?= h($t['hora']) ?></p>
                    <p><strong>Teléfono:</strong> <?= h($t['phone']) ?></p>
                    <p><strong>Email:</strong> <?= h($t['email']) ?></p>

                    <div class="flex gap-3 mt-3">
                        <a href="https://wa.me/549<?= $telefono ?>?text=<?= $mensaje ?>"
                           target="_blank"
                           class="px-3 py-1 bg-green-600 text-white rounded text-sm">
                           WhatsApp
                        </a>

                        <a href="enviar-recordatorio.php?turno_id=<?= $t['id'] ?>"
                           class="px-3 py-1 bg-blue-600 text-white rounded text-sm">
                           Email
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>