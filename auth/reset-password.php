<?php
require __DIR__ . '/../pro/includes/db.php';

$token = $_GET['token'] ?? '';

$stmt = $pdo->prepare("
    SELECT email, expires_at 
    FROM password_resets 
    WHERE token = ?
");
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset || strtotime($reset['expires_at']) < time()) {
    die("Enlace inválido o vencido.");
}

$error = "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer contraseña - TurnosPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">

<div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md border border-slate-200">

    <h2 class="text-2xl font-bold text-slate-900 mb-4 text-center">
        Restablecer contraseña
    </h2>

    <?php if (!empty($_GET['error'])): ?>
        <p class="text-red-600 text-sm mb-4 text-center">Las contraseñas no coinciden.</p>
    <?php endif; ?>

    <form method="post" action="reset-save.php" class="space-y-4">

        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <div>
            <label class="block text-sm font-medium">Nueva contraseña</label>
            <input type="password" name="password"
                   class="w-full px-3 py-2 border rounded-lg" required>
        </div>

        <div>
            <label class="block text-sm font-medium">Repetir contraseña</label>
            <input type="password" name="password2"
                   class="w-full px-3 py-2 border rounded-lg" required>
        </div>

        <button class="w-full bg-slate-900 text-white py-2 rounded-lg">
            Guardar nueva contraseña
        </button>

    </form>

</div>

</body>
</html>