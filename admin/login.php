<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../pro/includes/db.php';

// Si ya está logueado como admin → redirigir
if (isset($_SESSION['user_id']) && $_SESSION['account_type'] === 'admin') {
    header("Location: /admin/admin-dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT id, password, account_type FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['account_type'] === 'admin' && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['account_type'] = 'admin';

        header("Location: /admin/admin-dashboard.php");
        exit;
    }

    $error = "Credenciales incorrectas";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin - Iniciar sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">

<div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md border border-slate-200">

    <h1 class="text-2xl font-bold text-slate-900 mb-6 text-center">
        Panel Administrador
    </h1>

    <?php if (!empty($error)): ?>
        <p class="text-red-600 text-sm mb-4 text-center"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input type="email" name="email"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
            <input type="password" name="password"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg"
                   required>
        </div>

        <button type="submit"
                class="w-full bg-slate-900 text-white py-2 rounded-lg hover:bg-slate-800 transition">
            Ingresar al panel admin
        </button>

    </form>

</div>

</body>
</html>