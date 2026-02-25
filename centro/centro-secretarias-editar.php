<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';

$sec_id = $_GET['id'] ?? null;

if (!$sec_id) {
    header("Location: centro-secretarias.php");
    exit;
}

// Obtener secretaria
$stmt = $pdo->prepare("
    SELECT id, name, email
    FROM users
    WHERE id = ? AND parent_center_id = ? AND account_type='secretary'
");
$stmt->execute([$sec_id, $center_id]);
$sec = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sec) {
    die("Secretaria no encontrada.");
}

$errors = [];
$success = "";

if ($_POST) {

    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido.";
    }

    // Validar email único
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $sec_id]);
    if ($stmt->fetch()) {
        $errors[] = "Ese email ya está registrado.";
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE users SET name=?, email=?
            WHERE id=? AND parent_center_id=?
        ");

        $stmt->execute([$name, $email, $sec_id, $center_id]);

        $success = "Datos actualizados correctamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar secretaria</title>
<style>
body{background:#f1f5f9;font-family:Arial;display:flex;justify-content:center;align-items:center;padding:40px;}
.box{background:white;padding:40px;border-radius:20px;width:380px;text-align:center;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input{width:100%;padding:12px;margin:8px 0;border-radius:10px;border:1px solid #cbd5e1;}
button{width:100%;padding:14px;background:#0ea5e9;color:white;border:none;border-radius:12px;font-weight:600;cursor:pointer;}
button:hover{opacity:0.9;}
.error{color:#b00020;margin-bottom:10px;}
.success{color:#22c55e;margin-bottom:10px;}
a{color:#0ea5e9;text-decoration:none;font-size:14px;}
</style>
</head>
<body>

<div class="box">
    <h2>Editar secretaria</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post">
        <input name="name" value="<?= htmlspecialchars($sec['name']) ?>" required>
        <input name="email" type="email" value="<?= htmlspecialchars($sec['email']) ?>" required>
        <button>Guardar cambios</button>
    </form>

    <p style="margin-top:10px;"><a href="centro-secretarias.php">Volver</a></p>
</div>

</body>
</html>