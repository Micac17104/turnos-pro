<?php
// --- SESIONES ---
$path = __DIR__ . '/../sessions'; // login.php está en /auth → subir 1 nivel
if (!is_dir($path)) mkdir($path, 0777, true);
session_save_path($path);
session_start();

require __DIR__ . '/../pro/includes/db.php';

// SI YA ESTÁ LOGUEADO → IR AL DASHBOARD
if (isset($_SESSION['user_id'])) {
    header("Location: /pro/dashboard.php");
    exit;
}

// PROCESAR LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: /pro/dashboard.php");
        exit;
    }

    $error = "Credenciales incorrectas";
}
?>

<!-- FORMULARIO HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

<h1>Iniciar sesión</h1>

<?php if (!empty($error)): ?>
<p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Contraseña" required><br><br>
    <button type="submit">Ingresar</button>
</form>

</body>
</html>