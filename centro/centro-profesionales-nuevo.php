<?php
session_start();
require '../config.php';

$center_id = $_SESSION['user_id'] ?? $_SESSION['center_id'] ?? null;
if (!$center_id) { header("Location: ../auth/login.php"); exit; }

$errors = [];

if ($_POST) {
    if ($_POST['password'] !== $_POST['password2']) {
        $errors[] = "Las contraseñas no coinciden.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, profession, account_type, parent_center_id)
            VALUES (?, ?, ?, ?, 'professional', ?)
        ");
        try {
            $stmt->execute([
                $_POST['name'],
                $_POST['email'],
                password_hash($_POST['password'], PASSWORD_BCRYPT),
                $_POST['profession'],
                $center_id
            ]);
            header("Location: centro-dashboard.php");
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
<title>Nuevo profesional</title>
<style>
body{background:#f1f5f9;font-family:Arial;display:flex;justify-content:center;align-items:center;height:100vh;}
.box{background:white;padding:40px;border-radius:20px;width:380px;text-align:center;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input{width:100%;padding:12px;margin:8px 0;border-radius:10px;border:1px solid #cbd5e1;}
button{width:100%;padding:14px;background:#0ea5e9;color:white;border:none;border-radius:12px;font-weight:600;cursor:pointer;}
button:hover{opacity:0.9;}
.error{color:#b00020;margin-bottom:10px;}
a{color:#0ea5e9;text-decoration:none;font-size:14px;}
</style>
</head>
<body>
<div class="box">
    <h2>Agregar profesional</h2>

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
        <button>Crear profesional</button>
    </form>

    <p style="margin-top:10px;"><a href="centro-dashboard.php">Volver al panel</a></p>
</div>
</body>
</html>