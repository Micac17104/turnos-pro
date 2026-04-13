<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'center') {
    header("Location: /auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Suscripción cancelada</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-lg mx-auto mt-20 bg-white shadow p-6 rounded">

    <h1 class="text-2xl font-bold mb-4 text-red-600">Suscripción cancelada</h1>

    <p class="mb-4 text-gray-700">
        Tu suscripción fue cancelada correctamente en Aura.
    </p>

    <div class="p-4 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded mb-4">
        <strong>Importante:</strong><br>
        Tu suscripción fue cancelada en Aura. Para evitar futuros cobros, cancelala también desde tu cuenta de Mercado Pago.
    </div>

    <a href="/centro/panel.php"
       class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Volver al panel
    </a>

</div>

</body>
</html>
