<?php
session_start();
require '../config.php';

$center_id = $_SESSION['user_id'] ?? null;
if (!$center_id || ($_SESSION['account_type'] !== 'center' && $_SESSION['account_type'] !== 'secretary')) {
    header("Location: /auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pago no completado</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100">

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div style="margin-left:260px; padding:24px;">

    <div class="bg-white p-4 mb-4 shadow rounded-lg flex justify-between items-center">
        <div><strong>TurnosPro – Centro</strong></div>
        <div>
            <?= htmlspecialchars($_SESSION['user_name'] ?? 'Centro') ?>
            &nbsp;|&nbsp;
            <a href="../auth/logout.php" style="color:#0ea5e9;text-decoration:none;">Salir</a>
        </div>
    </div>

    <main class="flex-1">
        <h1 class="text-2xl font-semibold text-red-600 mb-4">El pago no se completó</h1>

        <div class="bg-white p-6 rounded-xl shadow border max-w-lg">
            <p class="text-slate-700 mb-4">
                El pago fue cancelado o no pudo procesarse correctamente.
            </p>

            <a href="planes.php"
               class="inline-block px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
                Volver a los planes
            </a>
        </div>
    </main>

</div>
</body>
</html>