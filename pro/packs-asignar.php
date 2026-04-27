<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = "Asignar pack a paciente";
$current = "pacientes";

// Obtener pacientes del profesional
$stmt = $pdo->prepare("
    SELECT id, name
    FROM clients
    WHERE user_id = ?
    ORDER BY name ASC
");
$stmt->execute([$user_id]);
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener packs del profesional
$stmt = $pdo->prepare("
    SELECT id, name, total_sessions
    FROM packs
    WHERE owner_type = 'professional' AND owner_id = ? AND active = 1
    ORDER BY id DESC
");
$stmt->execute([$user_id]);
$packs = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Asignar pack a paciente</h1>

    <form method="post" action="packs-asignar-guardar.php"
          class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-6">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Paciente</label>
            <select name="client_id" class="w-full px-3 py-2 border rounded-lg" required>
                <option value="">Seleccionar paciente</option>
                <?php foreach ($pacientes as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= h($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Pack</label>
            <select name="pack_id" class="w-full px-3 py-2 border rounded-lg" required>
                <option value="">Seleccionar pack</option>
                <?php foreach ($packs as $p): ?>
                    <option value="<?= $p['id'] ?>">
                        <?= h($p['name']) ?> (<?= $p['total_sessions'] ?> sesiones)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button class="px-4 py-2 bg-slate-900 text-white rounded-lg">
            Asignar pack
        </button>

    </form>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
