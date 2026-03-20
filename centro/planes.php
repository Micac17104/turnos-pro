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
    <title>Planes del centro</title>
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

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Elegí tu plan de suscripción</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php
        $planes = [
            1 => 8000,
            2 => 13000,
            3 => 18000,
            4 => 23000,
            5 => 28000
        ];

        <?php if (isset($_GET['expired'])): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
        Tu suscripción está inactiva. Pagá para recuperar acceso.
    </div>
<?php endif; ?>

        foreach ($planes as $profesionales => $precio):
        ?>
            <div class="bg-white shadow rounded-xl p-6 text-center border">
                <h2 class="text-xl font-bold mb-2">
                    <?= $profesionales ?> Profesional<?= $profesionales > 1 ? 'es' : '' ?>
                </h2>
                <p class="text-3xl font-semibold mb-4">$<?= number_format($precio, 0, ',', '.') ?></p>

                <a href="pago-preferencia.php?plan=<?= $profesionales ?>"
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                   Pagar con MercadoPago
                </a>
            </div>
        <?php endforeach; ?>
    </div>

</div>
</body>
</html>