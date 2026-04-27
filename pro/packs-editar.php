<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = "Editar pack";
$current = "packs";

$pack_id = $_GET['id'] ?? null;

if (!$pack_id) {
    die("Pack no encontrado.");
}

// Obtener pack
$stmt = $pdo->prepare("
    SELECT *
    FROM packs
    WHERE id = ? AND owner_type = 'professional' AND owner_id = ?
");
$stmt->execute([$pack_id, $user_id]);
$pack = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pack) {
    die("No tenés permiso para editar este pack.");
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Editar pack</h1>

    <form method="post" action="packs-editar-guardar.php"
          class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-6">

        <input type="hidden" name="id" value="<?= $pack['id'] ?>">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del pack</label>
            <input type="text" name="name" value="<?= h($pack['name']) ?>"
                   class="w-full px-3 py-2 border rounded-lg" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Cantidad de sesiones</label>
            <input type="number" name="total_sessions" value="<?= $pack['total_sessions'] ?>"
                   class="w-full px-3 py-2 border rounded-lg" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Precio</label>
            <input type="number" step="0.01" name="price" value="<?= $pack['price'] ?>"
                   class="w-full px-3 py-2 border rounded-lg" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
            <select name="active" class="w-full px-3 py-2 border rounded-lg">
                <option value="1" <?= $pack['active'] ? 'selected' : '' ?>>Activo</option>
                <option value="0" <?= !$pack['active'] ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>

        <button class="px-4 py-2 bg-slate-900 text-white rounded-lg">
            Guardar cambios
        </button>

    </form>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
