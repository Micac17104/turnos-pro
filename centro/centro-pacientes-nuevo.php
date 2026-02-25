<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';

$errors = [];
$success = "";

if ($_POST) {

    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '') {
        $errors[] = "El nombre es obligatorio.";
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido.";
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO clients (name, email, phone)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([$name, $email, $phone]);

        header("Location: centro-pacientes.php?ok=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo paciente</title>
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

<div class="box">
    <h2>Agregar paciente</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input name="name" placeholder="Nombre y apellido" required>
        <input name="email" type="email" placeholder="Email (opcional)">
        <input name="phone" placeholder="Teléfono (opcional)">
        <button>Crear paciente</button>
    </form>

    <p style="margin-top:10px;"><a href="centro-pacientes.php">Volver</a></p>
</div>

</body>
</html>