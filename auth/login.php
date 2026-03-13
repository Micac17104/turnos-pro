<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../pro/includes/db.php';

// Si ya está logueado → redirigir según tipo
if (isset($_SESSION['user_id']) && isset($_SESSION['account_type'])) {

    if ($_SESSION['account_type'] === 'professional') {
        header("Location: /pro/dashboard.php");
        exit;
    }

    if ($_SESSION['account_type'] === 'center' || $_SESSION['account_type'] === 'secretary') {
        header("Location: /centro/centro-dashboard.php");
        exit;
    }

    if ($user['account_type'] === 'admin') {
    header("Location: /admin/admin-dashboard.php");
    exit;
}
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT id, password, account_type FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        // Guardar sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['account_type'] = $user['account_type'];

        // Redirigir según tipo
        if ($user['account_type'] === 'professional') {
            header("Location: /pro/dashboard.php");
            exit;
        }

        if ($user['account_type'] === 'center' || $user['account_type'] === 'secretary') {
            header("Location: /centro/centro-dashboard.php");
            exit;
        }
    }

    $error = "Credenciales incorrectas";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - TurnosPro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">

<div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md border border-slate-200">

    <h1 class="text-2xl font-bold text-slate-900 mb-6 text-center">
        Iniciar sesión
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
            Ingresar
        </button>

    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        <a href="/auth/forgot-password.php" class="text-slate-900 hover:underline">
            ¿Olvidaste tu contraseña?
        </a>
    </p>

    <p class="mt-2 text-center text-sm text-slate-600">
        ¿No tenés cuenta?
        <a href="/auth/register-type.php" class="text-slate-900 font-medium hover:underline">
            Crear cuenta
        </a>
    </p>

</div>

</body>
</html>