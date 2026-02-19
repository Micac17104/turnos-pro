<?php
session_start();
require __DIR__ . '/../../config.php';

// Verificar login
if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Detectar tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

// Obtener datos actuales
$stmt = $pdo->prepare("SELECT name, photo, description, location, profession, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$mensaje = "";

// Guardar datos del perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $profession  = trim($_POST['profession'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');

    $stmt = $pdo->prepare("
        UPDATE users 
        SET name = ?, description = ?, location = ?, profession = ?, phone = ?
        WHERE id = ?
    ");
    $stmt->execute([$name, $description, $location, $profession, $phone, $user_id]);

    $mensaje = "Cambios guardados correctamente.";
}

// Procesar foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {

    $archivo = $_FILES['photo'];

    if ($archivo['error'] === 0) {

        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $permitidas)) {

            $ruta = __DIR__ . "/foto.$ext";

            move_uploaded_file($archivo['tmp_name'], $ruta);

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
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-lg mx-auto mt-12 bg-white p-8 rounded-xl shadow">

    <h2 class="text-2xl font-bold text-gray-800 mb-4">Editar perfil</h2>

    <?php if ($mensaje): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg border border-green-300 text-sm">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <h3 class="text-lg font-semibold text-gray-700 mb-2">Foto actual</h3>

    <img 
        src="/turnos-pro/profiles/<?= $user_id ?>/<?= $user['photo'] ?: 'https://via.placeholder.com/200' ?>"
        class="w-36 h-36 rounded-full object-cover border-4 border-gray-200 mb-6"
    >

    <form method="post" enctype="multipart/form-data" class="space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Subir nueva foto</label>
            <input type="file" name="photo"
                   class="w-full p-3 border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
            <input type="text" name="name"
                   value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                   class="w-full p-3 border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción profesional</label>
            <textarea name="description" rows="4"
                      class="w-full p-3 border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-400"><?= htmlspecialchars($user['description'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
            <input type="text" name="location"
                   value="<?= htmlspecialchars($user['location'] ?? '') ?>"
                   class="w-full p-3 border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Profesión</label>
            <input type="text" name="profession"
                   value="<?= htmlspecialchars($user['profession'] ?? '') ?>"
                   class="w-full p-3 border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono (WhatsApp)</label>
            <input type="text" name="phone"
                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                   class="w-full p-3 border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-400">
        </div>

        <button type="submit"
                class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
            Guardar cambios
        </button>

        <a href="/turnos-pro/profiles/<?= $user_id ?>/panel.php"
           class="block text-center mt-3 text-gray-600 hover:text-gray-800">
            ← Volver al panel
        </a>

    </form>

</div>

</body>
</html>