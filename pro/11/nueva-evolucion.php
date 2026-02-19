<?php
session_start();
require __DIR__ . '/../../config.php';

// Detectar si estoy en _template o en un tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

// Verificar login del profesional
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    die("Paciente no encontrado.");
}

// Verificar que el paciente pertenece al profesional
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

    <link rel="stylesheet" href="/turnos-pro/assets/style.css">

    <style>
        .form-box {
            background: #ffffff;
            padding: 25px;
            border-radius: 14px;
            max-width: 650px;
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

        textarea,
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            margin-bottom: 15px;
            background: #f9fafb;
            resize: none;
            transition: 0.2s ease;
        }

        textarea:focus,
        input:focus {
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
    </style>
</head>
<body>

<div class="form-box">
    <h2>Nueva evolución</h2>
    <p>Paciente: <strong><?= htmlspecialchars($paciente['name']) ?></strong></p>

    <form method="post" action="/turnos-pro/profiles/<?= $user_id ?>/guardar-evolucion.php">
        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <label>Motivo de consulta</label>
        <textarea name="motivo"></textarea>

        <label>Evolución</label>
        <textarea name="evolucion" required></textarea>

        <label>Indicaciones</label>
        <textarea name="indicaciones"></textarea>

        <label>Diagnóstico</label>
        <input type="text" name="diagnostico">

        <button type="submit" class="btn-submit">Guardar evolución</button>
    </form>
</div>

</body>
</html>