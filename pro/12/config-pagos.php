<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    exit("No autorizado.");
}

$stmt = $pdo->prepare("SELECT mp_access_token FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Configurar pagos</title>
<link rel="stylesheet" href="/turnos-pro/assets/style.css">
</head>
<body>

<div class="container">
    <h2>Pagos online</h2>

    <form method="post" action="guardar-config-pagos.php">
        <label>Access Token de Mercado Pago</label>
        <input type="text" name="mp_access_token" value="<?= htmlspecialchars($user['mp_access_token'] ?? '') ?>">

        <button class="btn-big">Guardar</button>
    </form>

    <a href="dashboard.php" class="btn-ghost">← Volver</a>
</div>

</body>
</html>