<?php
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../pro/includes/helpers.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    die("Perfil no encontrado.");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE slug = ?");
$stmt->execute([$slug]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    die("Profesional no encontrado.");
}

// Horarios desde professional_schedule
$dias = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    0 => 'Domingo'
];

$stmt = $pdo->prepare("
    SELECT day_of_week, start_time, end_time, interval_minutes
    FROM professional_schedule
    WHERE user_id = ?
    ORDER BY FIELD(day_of_week, 1,2,3,4,5,6,0)
");
$stmt->execute([$pro['id']]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$horarios = [];
foreach ($rows as $r) {
    $horarios[$r['day_of_week']] = $r;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= h($pro['name']) ?> - Profesional</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">

<div class="max-w-3xl mx-auto py-10 px-4">

    <!-- HEADER -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex items-center gap-4">
        
        <?php if (!empty($pro['profile_image'])): ?>
            <img src="/turnos-pro/uploads/<?= h($pro['profile_image']) ?>"
                 class="w-20 h-20 rounded-full object-cover border">
        <?php else: ?>
            <div class="w-20 h-20 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 text-xl">
                <?= strtoupper(substr($pro['name'], 0, 1)) ?>
            </div>
        <?php endif; ?>

        <div>
            <h1 class="text-2xl font-semibold text-slate-900"><?= h($pro['name']) ?></h1>
            <p class="text-slate-600"><?= h($pro['profession']) ?></p>

            <?php if ($pro['city'] || $pro['province']): ?>
                <p class="text-sm text-slate-500 mt-1">
                    <?= h($pro['city']) ?><?= $pro['city'] && $pro['province'] ? ', ' : '' ?><?= h($pro['province']) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- BOTÓN WHATSAPP -->
    <?php if (!empty($pro['phone'])): ?>
        <div class="mt-6">
            <a href="https://wa.me/<?= h($pro['phone']) ?>"
               class="block w-full text-center px-4 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition">
                Pedir turno por WhatsApp
            </a>
        </div>
    <?php endif; ?>

    <!-- (OPCIONAL) BOTÓN RESERVA ONLINE -->
    <div class="mt-3">
        <a href="/turnos-pro/public/reservar-p.php?slug=<?= $pro['slug'] ?>"
           class="block w-full text-center px-4 py-3 bg-slate-900 text-white rounded-lg font-medium hover:bg-slate-800 transition">
            Reservar turno online
        </a>
    </div>

    <!-- DESCRIPCIÓN -->
    <?php if (!empty($pro['public_description'])): ?>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mt-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-2">Sobre mí</h2>
            <p class="text-slate-700 leading-relaxed">
                <?= nl2br(h($pro['public_description'])) ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- ESPECIALIDADES -->
    <?php if (!empty($pro['specialties'])): ?>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mt-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-2">Especialidades</h2>
            <p class="text-slate-700"><?= h($pro['specialties']) ?></p>
        </div>
    <?php endif; ?>

    <!-- HORARIOS DE ATENCIÓN -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mt-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-2">Horarios de atención</h2>

        <?php if (!$horarios): ?>
            <p class="text-slate-500 text-sm">Este profesional aún no cargó sus horarios de atención.</p>
        <?php else: ?>
            <div class="divide-y divide-slate-200">
                <?php foreach ($dias as $dow => $label): ?>
                    <?php if (!isset($horarios[$dow])) continue; ?>
                    <?php $h = $horarios[$dow]; ?>
                    <div class="py-2 flex items-center justify-between">
                        <span class="text-sm text-slate-700"><?= $label ?></span>
                        <div class="text-sm text-slate-600">
                            <?= substr($h['start_time'], 0, 5) ?> a <?= substr($h['end_time'], 0, 5) ?>
                            <span class="text-slate-400">· cada <?= (int)$h['interval_minutes'] ?> min</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- UBICACIÓN -->
    <?php if (!empty($pro['address'])): ?>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mt-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-2">Ubicación</h2>
            <p class="text-slate-700"><?= h($pro['address']) ?></p>

            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($pro['address']) ?>"
               target="_blank"
               class="text-blue-600 text-sm mt-2 inline-block hover:underline">
                Ver en Google Maps
            </a>
        </div>
    <?php endif; ?>

</div>

</body>
</html>