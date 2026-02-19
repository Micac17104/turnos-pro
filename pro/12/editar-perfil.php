<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Datos del profesional
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar perfil</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-3xl mx-auto mt-10 bg-white p-8 rounded-xl shadow">

<a href="/turnos-pro/profiles/<?= $user_id ?>/dashboard.php"
       class="inline-block mb-6 text-gray-600 hover:text-gray-800">
        ← Volver al panel
    </a>

    <h1 class="text-2xl font-bold mb-6">Editar perfil</h1>

    <!-- FORMULARIO PRINCIPAL -->
    <form action="update-perfil.php" method="POST" enctype="multipart/form-data" class="space-y-6">

        <div>
            <label class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"
                   class="w-full p-3 border rounded-lg bg-gray-50" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Teléfono</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>"
                   class="w-full p-3 border rounded-lg bg-gray-50" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                   class="w-full p-3 border rounded-lg bg-gray-50" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Profesion</label>
            <input type="text" name="profesion"
       value="<?= htmlspecialchars($user['profesion'] ?? '') ?>"
       class="w-full p-3 border rounded-lg bg-gray-50">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Foto de perfil</label>
            <input type="file" name="photo" class="w-full p-3 border rounded-lg bg-gray-50">
        </div>

        <button class="px-6 py-3 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">
            Guardar cambios
        </button>
    </form>

    <hr class="my-10">

    <!-- NOTIFICACIONES -->
    <h2 class="text-xl font-semibold mb-4">Notificaciones</h2>

    <form action="update-notificaciones.php" method="POST" class="space-y-4">

        <label class="flex items-center gap-3">
            <input type="checkbox" name="notify_whatsapp" value="1"
                   <?= $user['notify_whatsapp'] ? 'checked' : '' ?>>
            <span class="text-gray-700">Recibir notificaciones por WhatsApp</span>
        </label>

        <label class="flex items-center gap-3">
            <input type="checkbox" name="notify_email" value="1"
                   <?= $user['notify_email'] ? 'checked' : '' ?>>
            <span class="text-gray-700">Recibir notificaciones por Email</span>
        </label>

        <button class="px-6 py-3 bg-green-600 text-white rounded-lg shadow hover:bg-green-700">
            Guardar preferencias
        </button>

    </form>

</div>

</body>
</html>