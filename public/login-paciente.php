<?php
var_dump(session_save_path());
var_dump(is_writable(session_save_path()));
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Buscar paciente por email
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
    $stmt->execute([$email]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($paciente && password_verify($password, $paciente['password'])) {

        // Si el paciente tiene user_id = 0, verificar si el profesional ya lo cargó
        if ($paciente['user_id'] == 0) {

            $stmt2 = $pdo->prepare("
                SELECT * FROM clients 
                WHERE email = ? AND user_id != 0
            ");
            $stmt2->execute([$email]);
            $clienteProfesional = $stmt2->fetch(PDO::FETCH_ASSOC);

            if ($clienteProfesional) {

                // Vincular cuenta del paciente al registro del profesional
                $_SESSION['paciente_id'] = $clienteProfesional['id'];
                $_SESSION['paciente_nombre'] = $clienteProfesional['name'];

                header("Location: paciente-dashboard.php");
                exit;
            }
        }

        // Si ya estaba vinculado o no existe registro del profesional
        $_SESSION['paciente_id'] = $paciente['id'];
        $_SESSION['paciente_nombre'] = $paciente['name'];

        header("Location: paciente-dashboard.php");
        exit;

    } else {
        $mensaje = "Email o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>

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

        .link {
            display:block;
            margin-top:15px;
            text-align:center;
            color:#0ea5e9;
            text-decoration:none;
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

    <h2>Iniciar sesión</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Contraseña" required>

        <button class="btn-primary">Ingresar</button>
    </form>

    <a class="link" href="registro-paciente.php">Crear cuenta</a>

</div>

</body>
</html>