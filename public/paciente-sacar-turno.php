<?php
require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';

// Centros médicos
$stmt = $pdo->query("
    SELECT id, name
    FROM users
    WHERE account_type = 'center'
    ORDER BY name
");
$centros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Profesionales independientes
$stmt = $pdo->query("
    SELECT id, name, profession
    FROM users
    WHERE account_type = 'professional'
      AND parent_center_id IS NULL
    ORDER BY name
");
$independientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="text-2xl font-bold text-slate-900 mb-6">Sacar turno</h1>

<p class="text-slate-600 mb-8">
    Elegí un centro médico o un profesional independiente para continuar con la reserva.
</p>

<!-- CENTROS MÉDICOS -->
<div class="bg-white p-6 rounded-xl shadow border mb-10">
    <h2 class="text-xl font-semibold mb-4">Centros médicos</h2>

    <?php if (empty($centros)): ?>
        <p class="text-slate-500">No hay centros cargados.</p>
    <?php endif; ?>

    <div class="space-y-3">
        <?php foreach ($centros as $c): ?>
            <div class="flex items-center justify-between p-4 bg-slate-50 border rounded-lg">
                <div class="font-medium text-slate-900">
                    <?= htmlspecialchars($c['name']) ?>
                </div>

                <a href="/turnos-pro/public/paciente-elegir-profesional.php?center_id=<?= $c['id'] ?>"
                   class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm hover:bg-slate-800">
                    Ver profesionales
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- PROFESIONALES INDEPENDIENTES -->
<div class="bg-white p-6 rounded-xl shadow border">
    <h2 class="text-xl font-semibold mb-4">Profesionales independientes</h2>

    <?php if (empty($independientes)): ?>
        <p class="text-slate-500">No hay profesionales independientes cargados.</p>
    <?php endif; ?>

    <div class="space-y-3">
        <?php foreach ($independientes as $p): ?>
            <div class="flex items-center justify-between p-4 bg-slate-50 border rounded-lg">
                <div>
                    <p class="font-medium text-slate-900"><?= htmlspecialchars($p['name']) ?></p>
                    <p class="text-sm text-slate-500"><?= htmlspecialchars($p['profession']) ?></p>
                </div>

                <a href="/turnos-pro/public/reservar.php?pro=<?= $p['id'] ?>"
                   class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm hover:bg-slate-800">
                    Sacar turno
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
// CIERRE DEL LAYOUT
echo "</main></div></body></html>";
?>