<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Buscador
$search = trim($_GET['search'] ?? '');

if ($search) {
    $stmt = $pdo->prepare("
        SELECT * FROM clients 
        WHERE user_id = ? 
        AND name LIKE ? 
        ORDER BY name ASC
    ");
    $stmt->execute([$user_id, "%$search%"]);
} else {
    $stmt = $pdo->prepare("
        SELECT * FROM clients 
        WHERE user_id = ? 
        ORDER BY name ASC
    ");
    $stmt->execute([$user_id]);
}

$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pacientes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="flex">

    <!-- SIDEBAR -->
    <aside class="w-64 h-screen bg-white shadow-lg p-6 flex flex-col gap-6">
        <h1 class="text-2xl font-bold text-gray-800">Panel</h1>

        <nav class="flex flex-col gap-3">
            <a href="/turnos-pro/profiles/<?= $user_id ?>/panel.php" class="text-gray-700 hover:text-blue-600">Dashboard</a>
            <a href="/turnos-pro/profiles/<?= $user_id ?>/agenda.php" class="text-gray-700 hover:text-blue-600">Agenda</a>
            <a href="/turnos-pro/profiles/<?= $user_id ?>/pacientes.php" class="text-blue-600 font-semibold">Pacientes</a>
            <a href="/turnos-pro/profiles/<?= $user_id ?>/estadisticas.php" class="text-gray-700 hover:text-blue-600">Estadísticas</a>
            <a href="/turnos-pro/profiles/<?= $user_id ?>/config-pagos.php" class="text-gray-700 hover:text-blue-600">Pagos online</a>
            <a href="/turnos-pro/profiles/<?= $user_id ?>/editar-perfil.php" class="text-gray-700 hover:text-blue-600">Editar perfil</a>
            <a href="/turnos-pro/profiles/<?= $user_id ?>/editar-horarios.php" class="text-gray-700 hover:text-blue-600">Horarios</a>
        </nav>
    </aside>

    <!-- CONTENIDO -->
    <main class="flex-1 p-10">

        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-semibold text-gray-800">Pacientes</h2>

            <!-- Botón agregar -->
            <button onclick="document.getElementById('modal').classList.remove('hidden')"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow">
                + Agregar paciente
            </button>
        </div>

        <!-- Buscador -->
        <form method="GET" class="mb-6">
            <input 
                type="text" 
                name="search" 
                placeholder="Buscar por nombre o apellido..." 
                value="<?= htmlspecialchars($search) ?>"
                class="w-full p-3 rounded-lg border border-gray-300"
            >
        </form>

        <!-- Lista de pacientes -->
        <div class="bg-white rounded-xl shadow divide-y">

            <?php if (empty($clients)): ?>
                <p class="p-6 text-gray-500">No se encontraron pacientes.</p>
            <?php else: ?>
                <?php foreach ($clients as $c): ?>
                    <?php
                    $mensaje = urlencode("Hola {$c['name']}, ¿cómo estás?");
                    $whatsapp = "https://wa.me/{$c['phone']}?text={$mensaje}";
                    ?>
                    <div class="p-6 flex justify-between items-center">

                        <div>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($c['name']) ?></p>
                            <p class="text-gray-500 text-sm"><?= $c['phone'] ?></p>
                        </div>

                        <div class="flex gap-2">
                            <a href="<?= $whatsapp ?>" target="_blank" class="px-3 py-1 bg-green-500 text-white rounded text-sm">WhatsApp</a>
                            <a href="/turnos-pro/profiles/<?= $user_id ?>/paciente-historia.php?id=<?= $c['id'] ?>" class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm">Historia</a>
                            <a href="/turnos-pro/profiles/<?= $user_id ?>/paciente-editar-clinico.php?id=<?= $c['id'] ?>" class="px-3 py-1 bg-blue-200 text-blue-700 rounded text-sm">Editar</a>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>

    </main>

</div>

<!-- MODAL AGREGAR PACIENTE -->
<div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl shadow-lg w-96">

        <h3 class="text-xl font-semibold mb-4">Agregar paciente</h3>

        <form method="POST" action="/turnos-pro/profiles/<?= $user_id ?>/save_client.php">

            <input type="text" name="name" placeholder="Nombre completo" required
                class="w-full p-3 mb-3 border rounded">

            <input type="text" name="phone" placeholder="Teléfono" required
                class="w-full p-3 mb-3 border rounded">

            <input type="email" name="email" placeholder="Email" required>

    

            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="document.getElementById('modal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-200 rounded">
                    Cancelar
                </button>

                <button class="px-4 py-2 bg-blue-600 text-white rounded">
                    Guardar
                </button>
            </div>

        </form>

    </div>

</div>

</body>
</html>