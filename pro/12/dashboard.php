<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Datos del profesional
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Últimos 2 pacientes
$stmt = $pdo->prepare("SELECT * FROM clients WHERE user_id = ? ORDER BY id DESC LIMIT 2");
$stmt->execute([$user_id]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Próximos 2 turnos (sin cancelados)
$stmt2 = $pdo->prepare("
    SELECT a.*, c.name AS client_name, c.phone AS client_phone
    FROM appointments a
    JOIN clients c ON a.client_id = c.id
    WHERE a.user_id = ?
    AND a.date >= CURDATE()
    AND a.status != 'cancelled'
    ORDER BY a.date ASC, a.time ASC
    LIMIT 2
");
$stmt2->execute([$user_id]);
$appointments = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel del Profesional</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="flex">

    <!-- SIDEBAR -->
    <aside class="w-64 h-screen bg-white shadow-xl p-6 flex flex-col gap-4">

        <h2 class="text-xl font-bold mb-4">Panel</h2>

        <a href="/turnos-pro/profiles/<?= $user_id ?>/agenda.php" class="text-gray-700 hover:text-blue-600">📅 Agenda</a>
        <a href="/turnos-pro/profiles/<?= $user_id ?>/agenda.php" class="text-gray-700 hover:text-blue-600">📋 Turnos</a>
        <a href="/turnos-pro/profiles/<?= $user_id ?>/pacientes.php" class="text-gray-700 hover:text-blue-600">👤 Pacientes</a>
        <a href="/turnos-pro/profiles/<?= $user_id ?>/editar-horarios.php" class="text-gray-700 hover:text-blue-600">⏰ Editar horarios</a>
        <a href="/turnos-pro/profiles/<?= $user_id ?>/editar-perfil.php" class="text-gray-700 hover:text-blue-600">⚙️ Editar perfil</a>
        <a href="/turnos-pro/profiles/<?= $user_id ?>/estadisticas.php" class="text-gray-700 hover:text-blue-600">📊 Estadísticas</a>
        <a href="/turnos-pro/profiles/<?= $user_id ?>/config-pagos.php" class="text-gray-700 hover:text-blue-600">💳 Pagos</a>

        <div class="mt-auto pt-6 border-t">
            <a href="/turnos-pro/index.php" class="text-red-600 hover:text-red-800 font-semibold">
                🔒 Cerrar sesión
            </a>
        </div>

    </aside>

    <!-- CONTENIDO -->
    <main class="flex-1 p-10">

        <h1 class="text-3xl font-bold mb-8">Bienvenido, <?= htmlspecialchars($user['name']) ?></h1>

        <!-- TARJETA TURNOS -->
        <div class="bg-white p-6 rounded-xl shadow mb-8">
            <h2 class="text-xl font-semibold mb-4">Próximos turnos</h2>

            <?php if (!$appointments): ?>
                <p class="text-gray-600">No hay turnos próximos.</p>
            <?php endif; ?>

            <?php foreach ($appointments as $a): ?>
                <div id="turno-<?= $a['id'] ?>" class="p-4 border rounded-lg mb-3 bg-gray-50">

                    <p class="font-medium text-gray-800">
                        <?= $a['date'] ?> — <?= substr($a['time'],0,5) ?> hs
                    </p>

                    <p class="text-gray-600 text-sm"><?= htmlspecialchars($a['client_name']) ?></p>

                    <p class="text-xs text-gray-500 mt-1">
                        Estado:
                        <?php if ($a['status'] === 'confirmed'): ?>
                            <span class="text-green-600 font-medium">Confirmado</span>
                        <?php elseif ($a['status'] === 'pending'): ?>
                            <span class="text-blue-600 font-medium">Pendiente</span>
                        <?php elseif ($a['status'] === 'attended'): ?>
                            <span class="text-blue-800 font-medium">Atendido</span>
                        <?php endif; ?>
                    </p>

                    <div class="flex gap-3 mt-3">

                        <!-- EDITAR -->
                        <button 
                            onclick="abrirEditarTurno(
                                <?= $a['id'] ?>,
                                '<?= $a['date'] ?>',
                                '<?= $a['time'] ?>',
                                '<?= $a['status'] ?>'
                            )"
                            class="text-blue-600 text-sm hover:underline">
                            Editar
                        </button>

                        <!-- CANCELAR (modal + AJAX) -->
                        <button 
                            onclick="confirmarCancelacion(<?= $a['id'] ?>)"
                            class="text-red-600 text-sm hover:underline">
                            Cancelar
                        </button>

                    </div>

                </div>
            <?php endforeach; ?>

            <a href="/turnos-pro/profiles/<?= $user_id ?>/agenda.php" class="text-blue-600 text-sm hover:underline">
                Ver más turnos →
            </a>
        </div>

        <!-- TARJETA PACIENTES -->
        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="text-xl font-semibold mb-4">Pacientes recientes</h2>

            <?php if (!$clients): ?>
                <p class="text-gray-600">No hay pacientes cargados.</p>
            <?php endif; ?>

            <?php foreach ($clients as $c): ?>
                <div class="p-4 border rounded-lg mb-3 bg-gray-50">
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($c['name']) ?></p>
                    <p class="text-gray-600 text-sm"><?= $c['phone'] ?></p>

                    <a href="/turnos-pro/profiles/<?= $user_id ?>/paciente-historia.php?id=<?= $c['id'] ?>"
                       class="text-blue-600 text-sm hover:underline">
                        Ver historia clínica
                    </a>
                </div>
            <?php endforeach; ?>

            <a href="/turnos-pro/profiles/<?= $user_id ?>/clientes.php" class="text-blue-600 text-sm hover:underline">
                Ver más pacientes →
            </a>
        </div>

    </main>

</div>

<!-- MODAL CONFIRMAR CANCELACIÓN -->
<div id="modalConfirmar" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-xl shadow w-80">
        <p class="text-lg font-semibold mb-4">¿Seguro que querés cancelar este turno?</p>

        <div class="flex justify-end gap-3">
            <button onclick="cerrarModal()" class="px-4 py-2 bg-gray-200 rounded-lg">No</button>
            <button id="btnConfirmar" class="px-4 py-2 bg-red-600 text-white rounded-lg">Sí, cancelar</button>
        </div>
    </div>
</div>

<!-- MODAL EDITAR (simple) -->
<div id="modalEditar" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-xl shadow w-96">

        <h3 class="text-xl font-semibold mb-4">Editar turno</h3>

        <form id="formEditar" method="POST" action="/turnos-pro/profiles/<?= $user_id ?>/editar-turno.php">

            <input type="hidden" name="id" id="edit_id">

            <label class="text-sm text-gray-700">Fecha</label>
            <input type="date" name="date" id="edit_date"
                   class="w-full p-3 border rounded-lg bg-gray-50 mb-3">

            <label class="text-sm text-gray-700">Hora</label>
            <input type="time" name="time" id="edit_time"
                   class="w-full p-3 border rounded-lg bg-gray-50 mb-3">

            <label class="text-sm text-gray-700">Estado</label>
            <select name="status" id="edit_status"
                    class="w-full p-3 border rounded-lg bg-gray-50 mb-3">
                <option value="pending">Pendiente</option>
                <option value="confirmed">Confirmado</option>
                <option value="attended">Atendido</option>
            </select>

            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modalEditar').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 rounded-lg">
                    Cerrar
                </button>

                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                    Guardar cambios
                </button>
            </div>

        </form>

    </div>
</div>

<script>
let turnoAEliminar = null;

function confirmarCancelacion(id) {
    turnoAEliminar = id;
    document.getElementById('modalConfirmar').classList.remove('hidden');
}

function cerrarModal() {
    document.getElementById('modalConfirmar').classList.add('hidden');
}

document.getElementById('btnConfirmar').onclick = function() {
    fetch('/turnos-pro/profiles/<?= $user_id ?>/cancelar-turno-ajax.php?id=' + turnoAEliminar)
        .then(() => {
            document.getElementById('turno-' + turnoAEliminar).remove();
            cerrarModal();
        });
};

function abrirEditarTurno(id, fecha, hora, estado) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_date').value = fecha;
    document.getElementById('edit_time').value = hora;
    document.getElementById('edit_status').value = estado;

    document.getElementById('modalEditar').classList.remove('hidden');
}
</script>

</body>
</html>