<?php
require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';

/* Aceptar pro, user_id o slug */
$pro_id = $_GET['pro'] ?? ($_GET['user_id'] ?? null);
$slug = $_GET['slug'] ?? null;

if ($pro_id) {
    $stmt = $pdo->prepare("SELECT id, name, profession FROM users WHERE id = ?");
    $stmt->execute([$pro_id]);
    $pro = $stmt->fetch(PDO::FETCH_ASSOC);

} elseif ($slug) {
    $stmt = $pdo->prepare("SELECT id, name, profession FROM users WHERE slug = ?");
    $stmt->execute([$slug]);
    $pro = $stmt->fetch(PDO::FETCH_ASSOC);

} else {
    echo "<p class='text-red-600'>Profesional no especificado.</p>";
    echo "</main></div></body></html>";
    exit;
}

if (!$pro) {
    echo "<p class='text-red-600'>Profesional no encontrado.</p>";
    echo "</main></div></body></html>";
    exit;
}

$pro_id = $pro['id'];
?>

<h1 class="text-2xl font-bold text-slate-900 mb-2">
    Reservar turno con <?= h($pro['name']) ?>
</h1>

<p class="text-slate-600 mb-8">
    <?= h($pro['profession']) ?>
</p>

<div class="bg-white p-8 rounded-xl shadow border max-w-lg">

    <form action="/turnos-pro/public/horarios.php" method="get" class="space-y-6">

        <input type="hidden" name="pro" value="<?= $pro_id ?>">

        <!-- Fecha -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Elegí una fecha</label>
            <input type="date" name="date" required
                   class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-900 focus:outline-none">
        </div>

        <button class="w-full py-3 bg-slate-900 text-white rounded-lg font-semibold hover:bg-slate-800 transition">
            Ver horarios disponibles
        </button>

    </form>

</div>

<?php
// CIERRE DEL LAYOUT
echo "</main></div></body></html>";
?>