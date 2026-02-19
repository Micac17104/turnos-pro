<?php
session_start();
require __DIR__ . '/../../config.php';

// Verificar login
if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Detectar si estoy en _template o en un tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

// Obtener datos actuales
$stmt = $pdo->prepare("SELECT name, photo, description, location, profession, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$mensaje = "";

// Guardar datos del perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $profession = trim($_POST['profession'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $stmt = $pdo->prepare("
        UPDATE users 
        SET name = ?, description = ?, location = ?, profession = ?, phone = ?
        WHERE id = ?
    ");
    $stmt->execute([$name, $description, $location, $profession, $phone, $user_id]);

    $mensaje = "Cambios guardados correctamente.";
}

// Procesar subida de foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {

    $archivo = $_FILES['photo'];

    if ($archivo['error'] === 0) {

        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $permitidas)) {

            // Ruta destino absoluta
            $ruta = __DIR__ . "/foto.$ext";

            // Mover archivo
            move_uploaded_file($archivo['tmp_name'], $ruta);

            // Guardar en DB
            $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE id = ?");
            $stmt->execute(["foto.$ext", $user_id]);

            $mensaje = "Foto actualizada correctamente.";

        } else {
            $mensaje = "Formato no permitido. Subí JPG, PNG o WebP.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar perfil</title>

    <link rel="stylesheet" href="/turnos-pro/assets/style.css">

    <style>
        .page-box {
            max-width: 500px;
            margin: 40px auto;
        }

        h2 {
            color: #0f172a;
            margin-bottom: 15px;
        }

        .card {
            background: #ffffff;
            padding: 20px;
            border-radius: 14px;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
            border: 1px solid rgba(148, 163, 184, 0.25);
        }

        label {
            font-size: 14px;
            color: #334155;
            margin-bottom: 6px;
            display: block;
        }

        input[type="text"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            margin-bottom: 12px;
            background: #f9fafb;
            transition: 0.2s ease;
        }

        input:focus,
        textarea:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 1px rgba(14, 165, 233, 0.35);
            background: #ffffff;
        }

        .alert-success {
            background: #ecfdf3;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 15px;
            color: #166534;
            font-size: 13px;
            border: 1px solid #bbf7d0;
        }

        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #e2e8f0;
        }
    </style>
</head>
<body>

<div class="page-box">

    <div class="card">
        <h2>Editar perfil</h2>

        <?php if ($mensaje): ?>
            <div class="alert-success"><?= $mensaje ?></div>
        <?php endif; ?>

        <h3>Foto actual</h3>

        <img 
            src="/turnos-pro/profiles/<?= $user_id ?>/<?= $user['photo'] ?: 'https://via.placeholder.com/200' ?>" 
            class="profile-photo"
        >

        <form method="post" enctype="multipart/form-data">

            <label>Subir nueva foto:</label>
            <input type="file" name="photo">

            <label>Nombre completo:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>">

            <label>Descripción profesional:</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($user['description'] ?? '') ?></textarea>

            <label>Ubicación:</label>
            <input type="text" name="location" value="<?= htmlspecialchars($user['location'] ?? '') ?>">

            <label>Profesión:</label>
            <input type="text" name="profession" value="<?= htmlspecialchars($user['profession'] ?? '') ?>">

            <label>Teléfono (WhatsApp):</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">

            <button type="submit" class="btn-big" style="margin-top:10px;">
                Guardar cambios
            </button>
        </form>
    </div>

</div>

</body>
</html>