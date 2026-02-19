<?php
require '../config.php';

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
<title>Restablecer contraseña</title>

<style>
body { background:#f1f5f9; font-family:Arial; display:flex; justify-content:center; align-items:center; height:100vh; }
.box { background:white; padding:40px; border-radius:20px; width:350px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.08); }
input { width:100%; padding:14px; margin:8px 0; border-radius:12px; border:1px solid #cbd5e1; }
button { width:100%; padding:14px; background:#22c55e; color:white; border:none; border-radius:12px; font-weight:600; cursor:pointer; }
button:hover { opacity:0.9; }
.error { color:#b00020; margin-bottom:10px; }
</style>

</head>
<body>

<div class="box">
    <h2>Restablecer contraseña</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="password" name="password" placeholder="Nueva contraseña" required>
        <input type="password" name="password2" placeholder="Repetir contraseña" required>
        <button type="submit">Guardar nueva contraseña</button>
    </form>
</div>

</body>
</html>