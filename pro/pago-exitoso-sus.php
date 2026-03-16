<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">
    <h1 class="text-2xl font-semibold text-green-600 mb-4">¡Pago exitoso!</h1>

    <div class="bg-white p-6 rounded-xl shadow border max-w-lg">
        <p class="text-slate-700 mb-4">
            Tu suscripción fue procesada correctamente. En unos minutos tu cuenta quedará actualizada.
        </p>

        <a href="dashboard.php"
           class="inline-block px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
            Volver al panel
        </a>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>