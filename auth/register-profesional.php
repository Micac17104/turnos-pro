<?php
require __DIR__ . '/../pro/includes/db.php';

$errors = [];

if ($_POST) {

    // Normalizar email
    $email = trim(strtolower($_POST['email'] ?? ''));
    $name  = trim($_POST['name'] ?? '');
    $profession = trim($_POST['profession'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city  = trim($_POST['city'] ?? '');
    $dni   = trim($_POST['dni'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $accepts_insurance = isset($_POST['accepts_insurance']) ? 1 : 0;

    // Validaciones
    if ($password !== $password2) {
        $errors[] = "Las contraseñas no coinciden.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido.";
    }

    if ($name === '' || $profession === '' || $phone === '' || $city === '' || $dni === '') {
        $errors[] = "Completá todos los campos obligatorios.";
    }

    // Validar email único
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Ese email ya está registrado.";
        }
    }

    // Validar DNI único
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE dni = ?");
        $stmt->execute([$dni]);
        if ($stmt->fetch()) {
            $errors[] = "Ese DNI ya está registrado.";
        }
    }

    // Crear cuenta
    if (empty($errors)) {

        // SUSCRIPCIÓN: primer mes gratis
        $today = date('Y-m-d');
        $end   = date('Y-m-d', strtotime('+1 month'));

        /*
        ---------------------------------------------------------
        FIX CORRECTO PARA TU BASE REAL:
        Usamos SOLO las columnas que existen:
        subscription_start
        subscription_end
        is_active
        last_payment
        ---------------------------------------------------------
        */
        $stmt = $pdo->prepare("
            INSERT INTO users 
            (name, email, password, profession, phone, city, accepts_insurance, account_type,
             subscription_start, subscription_end, is_active, last_payment, dni)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'professional',
                    ?, ?, 1, NULL, ?)
        ");

        $stmt->execute([
            $name,
            $email,
            password_hash($password, PASSWORD_BCRYPT),
            $profession,
            $phone,
            $city,
            $accepts_insurance,
            $today,
            $end,
            $dni
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
    <title>Crear cuenta profesional - TurnosPro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/pro/assets/css/app.css">
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md border border-slate-200">

        <h2 class="text-2xl font-bold text-slate-900 mb-4 text-center">
            Crear cuenta profesional
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
                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre y apellido</label>
                <input name="name"
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
                <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                <input name="phone"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Ciudad</label>
                <input name="city"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg"
                       required>
            </div>

            <!-- DNI -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">DNI</label>
                <input name="dni"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Profesión</label>
                <input name="profession"
                       placeholder="Psicólogo, nutricionista, etc."
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg"
                       required>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="accepts_insurance" class="w-4 h-4">
                <label class="text-sm text-slate-700">Acepto obra social</label>
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

            <button class="w-full bg-slate-900 text-white py-2 rounded-lg hover:bg-slate-800 transition">
                Crear cuenta
            </button>

        </form>

        <p class="mt-4 text-center text-sm">
            ¿Ya tenés cuenta?
            <a href="login.php" class="text-slate-900 hover:underline">Ingresar</a>
        </p>

    </div>

</body>
</html>
