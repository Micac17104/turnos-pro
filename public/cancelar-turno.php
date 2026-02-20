<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';

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
    echo "<p class='text-red-600'>No se encontró el turno.</p>";
    echo "</main></div></body></html>";
    exit;
}

// Cancelar turno
$stmt = $pdo->prepare("UPDATE appointments SET status='cancelled' WHERE id=?");
$stmt->execute([$turno_id]);
?>

<h1 class="text-2xl font-bold text-slate-900 mb-6">Turno cancelado</h1>

<div class="bg-white p-8 rounded-xl shadow border max-w-md">

    <p class="text-slate-700 mb-4">
        Tu turno fue cancelado correctamente.
    </p>

    <div class="p-4 bg-slate-50 border rounded-lg mb-6">
        <p class="text-sm text-slate-500">Profesional</p>
        <p class="font-semibold text-slate-900"><?= htmlspecialchars($turno['profesional']) ?></p>
        <p class="text-sm text-slate-600"><?= htmlspecialchars($turno['profession']) ?></p>

        <p class="text-sm text-slate-500 mt-4">Fecha original</p>
        <p class="font-semibold text-slate-900"><?= date("d/m/Y", strtotime($turno['date'])) ?></p>

        <p class="text-sm text-slate-500 mt-4">Hora original</p>
        <p class="font-semibold text-slate-900"><?= substr($turno['time'], 0, 5) ?> hs</p>
    </div>

    <a href="paciente-dashboard.php"
       class="block w-full text-center py-3 bg-slate-900 text-white rounded-lg font-semibold hover:bg-slate-800 transition">
        Volver al panel
    </a>

</div>

<?php
echo "</main></div></body></html>";
?>