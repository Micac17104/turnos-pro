<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Pacientes';
$current    = 'pacientes';

// Buscador
$search = trim($_GET['search'] ?? '');

if ($search) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM clients
        WHERE user_id = ?
        AND name LIKE ?
        ORDER BY name ASC
    ");
    $stmt->execute([$user_id, "%$search%"]);
} else {
    $stmt = $pdo->prepare("
        SELECT *
        FROM clients
        WHERE user_id = ?
        ORDER BY name ASC
    ");
    $stmt->execute([$user_id]);
}

$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">Pacientes</h1>

        <button onclick="document.getElementById('modal').classList.remove('hidden')"
                class="px-4 py-2 bg-slate-900 text-white rounded-lg shadow hover:bg-slate-800">
            + Agregar paciente
        </button>
    </div>

    <!-- Buscador -->
    <form method="GET" class="mb-6">
        <input
            type="text"
            name="search"
            placeholder="Buscar por nombre..."
            value="<?= h($search) ?>"
            class="w-full px-4 py-3 rounded-lg border border-slate-300"
        >
    </form>

    <!-- Lista de pacientes -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 divide-y">

        <?php if (empty($clients)): ?>
            <p class="p-6 text-slate-500">No se encontraron pacientes.</p>

        <?php else: ?>
            <?php foreach ($clients as $c): ?>
                <?php
                $mensaje = urlencode("Hola {$c['name']}, ¿cómo estás?");
                $whatsapp = "https://wa.me/{$c['phone']}?text={$mensaje}";
                ?>
                <div class="p-6 flex justify-between items-center">

                    <div>
                        <p class="font-semibold text-slate-900"><?= h($c['name']) ?></p>
                        <p class="text-slate-500 text-sm"><?= h($c['phone']) ?></p>
                    </div>

                    <div class="flex gap-2">

                        <a href="<?= $whatsapp ?>" target="_blank"
                           class="px-3 py-1 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-500">
                            WhatsApp
                        </a>

                        <a href="paciente-historia.php?id=<?= $c['id'] ?>"
                           class="px-3 py-1 bg-slate-200 text-slate-700 rounded text-sm hover:bg-slate-300">
                            Historia
                        </a>

                        <a href="paciente-datos-editar.php?id=<?= $c['id'] ?>"
                           class="px-3 py-1 bg-slate-200 text-slate-700 rounded text-sm hover:bg-slate-300">
                            Editar
                        </a>

                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

</main>

<!-- MODAL AGREGAR PACIENTE -->
<div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl shadow-lg w-96">

        <h3 class="text-xl font-semibold mb-4">Agregar paciente</h3>

        <form method="POST" action="paciente-guardar.php">

            <input type="text" name="name" placeholder="Nombre completo" required
                   class="w-full p-3 mb-3 border rounded">

            <input type="text" name="phone" placeholder="Teléfono" required
                   class="w-full p-3 mb-3 border rounded">

            <input type="email" name="email" placeholder="Email"
                   class="w-full p-3 mb-3 border rounded">

            <div class="flex justify-end gap-3 mt-4">
                <button type="button"
                        onclick="document.getElementById('modal').classList.add('hidden')"
                        class="px-4 py-2 bg-slate-200 rounded">
                    Cancelar
                </button>

                <button class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800">
                    Guardar
                </button>
            </div>

        </form>

    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>