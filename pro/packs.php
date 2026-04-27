<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = "Packs de sesiones";
$current = "pacientes";

// Obtener packs del profesional
$stmt = $pdo->prepare("
    SELECT id, name, total_sessions, price, active
    FROM packs
    WHERE owner_type = 'professional' AND owner_id = ?
    ORDER BY id DESC
");
$stmt->execute([$user_id]);
$packs = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Packs de sesiones</h1>

        <a href="packs-crear.php"
           class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm">
            Crear pack
        </a>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">

        <?php if (empty($packs)): ?>
            <p class="text-sm text-slate-500">Todavía no creaste packs.</p>
        <?php else: ?>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-600 border-b">
                        <th class="py-2">Nombre</th>
                        <th class="py-2">Sesiones</th>
                        <th class="py-2">Precio</th>
                        <th class="py-2">Estado</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packs as $p): ?>
                        <tr class="border-b">
                            <td class="py-2"><?= h($p['name']) ?></td>
                            <td><?= $p['total_sessions'] ?></td>
                            <td>$<?= number_format($p['price'], 2) ?></td>
                            <td><?= $p['active'] ? "Activo" : "Inactivo" ?></td>
                            <td>
                                <a href="packs-editar.php?id=<?= $p['id'] ?>"
                                   class="text-slate-900 hover:underline text-sm">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>

    </div>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
