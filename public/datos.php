<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';

$pro_id = $_GET['pro'] ?? null;
$date   = $_GET['date'] ?? null;
$time   = $_GET['time'] ?? null;

if (!$pro_id || !$date || !$time) {
    die("Datos incompletos.");
}

// Profesional
$stmt = $pdo->prepare("SELECT id, name, profession FROM users WHERE id = ?");
$stmt->execute([$pro_id]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$pro) {
    die("Profesional no encontrado.");
}

// Si el paciente está logueado, traemos sus datos
$paciente = null;
if (!empty($_SESSION['paciente_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$_SESSION['paciente_id']]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Datos del paciente</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">

<div class="max-w-lg mx-auto py-12 px-6">
    <h1 class="text-2xl font-bold text-slate-900 mb-2">Tus datos</h1>
    <p class="text-slate-600 mb-4">
        Turno con <?= h($pro['name']) ?> — <?= date('d/m/Y', strtotime($date)) ?> <?= $time ?> hs
    </p>

    <!-- RUTA CORRECTA PARA RAILWAY -->
    <form action="confirmar.php" method="post" class="space-y-4">
        <input type="hidden" name="pro"  value="<?= $pro_id ?>">
        <input type="hidden" name="date" value="<?= h($date) ?>">
        <input type="hidden" name="time" value="<?= h($time) ?>">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo</label>
            <input type="text" name="name" required
                   value="<?= h($paciente['name'] ?? '') ?>"
                   class="w-full px-3 py-2 rounded-lg border border-slate-300">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input type="email" name="email" required
                   value="<?= h($paciente['email'] ?? '') ?>"
                   class="w-full px-3 py-2 rounded-lg border border-slate-300">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
            <input type="text" name="phone"
                   value="<?= h($paciente['phone'] ?? '') ?>"
                   class="w-full px-3 py-2 rounded-lg border border-slate-300">
        </div>

        <button class="w-full mt-4 px-4 py-3 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
            Confirmar turno
        </button>
    </form>
</div>

</body>
</html>