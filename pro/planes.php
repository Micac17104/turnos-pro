<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: /auth/login.php");
    exit;
}

$plan = 1;
$precio = 8000;
?>

<main class="flex-1 p-8">
    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Suscripción profesional</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white shadow rounded-xl p-6 text-center border">
            <h2 class="text-xl font-bold mb-2">Plan 1 profesional</h2>
            <p class="text-3xl font-semibold mb-4">$<?= number_format($precio, 0, ',', '.') ?></p>

            <a href="pago-preferencia-sus.php?plan=<?= $plan ?>"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
               Pagar con MercadoPago
            </a>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>