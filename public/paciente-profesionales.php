<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';

$paciente_id = $_SESSION['paciente_id'];

// Obtener ciudad del paciente
$stmt = $pdo->prepare("SELECT city FROM clients WHERE id = ?");
$stmt->execute([$paciente_id]);
$paciente_city = $stmt->fetchColumn();

// Si el paciente no tiene ciudad, evitar errores
if (!$paciente_city) {
    $paciente_city = "";
}

// Obtener todas las ciudades disponibles (CORREGIDO: account_type)
$cities = $pdo->query("
    SELECT DISTINCT city 
    FROM users 
    WHERE account_type = 'professional' AND city IS NOT NULL AND city != ''
    ORDER BY city ASC
")->fetchAll(PDO::FETCH_COLUMN);

// Ciudad seleccionada (si no elige, usamos la del paciente)
$city = $_GET['city'] ?? $paciente_city;

// Si no hay ciudad seleccionada, evitar errores
if (!$city) {
    $city = $cities[0] ?? ""; // primera ciudad disponible
}

// Obtener profesionales filtrados (CORRECTO)
$stmt = $pdo->prepare("
    SELECT id, name, profession, city 
    FROM users
    WHERE account_type = 'professional'
    AND city = ?
");
$stmt->execute([$city]);
$profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="text-2xl font-bold text-slate-900 mb-6">Profesionales disponibles</h1>

<form method="GET" class="mb-6 flex items-center gap-3">
    <label class="text-sm text-slate-600">Ciudad</label>
    <select name="city" class="border rounded-lg p-2">
        <?php foreach ($cities as $c): ?>
            <option value="<?= $c ?>" <?= $c === $city ? 'selected' : '' ?>>
                <?= $c ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
        Filtrar
    </button>
</form>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<?php if (empty($profesionales)): ?>

    <div class="bg-white p-6 rounded-xl shadow border col-span-2 text-center">
        <p class="text-slate-500">No hay profesionales en esta ciudad.</p>
    </div>

<?php else: ?>

    <?php foreach ($profesionales as $pro): ?>
        <div class="bg-white p-6 rounded-xl shadow border">
            <p class="text-xl font-semibold text-slate-900"><?= htmlspecialchars($pro['name']) ?></p>
            <p class="text-slate-600"><?= htmlspecialchars($pro['profession']) ?></p>
            <p class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($pro['city']) ?></p>

            <!-- CORREGIDO: usar user_id en lugar de pro_id -->
            <a href="/turnos-pro/public/reservar.php?user_id=<?= $pro['id'] ?>"
               class="mt-4 inline-block px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700">
                Ver disponibilidad
            </a>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

</div>

<?php
echo "</main></div></body></html>";
?>