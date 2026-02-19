<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    exit("No autorizado.");
}

$appointment_id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
$stmt->execute([$appointment_id, $user_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    exit("Turno no encontrado.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Actualizar pago</title>
<link rel="stylesheet" href="/turnos-pro/assets/style.css">
</head>
<body>

<div class="container">
    <h2>Actualizar pago</h2>

    <form method="post" action="guardar-pago.php">
        <input type="hidden" name="appointment_id" value="<?= $appointment_id ?>">

        <label>Estado del pago:</label>
        <select name="payment_status">
            <option value="pendiente" <?= $turno['payment_status']=='pendiente'?'selected':'' ?>>Pendiente</option>
            <option value="pagado" <?= $turno['payment_status']=='pagado'?'selected':'' ?>>Pagado</option>
        </select>

        <label>Método de pago:</label>
        <select name="payment_method">
            <option value="">Seleccionar</option>
            <option value="efectivo" <?= $turno['payment_method']=='efectivo'?'selected':'' ?>>Efectivo</option>
            <option value="transferencia" <?= $turno['payment_method']=='transferencia'?'selected':'' ?>>Transferencia</option>
            <option value="mercado_pago" <?= $turno['payment_method']=='mercado_pago'?'selected':'' ?>>Mercado Pago</option>
        </select>

        <button class="btn-big">Guardar</button>
    </form>

    <a href="/turnos-pro/profiles/<?= $user_id ?>/dashboard.php" class="btn-ghost">← Volver al panel</a>
</div>

</body>
</html>