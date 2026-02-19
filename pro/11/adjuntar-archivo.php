<?php
session_start();
require __DIR__ . '/../../config.php';

// Validar login del profesional
if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Detectar si estoy en _template o en un tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

$record_id = $_GET['record_id'] ?? null;

if (!$record_id) {
    die("Evolución no encontrada.");
}

// Obtener evolución
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

    <link rel="stylesheet" href="/turnos-pro/assets/style.css">

    <style>
        .form-box {
            background: #ffffff;
            padding: 25px;
            border-radius: 14px;
            max-width: 600px;
            margin: 40px auto;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
            border: 1px solid rgba(148, 163, 184, 0.25);
        }
        h2 {
            margin-bottom: 15px;
            color: #0f172a;
            font-weight: 600;
        }
        label {
            font-size: 14px;
            color: #334155;
            margin-bottom: 6px;
            display: block;
        }
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            margin-bottom: 15px;
            background: #f9fafb;
        }
        input[type="file"]:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 1px rgba(14, 165, 233, 0.35);
            background: #ffffff;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            border-radius: 999px;
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            color: white;
            border: none;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: 0.2s ease;
        }
        .btn-submit:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #0ea5e9;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover {
            color: #0284c7;
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>Adjuntar archivo</h2>

    <p><strong>Paciente:</strong> <?= htmlspecialchars($evolucion['paciente_nombre']) ?></p>
    <p><strong>Fecha de evolución:</strong> <?= date("d/m/Y H:i", strtotime($evolucion['fecha'])) ?></p>

    <form method="post" action="/turnos-pro/profiles/<?= $user_id ?>/guardar-archivo.php" enctype="multipart/form-data">
        <input type="hidden" name="record_id" value="<?= $record_id ?>">

        <label>Seleccionar archivo (PDF, JPG, PNG)</label>
        <input type="file" name="archivo" required>

        <button type="submit" class="btn-submit">Subir archivo</button>
    </form>

    <a class="back-link" href="/turnos-pro/profiles/<?= $user_id ?>/paciente-historia.php?id=<?= $evolucion['patient_id'] ?>">
        ← Volver a la historia clínica
    </a>
</div>

</body>
</html>