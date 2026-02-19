<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

$patient_id = $_GET['id'] ?? null;

if (!$patient_id) {
    die("Paciente no encontrado.");
}

$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$patient_id, $user_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente no pertenece a este profesional.");
}

$stmt = $pdo->prepare("SELECT * FROM patients_extra WHERE patient_id = ?");
$stmt->execute([$patient_id]);
$extra = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar datos clínicos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-2xl mx-auto mt-12 bg-white p-8 rounded-xl shadow">

    <h2 class="text-2xl font-bold text-gray-800 mb-2">Editar datos clínicos</h2>
    <p class="text-gray-600 mb-6">Paciente: <strong><?= htmlspecialchars($paciente['name']) ?></strong></p>

    <form method="post" action="/turnos-pro/profiles/<?= $user_id ?>/guardar-datos-clinicos.php">

        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <label class="block text-sm font-medium text-gray-700 mb-1">Antecedentes</label>
        <textarea name="antecedentes"
                  class="w-full p-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-400 mb-4"
                  rows="3"><?= htmlspecialchars($extra['antecedentes'] ?? '') ?></textarea>

        <label class="block text-sm font-medium text-gray-700 mb-1">Alergias</label>
        <textarea name="alergias"
                  class="w-full p-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-400 mb-4"
                  rows="3"><?= htmlspecialchars($extra['alergias'] ?? '') ?></textarea>

        <label class="block text-sm font-medium text-gray-700 mb-1">Medicación actual</label>
        <textarea name="medicacion"
                  class="w-full p-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-400 mb-4"
                  rows="3"><?= htmlspecialchars($extra['medicacion'] ?? '') ?></textarea>

        <label class="block text-sm font-medium text-gray-700 mb-1">Patologías crónicas</label>
        <textarea name="patologias"
                  class="w-full p-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-400 mb-4"
                  rows="3"><?= htmlspecialchars($extra['patologias'] ?? '') ?></textarea>

        <label class="block text-sm font-medium text-gray-700 mb-1">Obra social</label>
        <input type="text" name="obra_social"
               value="<?= htmlspecialchars($extra['obra_social'] ?? '') ?>"
               class="w-full p-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-400 mb-4">

        <label class="block text-sm font-medium text-gray-700 mb-1">Número de afiliado</label>
        <input type="text" name="nro_afiliado"
               value="<?= htmlspecialchars($extra['nro_afiliado'] ?? '') ?>"
               class="w-full p-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-400 mb-6">

        <button type="submit"
                class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
            Guardar datos clínicos
        </button>

        <a href="/turnos-pro/profiles/<?= $user_id ?>/paciente-historia.php?id=<?= $patient_id ?>"
           class="block text-center mt-4 text-gray-600 hover:text-gray-800">
            ← Volver a la historia clínica
        </a>

    </form>

</div>

</body>
</html>