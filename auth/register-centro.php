<?php
require __DIR__ . '/../pro/includes/db.php';

$errors = [];

if ($_POST) {

    $email = trim(strtolower($_POST['email'] ?? ''));
    $name  = trim($_POST['name'] ?? '');
    $dni   = trim($_POST['dni'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($password !== $password2) {
        $errors[] = "Las contraseñas no coinciden.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido.";
    }

    if ($name === '' || $dni === '') {
        $errors[] = "Completá todos los campos obligatorios.";
    }

    // Email único
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Ese email ya está registrado.";
        }
    }

    // DNI único
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE dni = ?");
        $stmt->execute([$dni]);
        if ($stmt->fetch()) {
            $errors[] = "Ese DNI ya está registrado.";
        }
    }

    if (empty($errors)) {

        $today = date('Y-m-d');
        $end   = date('Y-m-d', strtotime('+1 month'));

        // Crear usuario tipo centro
        $stmt = $pdo->prepare("
            INSERT INTO users 
            (name, email, password, account_type, subscription_start, subscription_end, is_active, last_payment, dni)
            VALUES (?, ?, ?, 'center', ?, ?, 1, NULL, ?)
        ");

        $stmt->execute([
            $name,
            $email,
            password_hash($password, PASSWORD_BCRYPT),
            $today,
            $end,
            $dni
        ]);

        $center_id = $pdo->lastInsertId();

        // Crear administrador del centro
        $stmt2 = $pdo->prepare("
            INSERT INTO center_staff (center_id, name, email, password, role)
            VALUES (?, ?, ?, ?, 'admin')
        ");

        $stmt2->execute([
            $center_id,
            $name,
            $email,
            password_hash($password, PASSWORD_BCRYPT)
        ]);

        header("Location: login.php?registered=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear centro médico - TurnosPro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/pro/assets/css/app.css">
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md border border-slate-200">

        <h2 class="text-2xl font-bold text-slate-900 mb-4 text-center">
            Crear centro médico
        </h2>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm">
                <?php foreach ($errors as $e): ?>
                    <p><?= $e ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del centro</label>
                <input name="name"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg"
                       required>
            </div>

            <!-- DNI -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">DNI del responsable</label>
                <input name="dni"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input name="email" type="email"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
                <input name="password" type="password"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Repetir contraseña</label>
                <input name="password2" type="password"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg"
                       required>
            </div>

            <button class="w-full bg-emerald-600 text-white py-2 rounded-lg hover:bg-emerald-700 transition">
                Crear centro
            </button>

        </form>

        <p class="mt-4 text-center text-sm">
            ¿Ya tenés cuenta?
            <a href="login.php" class="text-slate-900 hover:underline">Ingresar</a>
        </p>

    </div>

</body>
</html>
