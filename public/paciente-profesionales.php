<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';

$paciente_id = $_SESSION['paciente_id'];
$center_id = $_GET['center_id'] ?? null;

// Obtener ciudad del paciente
$stmt = $pdo->prepare("SELECT city FROM clients WHERE id = ?");
$stmt->execute([$paciente_id]);
$paciente_city = $stmt->fetchColumn();

if (!$paciente_city) {
    $paciente_city = "";
}

// Obtener ciudades disponibles
$cities = $pdo->query("
    SELECT DISTINCT city 
    FROM users 
    WHERE account_type = 'professional'
      AND city IS NOT NULL 
      AND city != ''
    ORDER BY city ASC
")->fetchAll(PDO::FETCH_COLUMN);

$city = $_GET['city'] ?? $paciente_city;

if (!$city) {
    $city = $cities[0] ?? "";
}

// Si viene center_id → filtrar por centro
if ($center_id) {
    $stmt = $pdo->prepare("
        SELECT id, name, profession, city 
        FROM users
        WHERE account_type = 'professional'
          AND parent_center_id = ?
    ");
    $stmt->execute([$center_id]);
    $profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    // Filtrar por ciudad
    $stmt = $pdo->prepare("
        SELECT id, name, profession, city 
        FROM users
        WHERE account_type = 'professional'
          AND LOWER(city) = LOWER(?)
    ");
    $stmt->execute([$city]);
    $profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h1 class="text-2xl font-bold text-slate-900 mb-6">
    <?= $center_id ? "Profesionales del centro" : "Profesionales disponibles" ?>
</h1>

<?php if (!$center_id): ?>
<form method="GET" class="mb-6 relative">
    <label class="text-sm text-slate-600">Ciudad</label>

    <input 
        type="text" 
        id="city-input"
        name="city" 
        class="border rounded-lg p-2 w-full"
        placeholder="Escribí una ciudad..."
        autocomplete="off"
        value="<?= htmlspecialchars($city) ?>"
    >

    <ul id="city-suggestions" 
        class="border bg-white rounded-lg mt-1 hidden absolute z-50 w-full shadow">
    </ul>

    <button class="mt-3 px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
        Filtrar
    </button>
</form>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<?php if (empty($profesionales)): ?>

    <div class="bg-white p-6 rounded-xl shadow border col-span-2 text-center">
        <p class="text-slate-500">
            <?= $center_id ? "Este centro no tiene profesionales cargados." : "No hay profesionales en esta ciudad." ?>
        </p>
    </div>

<?php else: ?>

    <?php foreach ($profesionales as $pro): ?>
        <div class="bg-white p-6 rounded-xl shadow border">
            <p class="text-xl font-semibold text-slate-900"><?= htmlspecialchars($pro['name']) ?></p>
            <p class="text-slate-600"><?= htmlspecialchars($pro['profession']) ?></p>
            <p class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($pro['city']) ?></p>

            <a href="reservar.php?user_id=<?= $pro['id'] ?>"
               class="mt-4 inline-block px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700">
                Ver disponibilidad
            </a>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

</div>

<script>
const input = document.getElementById("city-input");
const box = document.getElementById("city-suggestions");

if (input) {
    input.addEventListener("input", async () => {
        const q = input.value.trim();

        if (q.length < 1) {
            box.innerHTML = "";
            box.classList.add("hidden");
            return;
        }

        const res = await fetch("buscar-ciudades.php?q=" + encodeURIComponent(q));
        const cities = await res.json();

        if (cities.length === 0) {
            box.innerHTML = "";
            box.classList.add("hidden");
            return;
        }

        box.innerHTML = cities
            .map(c => `<li class='p-2 hover:bg-slate-100 cursor-pointer'>${c}</li>`)
            .join("");

        box.classList.remove("hidden");

        document.querySelectorAll("#city-suggestions li").forEach(li => {
            li.addEventListener("click", () => {
                input.value = li.textContent;
                box.classList.add("hidden");
            });
        });
    });

    document.addEventListener("click", (e) => {
        if (!box.contains(e.target) && e.target !== input) {
            box.classList.add("hidden");
        }
    });
}
</script>

<?php
echo "</main></div></body></html>";
?>