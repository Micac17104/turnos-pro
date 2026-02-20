<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';

// Validar sesión del paciente
if (!isset($_SESSION['paciente_id'])) {
    header("Location: login-paciente.php");
    exit;
}

$paciente_id = $_SESSION['paciente_id'];

// Obtener datos del paciente
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$paciente_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

// Guardar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name  = $_POST['name']  ?? null;
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $city  = $_POST['city']  ?? null;

    // Construcción dinámica del SQL si cambia contraseña
    $password_sql = "";
    $params = [$name, $email, $phone, $city];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_sql = ", password = ?";
        $params[] = $password;
    }

    $params[] = $paciente_id;

    $stmt = $pdo->prepare("
        UPDATE clients 
        SET name = ?, email = ?, phone = ?, city = ?
        $password_sql
        WHERE id = ?
    ");
    $stmt->execute($params);

    header("Location: paciente-perfil.php?ok=1");
    exit;
}
?>

<h1 class="text-2xl font-bold text-slate-900 mb-6">Mi perfil</h1>

<?php if (isset($_GET['ok'])): ?>
    <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">
        Datos actualizados correctamente.
    </div>
<?php endif; ?>

<form method="POST" class="bg-white p-8 rounded-xl shadow border max-w-xl">

    <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo</label>
    <input type="text" name="name" required
           value="<?= htmlspecialchars($paciente['name']) ?>"
           class="w-full border rounded-lg p-2 mb-4">

    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
    <input type="email" name="email" required
           value="<?= htmlspecialchars($paciente['email']) ?>"
           class="w-full border rounded-lg p-2 mb-4">

    <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
    <input type="text" name="phone"
           value="<?= htmlspecialchars($paciente['phone']) ?>"
           class="w-full border rounded-lg p-2 mb-4">

    <label class="block text-sm font-medium text-slate-700 mb-1">Ciudad</label>
    <input type="text" name="city"
           value="<?= htmlspecialchars($paciente['city'] ?? '') ?>"
           class="w-full border rounded-lg p-2 mb-6">

    <label class="block text-sm font-medium text-slate-700 mb-1">Nueva contraseña (opcional)</label>
    <input type="password" name="password"
           placeholder="Dejar vacío para no cambiar"
           class="w-full border rounded-lg p-2 mb-6">

    <button class="w-full py-3 bg-sky-600 text-white rounded-lg font-semibold hover:bg-sky-700 transition">
        Guardar cambios
    </button>

</form>

<?php
echo "</main></div></body></html>";
?>