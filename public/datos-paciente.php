<?php
session_start();
require __DIR__ . '/../config.php';

// Si el paciente está logueado → NO usar este archivo
if (isset($_SESSION['paciente_id'])) {
    header("Location: /turnos-pro/public/paciente-sacar-turno.php");
    exit;
}

$user_id = $_GET['user_id'] ?? null;
$fecha = $_GET['fecha'] ?? null;
$hora = $_GET['hora'] ?? null;

if (!$user_id || !$fecha || !$hora) {
    die("Datos incompletos.");
}

// Traer datos del profesional
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    die("Profesional no encontrado.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Datos del paciente</title>

    <style>
        body { background:#f5f5f5; font-family:Arial; }
        .container { max-width:500px; margin:40px auto; background:white; padding:25px; border-radius:14px; }
        h2 { color:#0f172a; font-weight:700; margin-bottom:20px; }

        input {
            width:100%;
            padding:12px;
            margin-bottom:15px;
            border-radius:10px;
            border:1px solid #cbd5e1;
            font-size:16px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            padding: 12px 22px;
            border-radius: 999px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            display:block;
            text-align:center;
            border:none;
            cursor:pointer;
            width:100%;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Datos del paciente</h2>

    <p>Turno con <strong><?= htmlspecialchars($pro['name']) ?></strong></p>
    <p><?= date("d/m/Y", strtotime($fecha)) ?> — <?= $hora ?> hs</p>

    <form method="post" action="/turnos-pro/public/confirmar-turno.php">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <input type="hidden" name="fecha" value="<?= $fecha ?>">
        <input type="hidden" name="hora" value="<?= $hora ?>">

        <input type="text" name="nombre" placeholder="Tu nombre" required>
        <input type="text" name="telefono" placeholder="Teléfono" required>
        <input type="email" name="email" placeholder="Email" required>

        <button class="btn-primary">Confirmar turno</button>
    </form>

</div>

</body>
</html>