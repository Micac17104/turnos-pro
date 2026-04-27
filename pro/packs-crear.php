<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = "Crear pack";
$current = "pacientes";

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Crear pack de sesiones</h1>

    <form method="post" action="packs-guardar.php"
          class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-6">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del pack</label>
            <input type="text" name="name" class="w-full px-3 py-2 border rounded-lg" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Cantidad de sesiones</label>
            <input type="number" name="total_sessions" class="w-full px-3 py-2 border rounded-lg" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Precio</label>
            <input type="number" step="0.01" name="price" class="w-full px-3 py-2 border rounded-lg" required>
        </div>

        <button class="px-4 py-2 bg-slate-900 text-white rounded-lg">
            Guardar pack
        </button>

    </form>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
