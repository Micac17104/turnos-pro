<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../pro/includes/db.php';

// Solo admin
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'admin') {
    die("Acceso denegado");
}

// Obtener usuarios
$stmt = $pdo->query("
    SELECT id, name, email, account_type, is_active, subscription_end, mp_subscription_status, chosen_plan
    FROM users
    ORDER BY id DESC
");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-6xl mx-auto mt-10 bg-white shadow rounded p-6">

    <h1 class="text-2xl font-bold mb-6">Usuarios del sistema</h1>

    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-200 text-left">
                <th class="p-3">ID</th>
                <th class="p-3">Nombre</th>
                <th class="p-3">Email</th>
                <th class="p-3">Tipo</th>
                <th class="p-3">Estado</th>
                <th class="p-3">Plan</th>
                <th class="p-3">Acciones</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($usuarios as $u): ?>

            <tr class="border-b hover:bg-gray-50">
                <td class="p-3"><?= $u['id'] ?></td>
                <td class="p-3"><?= htmlspecialchars($u['name']) ?></td>
                <td class="p-3"><?= htmlspecialchars($u['email']) ?></td>
                <td class="p-3 capitalize"><?= $u['account_type'] ?></td>

                <!-- Estado visual -->
                <td class="p-3">
                    <?php if ($u['is_active'] == 1): ?>
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Activo</span>
                    <?php else: ?>
                        <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Inactivo</span>
                    <?php endif; ?>
                </td>

                <!-- Plan -->
                <td class="p-3">
                    <?php if ($u['chosen_plan']): ?>
                        Plan <?= $u['chosen_plan'] ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>

                <!-- Botones -->
                <td class="p-3">
                    <div class="flex flex-wrap gap-2">

                        <a href="admin_acciones.php?action=activar&id=<?= $u['id'] ?>"
                           class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">
                           Activar
                        </a>

                        <a href="admin_acciones.php?action=desactivar&id=<?= $u['id'] ?>"
                           class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                           Desactivar
                        </a>

                        <a href="admin_acciones.php?action=sumar_mes&id=<?= $u['id'] ?>"
                           class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                           +1 mes
                        </a>

                    </div>
                </td>

            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>

</div>

</body>
</html>
