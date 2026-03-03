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

// Obtener todas las ciudades donde haya centros (tabla users)
$cities = $pdo->query("
    SELECT DISTINCT city 
    FROM users 
    WHERE account_type = 'center'
      AND city IS NOT NULL
      AND city != ''
    ORDER BY city ASC
")->fetchAll(PDO::FETCH_COLUMN);

// Ciudad seleccionada (si no elige, usamos la del paciente)
$city = $_GET['city'] ?? $paciente_city;

// Si no hay ciudad seleccionada, usar la primera disponible
if (!$city) {
    $city = $cities[0] ?? "";
}

// Obtener centros filtrados (case-insensitive)
$stmt = $pdo->prepare("
    SELECT id, name, city, address
    FROM users
    WHERE account_type = 'center'
      AND LOWER(city) = LOWER(?)
");
$stmt->execute([$city]);
$centros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="text-2xl font-bold text-slate-900 mb-6">Centros médicos</h1>

<form method="GET" class="mb-6 flex items-center gap-3">
    <label class="text-sm text-slate-600">Ciudad</label>
    <select name="city" class="border rounded-lg p-2">
        <?php foreach ($cities as $c): ?>
            <option value="<?= $c ?>" <?= strtolower($c) === strtolower($city) ? 'selected' : '' ?>>
                <?= $c ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
        Filtrar
    </button>
</form>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<?php if (empty($centros)): ?>

    <div class="bg-white p-6 rounded-xl shadow border col-span-2 text-center">
        <p class="text-slate-500">No hay centros médicos en esta ciudad.</p>
    </div>

<?php else: ?>

    <?php foreach ($centros as $c): ?>
        <div class="bg-white p-6 rounded-xl shadow border">
            <p class="text-xl font-semibold text-slate-900"><?= htmlspecialchars($c['name']) ?></p>

            <?php if (!empty($c['address'])): ?>
                <p class="text-slate-600"><?= htmlspecialchars($c['address']) ?></p>
            <?php endif; ?>

            <p class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($c['city']) ?></p>

            <a href="paciente-profesionales.php?center_id=<?= $c['id'] ?>"
               class="mt-4 inline-block px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700">
                Ver profesionales
            </a>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

</div>

<?php
echo "</main></div></body></html>";
?>