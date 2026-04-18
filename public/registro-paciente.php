<?php
// --- FIX DEFINITIVO PARA RAILWAY ---
$path = __DIR__ . '/../sessions';

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

if (!is_writable($path)) {
    chmod($path, 0777);
}

session_save_path($path);
session_start();
// -----------------------------------

require __DIR__ . '/../config.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre   = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $dni      = $_POST['dni'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Verificar si ya existe una cuenta creada por el paciente
    $stmt = $pdo->prepare("SELECT id FROM clients WHERE email = ? AND user_id != 0");
    $stmt->execute([$email]);
    $cuentaExistente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cuentaExistente) {
        $mensaje = "Ya existe una cuenta creada con ese email.";
    } else {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        /*
        ---------------------------------------------------------
         1️⃣ Buscar si el centro ya creó a este paciente
            por DNI o email
        ---------------------------------------------------------
        */
        $stmt = $pdo->prepare("
            SELECT * FROM clients 
            WHERE dni = ? OR email = ?
            LIMIT 1
        ");
        $stmt->execute([$dni, $email]);
        $clienteCentro = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($clienteCentro) {

            /*
            ---------------------------------------------------------
             2️⃣ Actualizar ese registro y vincularlo
            ---------------------------------------------------------
            */
            $stmt = $pdo->prepare("
                UPDATE clients
                SET user_id = ?, name = ?, phone = ?, dni = ?, email = ?, password = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $clienteCentro['id'], // user_id = id del cliente
                $nombre,
                $telefono,
                $dni,
                $email,
                $hash,
                $clienteCentro['id']
            ]);

            $_SESSION['paciente_id'] = $clienteCentro['id'];
            $_SESSION['paciente_nombre'] = $nombre;

        } else {

            /*
            ---------------------------------------------------------
             3️⃣ Crear un nuevo paciente (sin centro asignado)
            ---------------------------------------------------------
            */
            $stmt = $pdo->prepare("
                INSERT INTO clients (user_id, name, phone, dni, email, password)
                VALUES (0, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nombre, $telefono, $dni, $email, $hash]);

            $_SESSION['paciente_id'] = $pdo->lastInsertId();
            $_SESSION['paciente_nombre'] = $nombre;
        }

        header("Location: paciente-dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear cuenta</title>

    <style>
        body { background:#f5f5f5; font-family:Arial; }
        .container { max-width:400px; margin:60px auto; background:white; padding:30px; border-radius:14px; }
        h2 { text-align:center; color:#0f172a; margin-bottom:20px; }

        input {
            width:100%;
            padding:12px;
            margin-bottom:15px;
            border-radius:10px;
            border:1px solid #cbd5e1;
            font-size:16px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            padding: 12px;
            border-radius: 999px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            display:block;
            text-align:center;
            border:none;
            cursor:pointer;
            width:100%;
        }

        .error {
            background:#fee2e2;
            padding:10px;
            border-radius:8px;
            color:#b91c1c;
            margin-bottom:15px;
            text-align:center;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Crear cuenta</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="nombre" placeholder="Nombre completo" required>
        <input type="text" name="telefono" placeholder="Teléfono" required>

        <input type="text" name="dni" placeholder="DNI" required>

        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Contraseña" required>

        <button class="btn-primary">Registrarme</button>
    </form>

</div>

</body>
</html>
