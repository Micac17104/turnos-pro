<?php
session_start();

require __DIR__ . '/includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;
$tipo    = $_SESSION['account_type'] ?? null; // 'professional' o 'center'

if (!$user_id) {
    header("Location: /auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT name, subscription_end FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$today = strtotime(date('Y-m-d'));
$end   = strtotime($user['subscription_end']);

$dias = floor(($today - $end) / 86400);

// Mensaje según días
if ($dias < 0) {
    $mensajeDias = "Tu suscripción vence en " . abs($dias) . " días.";
} elseif ($dias === 0) {
    $mensajeDias = "Tu suscripción venció hoy.";
} else {
    $mensajeDias = "Tu suscripción venció hace {$dias} días.";
}

// Mensaje según tipo de cuenta
if ($tipo === 'center') {
    $titulo = "Suscripción del centro vencida";
    $subtitulo = "Para seguir gestionando profesionales, turnos y pacientes del centro, renová tu suscripción.";
} else {
    $titulo = "Suscripción profesional vencida";
    $subtitulo = "Para seguir usando tu panel profesional, renová tu suscripción.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?php echo $titulo; ?></title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f8fafc;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 420px;
    margin: 60px auto;
    background: white;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    text-align: center;
}
h2 {
    color: #b91c1c;
    margin-bottom: 10px;
}
p {
    color: #374151;
    font-size: 16px;
}
.btn {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 20px;
    background: #2563eb;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
}
.btn:hover {
    background: #1d4ed8;
}
</style>
</head>
<body>

<div class="container">
    <h2><?php echo $titulo; ?></h2>
    <p><?php echo $mensajeDias; ?></p>
    <p><?php echo $subtitulo; ?></p>

    <a href="/pro/planes.php" class="btn">Ver planes y renovar</a>
</div>

</body>
</html>
