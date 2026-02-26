<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';

$errors = [];

if ($_POST) {

    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($password !== $password2) {
        $errors[] = "Las contraseñas no coinciden.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido.";
    }

    // Validar email único
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Ese email ya está registrado.";
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, account_type, parent_center_id)
            VALUES (?, ?, ?, 'secretary', ?)
        ");

        $stmt->execute([
            $name,
            $email,
            password_hash($password, PASSWORD_BCRYPT),
            $center_id
        ]);

        header("Location: centro-secretarias.php?ok=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nueva secretaria</title>
<style>
body{background:#f1f5f9;font-family:Arial;display:flex;justify-content:center;align-items:center;padding:40px;}
.box{background:white;padding:40px;border-radius:20px;width:380px;text-align:center;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input{width:100%;padding:12px;margin:8px 0;border-radius:10px;border:1px solid #cbd5e1;}
button{width:100%;padding:14px;background:#0ea5e9;color:white;border:none;border-radius:12px;font-weight:600;cursor:pointer;}
button:hover{opacity:0.9;}
.error{color:#b00020;margin-bottom:10px;}
a{color:#0ea5e9;text-decoration:none;font-size:14px;}
</style>
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div style="margin-left:260px; padding:24px;">

<div class="box">
    <h2>Agregar secretaria</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input name="name" placeholder="Nombre y apellido" required>
        <input name="email" type="email" placeholder="Email" required>
        <input name="password" type="password" placeholder="Contraseña" required>
        <input name="password2" type="password" placeholder="Repetir contraseña" required>
        <button>Crear secretaria</button>
    </form>

    <p style="margin-top:10px;"><a href="centro-secretarias.php">Volver</a></p>
</div>

</div>
</body>
</html>