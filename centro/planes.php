<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: /auth/login.php");
    exit;
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<?php if (isset($_GET['cancelada'])): ?>
    <div class="mb-4 p-3 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded">
        Tu suscripción fue cancelada en Aura. Para evitar futuros cobros, cancelala también desde Mercado Pago.
    </div>
<?php endif; ?>

<main class="flex-1 p-8">
    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Plan para centros</h1>

    <div class="grid grid-cols-1 gap-6 max-w-2xl mx-auto">

        <!-- ÚNICO PLAN -->
        <div class="bg-white shadow rounded-xl p-6 text-center border">
            <h2 class="text-xl font-bold mb-2">Plan Centro (hasta 4 profesionales)</h2>
            <p class="text-3xl font-semibold mb-4">$25.000</p>

            <a href="/centro/suscribirse-centro.php?plan=1"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
               Contratar
            </a>
        </div>

         <div class="bg-white shadow rounded-xl p-6 text-center border">
            <h2 class="text-xl font-bold mb-2">Plan Centro (hasta 8 profesionales)</h2>
            <p class="text-3xl font-semibold mb-4">$38.000</p>

            <a href="/centro/suscribirse-centro.php?plan=2"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
               Contratar
            </a>
        </div>


    </div>
</main>

<?php require __DIR__ . '/../pro/includes/footer.php'; ?>
