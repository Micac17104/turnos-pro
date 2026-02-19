<?php
require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';

$center_id = $_GET['center_id'] ?? null;

if (!$center_id) {
    echo "<p class='text-red-600'>Centro no especificado.</p>";
    echo "</main></div></body></html>";
    exit;
}

// Obtener profesionales del centro
$stmt = $pdo->prepare("
    SELECT id, name, profession
    FROM users
    WHERE account_type = 'professional'
      AND parent_center_id = ?
    ORDER BY name
");
$stmt->execute([$center_id]);
$profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="text-2xl font-bold text-slate-900 mb-6">Profesionales del centro</h1>

<p class="text-slate-600 mb-8">
    Elegí un profesional para continuar con la reserva.
</p>

<div class="bg-white p-6 rounded-xl shadow border">

    <?php if (empty($profesionales)): ?>
        <p class="text-slate-500">No hay profesionales cargados en este centro.</p>
    <?php endif; ?>

    <div class="space-y-3">
        <?php foreach ($profesionales as $p): ?>
            <div class="flex items-center justify-between p-4 bg-slate-50 border rounded-lg">
                <div>
                    <p class="font-medium text-slate-900">
                        <?= htmlspecialchars($p['name']) ?>
                    </p>
                    <p class="text-sm text-slate-500">
                        <?= htmlspecialchars($p['profession']) ?>
                    </p>
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