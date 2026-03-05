<?php
// /pro/plantilla-nueva.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Nueva plantilla de historia clínica';
$current    = 'pacientes';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">
            Nueva plantilla de historia clínica
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Definí los campos que querés usar en esta plantilla.
        </p>
    </div>

    <form method="post" action="plantilla-guardar.php" class="space-y-6 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Título de la plantilla</label>
            <input type="text" name="title" required
                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Campos</label>
            <p class="text-xs text-slate-500 mb-3">
                 Escribí un campo por línea.  (tipos, etc.).
            </p>
            <textarea name="fields_raw" rows="6" required
                      class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80"
                      placeholder="Motivo de consulta&#10;Diagnóstico inicial&#10;Observaciones"></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <a href="pacientes.php"
               class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 text-sm hover:bg-slate-300">
                Cancelar
            </a>

            <a href="plantilla-eliminar.php?id=<?= $p['id'] ?>"
   onclick="return confirm('¿Eliminar esta plantilla?')"
   class="text-red-600 hover:underline">
   Eliminar
</a>

            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm hover:bg-slate-800">
                Guardar plantilla
            </button>
        </div>
    </form>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>