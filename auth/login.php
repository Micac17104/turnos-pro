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

require '../config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1) Buscar en tabla users (profesionales + centros)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['account_type'] = $user['account_type'];
        $_SESSION['parent_center_id'] = $user['parent_center_id'];

        // Redirección según tipo de cuenta
        if ($user['account_type'] === 'center') {
            header("Location: ../centro/centro-dashboard.php");
            exit;
        } else {
            header("Location: ../pro/agenda.php");
            exit;
        }
    }

    // 2) Buscar en tabla center_staff (secretarias)
    $stmt2 = $pdo->prepare("SELECT * FROM center_staff WHERE email = ?");
    $stmt2->execute([$email]);
    $staff = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($staff && password_verify($password, $staff['password'])) {

        $_SESSION['staff_id'] = $staff['id'];
        $_SESSION['center_id'] = $staff['center_id'];
        $_SESSION['staff_role'] = $staff['role'];
        $_SESSION['user_name'] = $staff['name'];

        header("Location: ../staff/staff-dashboard.php");
        exit;
    }

    // Si no coincide con nada
    $errors[] = "Email o contraseña incorrectos.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ingresar</title>

<style>
body {
    margin:0; padding:0;
    background:#f1f5f9;
    font-family:Arial, sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}
.container {
    background:white;
    padding:40px;
    border-radius:20px;
    width:350px;
    text-align:center;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}
h1 {
    margin-bottom:20px;
    font-size:26px;
    font-weight:700;
}
input {
    width:100%;
    padding:14px;
    margin:8px 0;
    border-radius:12px;
    border:1px solid #cbd5e1;
}
button {
    width:100%;
    padding:14px;
    margin-top:10px;
    background:#0ea5e9;
    color:white;
    border:none;
    border-radius:12px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}
button:hover { opacity:0.9; }
.error { color:#b00020; margin-bottom:10px; }
.link {
    margin-top:12px;
    font-size:14px;
}
.link a { color:#0ea5e9; text-decoration:none; }
</style>

</head>
<body>

<div class="container">

    <h1>Ingresar</h1>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Entrar</button>
    </form>

    <p class="link">
        <a href="forgot-password.php">¿Olvidaste tu contraseña?</a>
    </p>

    <p class="link">
        ¿No tenés cuenta? <a href="register-type.php">Crear cuenta</a>
    </p>

</div>

</body>
</html>