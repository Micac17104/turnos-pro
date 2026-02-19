<?php
require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';

$paciente_id = $_SESSION['paciente_id'];
$turno_id    = $_GET['id'] ?? null;

if (!$turno_id) {
    echo "<p class='text-red-600'>Turno no especificado.</p>";
    echo "</main></div></body></html>";
    exit;
}

// Obtener turno
$stmt = $pdo->prepare("
    SELECT a.*, u.name AS profesional, u.profession
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.id = ? AND a.client_id = ?
");
$stmt->execute([$turno_id, $paciente_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    echo "<p class='text-red-600'>Turno no encontrado.</p>";
    echo "</main></div></body></html>";
    exit;
}

$pro_id = $turno['user_id'];
?>

<h1 class="text-2xl font-bold text-slate-900 mb-2">
    Reprogramar turno
</h1>

<p class="text-slate-600 mb-6">
    Estás reprogramando tu turno con <strong><?= h($turno['profesional']) ?></strong>.
</p>

<div class="bg-white p-8 rounded-xl shadow border max-w-xl">

    <div class="space-y-4 mb-8">

        <div class="p-4 bg-slate-50 border rounded-lg">
            <p class="text-sm text-slate-500">Profesional</p>
            <p class="font-semibold text-slate-900"><?= h($turno['profesional']) ?></p>
            <p class="text-slate-600 text-sm"><?= h($turno['profession']) ?></p>
        </div>

        <div class="p-4 bg-slate-50 border rounded-lg">
            <p class="text-sm text-slate-500">Turno actual</p>
            <p class="font-semibold text-slate-900">
                <?= date("d/m/Y", strtotime($turno['date'])) ?> — <?= substr($turno['time'], 0, 5) ?> hs
            </p>
        </div>

    </div>

    <!-- Selección de nueva fecha -->
    <form action="/turnos-pro/public/reprogramar-turno-horarios.php" method="get" class="space-y-6">

        <input type="hidden" name="turno_id" value="<?= $turno_id ?>">
        <input type="hidden" name="pro" value="<?= $pro_id ?>">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Elegí una nueva fecha</label>
            <input type="date" name="date" required
                   class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 focus:outline-none">
        </div>

        <button class="w-full py-3 bg-slate-900 text-white rounded-lg font-semibold hover:bg-slate-800 transition">
            Ver horarios disponibles
        </button>

    </form>

    <a href="/turnos-pro/public/paciente-dashboard.php"
       class="block mt-6 text-slate-600 hover:text-slate-900 text-sm">
        ← Volver al dashboard
    </a>

</div>

<?php
// CIERRE DEL LAYOUT
echo "</main></div></body></html>";
?>