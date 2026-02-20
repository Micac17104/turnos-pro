<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';

// VALIDAR SESIÓN ANTES DE CARGAR EL LAYOUT
if (!isset($_SESSION['paciente_id'])) {
    header("Location: login-paciente.php");
    exit;
}

require __DIR__ . '/paciente-layout.php';

$paciente_id = $_SESSION['paciente_id'];

// Obtener próximos turnos
$stmt = $pdo->prepare("
    SELECT a.*, u.name AS profesional
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.client_id = ?
      AND a.status != 'cancelled'
      AND a.date >= CURDATE()
    ORDER BY a.date ASC, a.time ASC
    LIMIT 5
");
$stmt->execute([$paciente_id]);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$hoy = date("Y-m-d");
?>

<!-- CONTENIDO DEL DASHBOARD -->
<h1 class="text-2xl font-bold text-slate-900 mb-6">
    Hola <?= htmlspecialchars($_SESSION['paciente_nombre']) ?> 👋
</h1>

<p class="text-slate-600 mb-8">
    Este es tu panel personal. Desde aquí podés ver tus próximos turnos, acceder a tu historia clínica y gestionar tu perfil.
</p>

<!-- ACCIONES RÁPIDAS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

    <a href="paciente-profesionales.php"
       class="bg-white p-6 rounded-xl shadow border hover:shadow-md transition">
        <div class="text-3xl mb-3">👨‍⚕️</div>
        <h3 class="font-semibold text-lg">Buscar profesionales</h3>
        <p class="text-slate-500 text-sm mt-1">Encontrá especialistas por ciudad</p>
    </a>

    <a href="paciente-centros.php"
       class="bg-white p-6 rounded-xl shadow border hover:shadow-md transition">
        <div class="text-3xl mb-3">🏥</div>
        <h3 class="font-semibold text-lg">Centros médicos</h3>
        <p class="text-slate-500 text-sm mt-1">Clínicas y centros cerca tuyo</p>
    </a>

    <a href="paciente-sacar-turno.php"
       class="bg-white p-6 rounded-xl shadow border hover:shadow-md transition">
        <div class="text-3xl mb-3">📅</div>
        <h3 class="font-semibold text-lg">Sacar un turno</h3>
        <p class="text-slate-500 text-sm mt-1">Elegí profesional, fecha y horario</p>
    </a>

    <a href="paciente-historia.php"
       class="bg-white p-6 rounded-xl shadow border hover:shadow-md transition">
        <div class="text-3xl mb-3">📄</div>
        <h3 class="font-semibold text-lg">Historia clínica</h3>
        <p class="text-slate-500 text-sm mt-1">Consultá tus evoluciones</p>
    </a>

    <a href="paciente-perfil.php"
       class="bg-white p-6 rounded-xl shadow border hover:shadow-md transition">
        <div class="text-3xl mb-3">👤</div>
        <h3 class="font-semibold text-lg">Mi perfil</h3>
        <p class="text-slate-500 text-sm mt-1">Actualizá tus datos personales</p>
    </a>

</div>

<!-- PRÓXIMOS TURNOS -->
<div class="bg-white p-6 rounded-xl shadow border mb-10">
    <h2 class="text-xl font-semibold mb-4">Tus próximos turnos</h2>

    <?php if (count($turnos) === 0): ?>
        <p class="text-slate-500">No tenés turnos reservados.</p>
    <?php endif; ?>

    <div class="space-y-4">
        <?php foreach ($turnos as $t): ?>
            <div class="p-4 border rounded-lg bg-slate-50">
                <p class="font-semibold text-slate-900">
                    <?= htmlspecialchars($t['profesional']) ?>
                </p>

                <p class="text-sm text-slate-600 mt-1">
                    <strong>Fecha:</strong> <?= date("d/m/Y", strtotime($t['date'])) ?>
                </p>

                <p class="text-sm text-slate-600">
                    <strong>Hora:</strong> <?= substr($t['time'], 0, 5) ?> hs
                </p>

                <p class="text-sm text-slate-600">
                    <strong>Estado:</strong> <?= htmlspecialchars($t['status']) ?>
                </p>

                <?php if ($t['date'] >= $hoy): ?>
                    <div class="mt-3 flex gap-3">
                        <a href="cancelar-turno.php?id=<?= $t['id'] ?>"
                           class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                            Cancelar
                        </a>

                        <a href="reprogramar-turno.php?id=<?= $t['id'] ?>"
                           class="px-3 py-2 bg-slate-900 text-white rounded-lg text-sm hover:bg-slate-800">
                            Reprogramar
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
echo "</main></div></body></html>";
?>