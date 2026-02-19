<?php
// Detectar si estoy en _template o en un tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

// NOTA: $name y $profession deben venir definidos desde el archivo que incluye esta vista.
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($name) ?> - <?= htmlspecialchars($profession) ?></title>

<link rel="stylesheet" href="/turnos-pro/assets/style.css">

<style>
    .profile-header {
        text-align: center;
        padding: 40px 20px;
    }

    .profile-name {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 6px;
        color: #0f172a;
    }

    .profile-profession {
        font-size: 18px;
        color: #475569;
        margin-bottom: 20px;
    }

    .profile-card {
        max-width: 480px;
        margin: 0 auto;
        background: #ffffff;
        padding: 28px;
        border-radius: 16px;
        border: 1px solid rgba(148,163,184,0.25);
        box-shadow: 0 10px 25px rgba(15,23,42,0.06);
        text-align: center;
    }

    .btn-big {
        display: inline-block;
        padding: 14px 22px;
        font-size: 16px;
        border-radius: 999px;
        background: linear-gradient(135deg, #22c55e, #0ea5e9);
        color: white;
        text-decoration: none;
        font-weight: 600;
        transition: 0.15s ease;
    }

    .btn-big:hover {
        filter: brightness(1.05);
        transform: translateY(-1px);
    }
</style>

</head>
<body>

<div class="container">

    <div class="profile-header">
        <div class="profile-name"><?= htmlspecialchars($name) ?></div>
        <div class="profile-profession"><?= htmlspecialchars($profession) ?></div>
    </div>

    <div class="profile-card">
        <h2>Reservar turno</h2>
        <p>Hacé clic para ver los horarios disponibles.</p>

        <a href="/turnos-pro/profiles/<?= $user_id ?>/turnos.php" class="btn-big">
            Ver turnos
        </a>
    </div>

</div>

</body>
</html>