<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../pro/includes/db.php';

// Si no está logueado, afuera
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Suscripción vencida</title>
</head>
<body style="font-family: sans-serif; padding: 40px;">

    <h1 style="color: #b91c1c;">Tu suscripción está vencida</h1>
    <p>Para seguir usando el panel, necesitás renovar tu suscripción.</p>

    <a href="/centro/planes.php" 
       style="display:inline-block; padding:12px 20px; background:#2563eb; color:white; border-radius:6px; text-decoration:none;">
        Renovar suscripción
    </a>

</body>
</html>
