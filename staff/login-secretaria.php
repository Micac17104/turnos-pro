<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require '../config.php';

$errors = [];

if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM center_staff WHERE email = ?");
    $stmt->execute([$email]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($staff && password_verify($password, $staff['password'])) {
        $_SESSION['staff_id'] = $staff['id'];
        $_SESSION['center_id'] = $staff['center_id'];
        $_SESSION['staff_role'] = $staff['role'];
        $_SESSION['user_name'] = $staff['name'];

        header("Location: staff-dashboard.php");
        exit;
    }

    $errors[] = "Email o contraseña incorrectos.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login secretaria</title>

<style>
body{background:#f1f5f9;font-family:Arial;display:flex;justify-content:center;align-items:center;height:100vh;}
.box{background:white;padding:40px;border-radius:20px;width:350px;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,0.08);}
input{width:100%;padding:14px;margin:8px 0;border-radius:12px;border:1px solid #cbd5e1;}
button{width:100%;padding:14px;background:#0ea5e9;color:white;border:none;border-radius:12px;font-weight:600;cursor:pointer;}
button:hover{opacity:0.9;}
.error{color:#b00020;margin-bottom:10px;}
</style>

</head>
<body>

<div class="box">
    <h2>Ingreso secretaria</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input name="email" placeholder="Email" required>
        <input name="password" type="password" placeholder="Contraseña" required>
        <button>Ingresar</button>
    </form>

    <p style="margin-top:12px;">
        <a href="../auth/login.php" style="color:#0ea5e9;">Volver</a>
    </p>
</div>

</body>
</html>