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
    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Planes para centros</h1>

    <div class="grid grid-cols-1 gap-6 max-w-2xl mx-auto">

        <!-- Plan 1 -->
        <div class="bg-white shadow rounded-xl p-6 text-center border">
            <h2 class="text-xl font-bold mb-2">Plan 1</h2>
            <p class="text-3xl font-semibold mb-4">$8.000</p>

            <a href="/centro/suscribirse-centro.php?plan=1"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
               Contratar
            </a>
        </div>

        <!-- Plan 2 -->
        <div class="bg-white shadow rounded-xl p-6 text-center border">
            <h2 class="text-xl font-bold mb-2">Plan 2</h2>
            <p class="text-3xl font-semibold mb-4">$13.000</p>

            <a href="/centro/suscribirse-centro.php?plan=2"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
               Contratar
            </a>
        </div>

        <!-- Plan 3 -->
        <div class="bg-white shadow rounded-xl p-6 text-center border">
            <h2 class="text-xl font-bold mb-2">Plan 3</h2>
            <p class="text-3xl font-semibold mb-4">$18.000</p>

            <a href="/centro/suscribirse-centro.php?plan=3"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
               Contratar
            </a>
        </div>

        <!-- Plan 4 -->
        <div class="bg-white shadow rounded-xl p-6 text-center border">
            <h2 class="text-xl font-bold mb-2">Plan 4</h2>
            <p class="text-3xl font-semibold mb-4">$23.000</p>

            <a href="/centro/suscribirse-centro.php?plan=4"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
               Contratar
            </a>
        </div>

        <!-- Plan 5 -->
        <div class="bg-white shadow rounded-xl p-6 text-center border">
            <h2 class="text-xl font-bold mb-2">Plan 5</h2>
            <p class="text-3xl font-semibold mb-4">$28.000</p>

            <a href="/centro/suscribirse-centro.php?plan=5"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
               Contratar
            </a>
        </div>

        <!-- Plan 6 -->
        <div class="bg-white shadow rounded-xl p-6 text-center border">
            <h2 class="text-xl font-bold mb-2">Plan 6</h2>
            <p class="text-3xl font-semibold mb-4">$100</p>

            <a href="/centro/suscribirse-centro.php?plan=5"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
               Contratar
            </a>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../pro/includes/footer.php'; ?>
