<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "ID del centro logueado: " . ($_SESSION['user_id'] ?? 'NO HAY SESSION');
exit;


/* ---------------------------------------------------------
   1) Validación de sesión ANTES de enviar cualquier HTML
--------------------------------------------------------- */
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: /auth/login.php");
    exit;
}

/* ---------------------------------------------------------
   2) Autenticación del centro
--------------------------------------------------------- */
require __DIR__ . '/includes/auth.php';

/* ---------------------------------------------------------
   3) Cargar layout (header y sidebar del profesional)
--------------------------------------------------------- */
require __DIR__ . '/../pro/includes/header.php';
require __DIR__ . '/includes/sidebar.php';

?>

<main class="flex-1 p-8">
    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Planes para centros</h1>

    <div class="grid grid-cols-1 gap-6 max-w-2xl mx-auto">


        <!-- Plan Básico -->
        <div class="bg-white shadow rounded-xl p-6 text-center border">
            <h2 class="text-xl font-bold mb-2">Plan Básico</h2>
            <p class="text-3xl font-semibold mb-4">$8.000</p>

            <a href="pago-preferencia.php?plan=basico"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
               Contratar
            </a>
        </div>

        <!-- Plan Pro -->
        <div class="bg-white shadow rounded-xl p-6 text-center border">
            <h2 class="text-xl font-bold mb-2">Plan Pro</h2>
            <p class="text-3xl font-semibold mb-4">$15.000</p>

            <a href="pago-preferencia.php?plan=pro"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
               Contratar
            </a>
        </div>

        <!-- Plan Premium -->
        <div class="bg-white shadow rounded-xl p-6 text-center border">
            <h2 class="text-xl font-bold mb-2">Plan Premium</h2>
            <p class="text-3xl font-semibold mb-4">$25.000</p>

            <a href="pago-preferencia.php?plan=premium"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
               Contratar
            </a>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../pro/includes/footer.php'; ?>
