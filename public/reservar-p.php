<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../pro/includes/helpers.php';

/* Aceptar pro, user_id o slug */
$pro_id = $_GET['pro'] ?? ($_GET['user_id'] ?? null);
$slug = $_GET['slug'] ?? null;

if ($pro_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$pro_id]);
    $pro = $stmt->fetch(PDO::FETCH_ASSOC);

} elseif ($slug) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE slug = ?");
    $stmt->execute([$slug]);
    $pro = $stmt->fetch(PDO::FETCH_ASSOC);

} else {
    die("Perfil no encontrado.");
}

if (!$pro) {
    die("Profesional no encontrado.");
}

$pro_id = $pro['id'];

/* Obtener horarios del profesional */
$stmt = $pdo->prepare("
    SELECT day_of_week, start_time, end_time, interval_minutes
    FROM professional_schedule
    WHERE user_id = ?
");
$stmt->execute([$pro_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Días disponibles */
$diasDisponibles = [];
foreach ($rows as $r) {
    $diasDisponibles[] = (int)$r['day_of_week'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar turno - <?= h($pro['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50">

<div class="max-w-3xl mx-auto py-10 px-4">

    <!-- HEADER -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex items-center gap-4">
        <?php if (!empty($pro['profile_image'])): ?>
            <img src="uploads/<?= h($pro['profile_image']) ?>"
                 class="w-16 h-16 rounded-full object-cover border">
        <?php else: ?>
            <div class="w-16 h-16 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 text-xl">
                <?= strtoupper(substr($pro['name'], 0, 1)) ?>
            </div>
        <?php endif; ?>

        <div>
            <h1 class="text-2xl font-semibold text-slate-900"><?= h($pro['name']) ?></h1>
            <p class="text-slate-600"><?= h($pro['profession']) ?></p>
        </div>
    </div>

    <!-- PASO 1: CALENDARIO -->
    <div id="step1" class="mt-8 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <h2 class="text-xl font-semibold mb-4">Elegí un día</h2>

        <div class="flex justify-between mb-4">
            <button id="prevMonth" class="px-3 py-1 bg-slate-200 rounded">←</button>
            <h3 id="calendarTitle" class="font-medium"></h3>
            <button id="nextMonth" class="px-3 py-1 bg-slate-200 rounded">→</button>
        </div>

        <div id="calendar" class="grid grid-cols-7 gap-2 text-center text-sm"></div>
    </div>

    <!-- PASO 2: HORARIOS -->
    <div id="step2" class="hidden mt-8 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <h2 class="text-xl font-semibold mb-4">Elegí un horario</h2>
        <div id="horarios" class="grid grid-cols-2 md:grid-cols-4 gap-3"></div>
        <button onclick="backToStep1()" class="mt-4 text-blue-600 text-sm">← Volver</button>
    </div>

    <!-- PASO 3: FORMULARIO -->
    <div id="step3" class="hidden mt-8 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <h2 class="text-xl font-semibold mb-4">Tus datos</h2>

        <form id="formReserva">
            <input type="hidden" name="user_id" value="<?= $pro_id ?>">
            <input type="hidden" name="date" id="selectedDate">
            <input type="hidden" name="time" id="selectedTime">

            <label class="block mb-2 text-sm">Nombre</label>
            <input name="name" required class="w-full mb-4 p-2 border rounded">

            <label class="block mb-2 text-sm">Teléfono</label>
            <input name="phone" required class="w-full mb-4 p-2 border rounded">

            <label class="block mb-2 text-sm">Email (opcional)</label>
            <input name="email" class="w-full mb-4 p-2 border rounded">

            <button class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700">
                Confirmar turno
            </button>
        </form>

        <button onclick="backToStep2()" class="mt-4 text-blue-600 text-sm">← Volver</button>
    </div>

    <!-- PASO 4: CONFIRMACIÓN -->
    <div id="step4" class="hidden mt-8 bg-white p-6 rounded-xl shadow-sm border border-slate-200 text-center">
        <h2 class="text-xl font-semibold mb-4">¡Turno reservado!</h2>
        <p class="text-slate-700">El profesional se contactará con vos por WhatsApp.</p>
    </div>

</div>

<script>
const diasDisponibles = <?= json_encode($diasDisponibles) ?>;
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
const proId = <?= $pro_id ?>;

function loadCalendar() {
    const calendar = document.getElementById("calendar");
    const title = document.getElementById("calendarTitle");

    const date = new Date(currentYear, currentMonth, 1);
    const monthName = date.toLocaleString("es-ES", { month: "long" });

    title.textContent = `${monthName} ${currentYear}`;
    calendar.innerHTML = "";

    const firstDay = date.getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

    for (let i = 0; i < firstDay; i++) {
        calendar.innerHTML += `<div></div>`;
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const dateObj = new Date(currentYear, currentMonth, day);
        const dayOfWeek = dateObj.getDay();
        const fullDate = `${currentYear}-${String(currentMonth + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;

        if (!diasDisponibles.includes(dayOfWeek)) {
            calendar.innerHTML += `
                <div class="p-2 rounded bg-slate-200 text-slate-400 cursor-not-allowed">
                    ${day}
                </div>`;
            continue;
        }

        calendar.innerHTML += `
            <button onclick="selectDay('${fullDate}')"
                class="p-2 rounded bg-blue-100 hover:bg-blue-300 transition">
                ${day}
            </button>`;
    }
}

function selectDay(date) {
    document.getElementById("step1").classList.add("hidden");
    document.getElementById("step2").classList.remove("hidden");

    fetch(`p/api/horarios.php?user_id=${proId}&date=${date}`)
        .then(res => res.json())
        .then(data => {
            const cont = document.getElementById("horarios");
            cont.innerHTML = "";

            data.available.forEach(h => {
                cont.innerHTML += `
                    <button onclick="selectTime('${date}','${h}')"
                        class="p-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        ${h}
                    </button>`;
            });

            data.occupied.forEach(h => {
                cont.innerHTML += `
                    <div class="p-3 bg-slate-300 text-slate-500 rounded-lg line-through cursor-not-allowed">
                        ${h}
                    </div>`;
            });
        });
}

function selectTime(date, time) {
    document.getElementById("selectedDate").value = date;
    document.getElementById("selectedTime").value = time;

    document.getElementById("step2").classList.add("hidden");
    document.getElementById("step3").classList.remove("hidden");
}

function backToStep1() {
    document.getElementById("step2").classList.add("hidden");
    document.getElementById("step1").classList.remove("hidden");
}

function backToStep2() {
    document.getElementById("step3").classList.add("hidden");
    document.getElementById("step2").classList.remove("hidden");
}

document.getElementById("formReserva").addEventListener("submit", e => {
    e.preventDefault();

    fetch("p/api/reservar-turno.php", {
        method: "POST",
        body: new FormData(e.target)
    })
    .then(res => res.json())
    .then(() => {
        document.getElementById("step3").classList.add("hidden");
        document.getElementById("step4").classList.remove("hidden");
    });
});

document.getElementById("prevMonth").onclick = () => {
    currentMonth--;
    if (currentMonth < 0) { currentMonth = 11; currentYear--; }
    loadCalendar();
};

document.getElementById("nextMonth").onclick = () => {
    currentMonth++;
    if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    loadCalendar();
};

loadCalendar();
</script>

</body>
</html>