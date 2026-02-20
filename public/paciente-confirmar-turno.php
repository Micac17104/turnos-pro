<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';

$paciente_id = $_SESSION['paciente_id'];

$user_id = $_GET['user_id'] ?? null;
$fecha   = $_GET['fecha'] ?? null;
$hora    = $_GET['hora'] ?? null;

if (!$user_id || !$fecha || !$hora) {
    echo "<p class='text-red-600'>Faltan datos para confirmar el turno.</p>";
    echo "</main></div></body></html>";
    exit;
}

// Profesional
$stmt = $pdo->prepare("SELECT id, name, profession FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    echo "<p class='text-red-600'>Profesional no encontrado.</p>";
    echo "</main></div></body></html>";
    exit;
}
?>

<h1 class="text-2xl font-bold text-slate-900 mb-2">
    Confirmar turno
</h1>

<p class="text-slate-600 mb-6">
    Estás a punto de reservar un turno con <strong><?= h($pro['name']) ?></strong>.
</p>

<div class="bg-white p-8 rounded-xl shadow border max-w-xl">

    <div class="space-y-4 mb-8">

        <div class="p-4 bg-slate-50 border rounded-lg">
            <p class="text-sm text-slate-500">Profesional</p>
            <p class="font-semibold text-slate-900"><?= h($pro['name']) ?></p>
            <p class="text-slate-600 text-sm"><?= h($pro['profession']) ?></p>
        </div>

        <div class="p-4 bg-slate-50 border rounded-lg">
            <p class="text-sm text-slate-500">Fecha</p>
            <p class="font-semibold text-slate-900"><?= date("d/m/Y", strtotime($fecha)) ?></p>
        </div>

        <div class="p-4 bg-slate-50 border rounded-lg">
            <p class="text-sm text-slate-500">Hora</p>
            <p class="font-semibold text-slate-900"><?= substr($hora, 0, 5) ?> hs</p>
        </div>

    </div>

    <form action="/turnos-pro/public/paciente-confirmar-turno-guardar.php" method="post" class="space-y-4">

        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <input type="hidden" name="fecha" value="<?= $fecha ?>">
        <input type="hidden" name="hora" value="<?= $hora ?>">

        <button class="w-full py-3 bg-slate-900 text-white rounded-lg font-semibold hover:bg-slate-800 transition">
            Confirmar turno
        </button>

    </form>

    <a href="/turnos-pro/public/horarios.php?pro=<?= $user_id ?>&date=<?= $fecha ?>"
       class="block mt-6 text-slate-600 hover:text-slate-900 text-sm">
        ← Elegir otro horario
    </a>

</div>

<?php
// CIERRE DEL LAYOUT
echo "</main></div></body></html>";
?>