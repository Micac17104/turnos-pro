<?php
session_start();
require __DIR__ . '/../../config.php';

// Validar login del profesional
if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Detectar tenant
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

$record_id = $_GET['record_id'] ?? null;

if (!$record_id) {
    die("Evolución no encontrada.");
}

// Obtener evolución + nombre del paciente
$stmt = $pdo->prepare("
    SELECT cr.*, c.name AS paciente_nombre
    FROM clinical_records cr
    JOIN clients c ON cr.patient_id = c.id
    WHERE cr.id = ? AND cr.user_id = ?
");
$stmt->execute([$record_id, $user_id]);
$evolucion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$evolucion) {
    die("Evolución no pertenece a este profesional.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Adjuntar archivo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-xl mx-auto mt-12 bg-white p-8 rounded-xl shadow">

    <h2 class="text-2xl font-bold text-gray-800 mb-2">Adjuntar archivo</h2>

    <p class="text-gray-700"><strong>Paciente:</strong> <?= htmlspecialchars($evolucion['paciente_nombre']) ?></p>
    <p class="text-gray-700 mb-6"><strong>Fecha de evolución:</strong> <?= date("d/m/Y H:i", strtotime($evolucion['fecha'])) ?></p>

    <form method="post" action="/turnos-pro/profiles/<?= $user_id ?>/guardar-archivo.php" enctype="multipart/form-data">

        <input type="hidden" name="record_id" value="<?= $record_id ?>">

        <label class="block text-sm font-medium text-gray-700 mb-1">Seleccionar archivo (PDF, JPG, PNG)</label>
        <input type="file" name="archivo" required
               class="w-full p-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-400 mb-6">

        <button type="submit"
                class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
            Subir archivo
        </button>

        <a href="/turnos-pro/profiles/<?= $user_id ?>/paciente-historia.php?id=<?= $evolucion['patient_id'] ?>"
           class="block text-center mt-4 text-gray-600 hover:text-gray-800">
            ← Volver a la historia clínica
        </a>

    </form>

</div>

</body>
</html>