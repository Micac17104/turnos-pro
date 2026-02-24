<?php
require __DIR__ . '/../pro/includes/db.php'; // conexión REAL

$token = $_GET['token'] ?? '';

$stmt = $pdo->prepare("
    SELECT * FROM password_resets
    WHERE token = ? AND expires_at > NOW()
");
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    die("Enlace inválido o vencido.");
}

if ($_POST) {

    if ($_POST['password'] !== $_POST['password2']) {
        $error = "Las contraseñas no coinciden.";
    } else {

        $email = $reset['email'];
        $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Actualizar en users
        $stmt = $pdo->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->execute([$hash, $email]);

        // Actualizar en center_staff
        $stmt = $pdo->prepare("UPDATE center_staff SET password=? WHERE email=?");
        $stmt->execute([$hash, $email]);

        // Borrar token
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email=?");
        $stmt->execute([$email]);

        header("Location: login.php?reset=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer contraseña - TurnosPro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Estilos propios -->
    <link rel="stylesheet" href="/pro/assets/css/app.css">
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md border border-slate-200">

        <h2 class="text-2xl font-bold text-slate-900 mb-4 text-center">
            Restablecer contraseña
        </h2>

        <?php if (!empty($error)): ?>
            <p class="text-red-600 text-sm mb-4 text-center"><?= $error ?></p>
        <?php endif; ?>

        <form method="post" class="space-y-4">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nueva contraseña</label>
                <input type="password" name="password"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:outline-none"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Repetir contraseña</label>
                <input type="password" name="password2"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:outline-none"
                       required>
            </div>

            <button type="submit"
                    class="w-full bg-slate-900 text-white py-2 rounded-lg hover:bg-slate-800 transition">
                Guardar nueva contraseña
            </button>

        </form>

    </div>

</body>
</html>