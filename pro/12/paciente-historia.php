<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

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

$stmt = $pdo->prepare("
    SELECT * FROM clinical_records
    WHERE patient_id = ? AND user_id = ?
    ORDER BY fecha DESC
");
$stmt->execute([$patient_id, $user_id]);
$evoluciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historia Clínica</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-4xl mx-auto py-10">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            Historia Clínica de <?= htmlspecialchars($paciente['name']) ?>
        </h1>

        <a href="/turnos-pro/profiles/<?= $user_id ?>/dashboard.php"
           class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
            ← Volver
        </a>
    </div>

    <!-- DATOS CLÍNICOS -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Datos clínicos</h2>

            <a href="/turnos-pro/profiles/<?= $user_id ?>/paciente-editar-clinico.php?id=<?= $patient_id ?>"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Editar datos clínicos
            </a>
        </div>

        <div class="space-y-2 text-gray-700">
            <p><strong>Antecedentes:</strong> <?= nl2br($extra['antecedentes'] ?? 'No registrado') ?></p>
            <p><strong>Alergias:</strong> <?= nl2br($extra['alergias'] ?? 'No registrado') ?></p>
            <p><strong>Medicación:</strong> <?= nl2br($extra['medicacion'] ?? 'No registrado') ?></p>
            <p><strong>Patologías:</strong> <?= nl2br($extra['patologias'] ?? 'No registrado') ?></p>
            <p><strong>Obra social:</strong> <?= $extra['obra_social'] ?? 'No registrado' ?></p>
            <p><strong>Nro afiliado:</strong> <?= $extra['nro_afiliado'] ?? 'No registrado' ?></p>
        </div>
    </div>

    <!-- EVOLUCIONES -->
    <div class="bg-white p-6 rounded-xl shadow">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Evoluciones</h2>

            <a href="/turnos-pro/profiles/<?= $user_id ?>/nueva-evolucion.php?patient_id=<?= $patient_id ?>"
               class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Nueva evolución
            </a>
        </div>

        <?php if (empty($evoluciones)): ?>
            <p class="text-gray-500">No hay evoluciones registradas.</p>

        <?php else: ?>
            <div class="space-y-6">

                <?php foreach ($evoluciones as $e): ?>
                    <div class="p-5 bg-gray-50 border border-gray-200 rounded-xl">

                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            <?= date("d/m/Y H:i", strtotime($e['fecha'])) ?>
                        </h3>

                        <p><strong>Motivo:</strong> <?= nl2br($e['motivo']) ?></p>
                        <p><strong>Evolución:</strong> <?= nl2br($e['evolucion']) ?></p>
                        <p><strong>Indicaciones:</strong> <?= nl2br($e['indicaciones']) ?></p>
                        <p><strong>Diagnóstico:</strong> <?= $e['diagnostico'] ?></p>

                        <?php
                        $stmtFiles = $pdo->prepare("SELECT * FROM clinical_files WHERE record_id = ?");
                        $stmtFiles->execute([$e['id']]);
                        $archivos = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <?php if (!empty($archivos)): ?>
                            <div class="mt-4">
                                <strong>Archivos adjuntos:</strong>
                                <ul class="list-disc ml-6 mt-2 text-blue-700">
                                    <?php foreach ($archivos as $f): ?>
                                        <li>
                                            <a href="/turnos-pro/uploads/<?= $f['file_path'] ?>" target="_blank">
                                                <?= htmlspecialchars($f['file_name']) ?>
                                            </a>

                                            <a href="/turnos-pro/profiles/<?= $user_id ?>/eliminar-archivo.php?id=<?= $f['id'] ?>"
                                               class="text-red-600 ml-2"
                                               onclick="return confirm('¿Eliminar archivo?');">
                                                Eliminar
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <a href="/turnos-pro/profiles/<?= $user_id ?>/adjuntar-archivo.php?record_id=<?= $e['id'] ?>"
                           class="inline-block mt-4 px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                            Adjuntar archivo
                        </a>

                    </div>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>