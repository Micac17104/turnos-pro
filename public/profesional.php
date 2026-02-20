<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';

$prof_id = $_GET['id'] ?? null;

if (!$prof_id) {
    die("Profesional no encontrado.");
}

// Traer datos del profesional
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$prof_id]);
$prof = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prof) {
    die("Profesional no encontrado.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($prof['name']) ?> - Turnos Online</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
            font-size: 32px;
            color: #0f172a;
        }

        .subtitle {
            font-size: 18px;
            color: #475569;
            margin-bottom: 20px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            padding: 12px 22px;
            border-radius: 999px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-top: 20px;
        }

        .info-box {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            border: 1px solid #e2e8f0;
        }

        .info-title {
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .info-text {
            color: #475569;
        }
    </style>
</head>
<body>

<div class="container">

    <h1><?= htmlspecialchars($prof['name']) ?></h1>
    <div class="subtitle"><?= htmlspecialchars($prof['specialty'] ?? 'Profesional de la salud') ?></div>

    <div class="info-box">
        <div class="info-title">Dirección</div>
        <div class="info-text"><?= htmlspecialchars($prof['address'] ?? 'No especificada') ?></div>
    </div>

    <div class="info-box">
        <div class="info-title">Descripción</div>
        <div class="info-text">
            <?= htmlspecialchars($prof['bio'] ?? 'Este profesional aún no agregó una descripción.') ?>
        </div>
    </div>

    <a href="/turnos-pro/public/sacar-turno.php?user_id=<?= $prof_id ?>" class="btn-primary">
        Sacar turno
    </a>

</div>

</body>
</html>