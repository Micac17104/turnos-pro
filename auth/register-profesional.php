<?php
require '../config.php';

$errors = [];

if ($_POST) {

    if ($_POST['password'] !== $_POST['password2']) {
        $errors[] = "Las contraseñas no coinciden.";
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, profession, account_type, parent_center_id)
            VALUES (?, ?, ?, ?, 'professional', NULL)
        ");

        try {
            $stmt->execute([
                $_POST['name'],
                $_POST['email'],
                password_hash($_POST['password'], PASSWORD_BCRYPT),
                $_POST['profession']
            ]);

            header("Location: login.php?registered=1");
            exit;

        } catch (PDOException $e) {
            $errors[] = "Ese email ya está registrado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear cuenta profesional</title>

<style>
body { background:#f1f5f9; font-family:Arial; display:flex; justify-content:center; align-items:center; height:100vh; }
.box { background:white; padding:40px; border-radius:20px; width:350px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.08); }
input { width:100%; padding:12px; margin:8px 0; border-radius:10px; border:1px solid #ddd; }
button { width:100%; padding:14px; background:#0ea5e9; color:white; border:none; border-radius:12px; font-weight:600; }
.error { color:#b00020; margin-bottom:10px; }
</style>

</head>
<body>

<div class="box">
    <h2>Crear cuenta profesional</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input name="name" placeholder="Nombre y apellido" required>
        <input name="email" placeholder="Email" required>
        <input name="profession" placeholder="Profesión (psicólogo, etc.)" required>
        <input name="password" type="password" placeholder="Contraseña" required>
        <input name="password2" type="password" placeholder="Repetir contraseña" required>
        <button>Crear cuenta</button>
    </form>

    <p style="margin-top:10px;">¿Ya tenés cuenta? <a href="login.php">Ingresar</a></p>
</div>

</body>
</html>