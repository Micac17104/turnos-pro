<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';

$tipo = $_GET['tipo'] ?? 'centros';
$city = $_GET['city'] ?? null;
$profession = $_GET['profession'] ?? '';

if (!$city) {
    die("Ciudad no seleccionada.");
}

$params = [$city];

if ($tipo === 'centros') {

    // Centros con profesionales de esa especialidad
    if ($profession !== '') {
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.id, u.name, u.address
            FROM users u
            JOIN users p ON p.parent_center_id = u.id
            WHERE u.account_type = 'center'
              AND u.city = ?
              AND u.active = 1
              AND p.profession = ?
        ");
        $params[] = $profession;

    } else {
        $stmt = $pdo->prepare("
            SELECT id, name, address
            FROM users
            WHERE account_type = 'center'
              AND city = ?
              AND active = 1
            ORDER BY name
        ");
    }

    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $titulo = "Centros médicos en " . htmlspecialchars($city);

} else {

    if ($profession !== '') {
        $stmt = $pdo->prepare("
            SELECT id, name, profession
            FROM users
            WHERE account_type = 'professional'
              AND parent_center_id IS NULL
              AND city = ?
              AND active = 1
              AND profession = ?
            ORDER BY name
        ");
        $params[] = $profession;

    } else {
        $stmt = $pdo->prepare("
            SELECT id, name, profession
            FROM users
            WHERE account_type = 'professional'
              AND parent_center_id IS NULL
              AND city = ?
              AND active = 1
            ORDER BY name
        ");
    }

    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $titulo = "Profesionales independientes en " . htmlspecialchars($city);
}
?>

<h1 class="text-2xl font-bold text-slate-900 mb-6"><?= $titulo ?></h1>

<div class="space-y-4">

<?php if (empty($resultados)): ?>
    <p class="text-slate-500">No se encontraron resultados en esta zona con esa especialidad.</p>
<?php endif; ?>

<?php foreach ($resultados as $r): ?>
    <div class="p-4 bg-white border rounded-xl shadow flex items-center justify-between">

        <div>
            <p class="font-semibold text-slate-900"><?= htmlspecialchars($r['name']) ?></p>

            <?php if ($tipo === 'centros'): ?>
                <p class="text-sm text-slate-500"><?= htmlspecialchars($r['address']) ?></p>
            <?php else: ?>
                <p class="text-sm text-slate-500"><?= htmlspecialchars($r['profession']) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($tipo === 'centros'): ?>
            <a href="paciente-elegir-profesional.php?center_id=<?= $r['id'] ?>"
               class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm hover:bg-slate-800">
                Ver profesionales
            </a>
        <?php else: ?>
            <a href="reservar.php?pro=<?= $r['id'] ?>"
               class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm hover:bg-slate-800">
                Sacar turno
            </a>
        <?php endif; ?>

    </div>
<?php endforeach; ?>

</div>

<?php
echo "</main></div></body></html>";
?>