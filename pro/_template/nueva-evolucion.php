<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    die("Paciente no encontrado.");
}

$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$patient_id, $user_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente no pertenece a este profesional.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva evolución</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-2xl mx-auto mt-12 bg-white p-8 rounded-xl shadow">

    <h2 class="text-2xl font-bold text-gray-800 mb-2">Nueva evolución</h2>
    <p class="text-gray-600 mb-6">Paciente: <strong><?= htmlspecialchars($paciente['name']) ?></strong></p>

    <form method="post" action="/turnos-pro/profiles/<?= $user_id ?>/guardar-evolucion.php">

        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <label class="block text-sm font-medium text-gray-700 mb-1">Motivo de consulta</label>
        <textarea name="motivo"
                  class="w-full p-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-400 mb-4"
                  rows="3"></textarea>

        <label class="block text-sm font-medium text-gray-700 mb-1">Evolución</label>
        <textarea name="evolucion" required
                  class="w-full p-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-400 mb-4"
                  rows="4"></textarea>

        <label class="block text-sm font-medium text-gray-700 mb-1">Indicaciones</label>
        <textarea name="indicaciones"
                  class="w-full p-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-400 mb-4"
                  rows="3"></textarea>

        <label class="block text-sm font-medium text-gray-700 mb-1">Diagnóstico</label>
        <input type="text" name="diagnostico"
               class="w-full p-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-400 mb-6">

        <button type="submit"
                class="w-full py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition">
            Guardar evolución
        </button>

        <a href="/turnos-pro/profiles/<?= $user_id ?>/paciente-historia.php?id=<?= $patient_id ?>"
           class="block text-center mt-4 text-gray-600 hover:text-gray-800">
            ← Volver a la historia clínica
        </a>

    </form>

</div>

</body>
</html>