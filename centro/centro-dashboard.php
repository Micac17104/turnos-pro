<?php
session_start();

require '../config.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

// 🚨 BLOQUEO DE SUSCRIPCIÓN (middleware)
require __DIR__ . '/includes/check_subscription.php';
?>

$center_id = $_SESSION['user_id'] ?? null;
if (!$center_id) {
    header("Location: ../auth/login.php");
    exit;
}

// Traer info de suscripción
$stmt = $pdo->prepare("SELECT subscription_end, is_active FROM users WHERE id = ?");
$stmt->execute([$center_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$alerta_suscripcion = null;

if ($user) {
    if (!empty($user['subscription_end'])) {
        $hoy = new DateTime();
        $fin = new DateTime($user['subscription_end']);
        $diff = (int) $hoy->diff($fin)->format('%r%a');

        if ($diff <= 5 && $diff >= 0) {
            $alerta_suscripcion = "La suscripción del centro vence en $diff día" . ($diff == 1 ? '' : 's') . ". Renovala para evitar suspensión.";
        } elseif ($diff < 0) {
            $alerta_suscripcion = "La suscripción del centro está vencida. Deben renovarla para seguir usando el sistema.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel del centro</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:1100px;margin:0 auto;}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;}
.card{background:white;border-radius:16px;padding:24px;text-align:center;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
.card h3{margin:0 0 10px;font-size:18px;color:#0f172a;}
.card p{margin:0 0 16px;color:#475569;font-size:14px;}
.btn{display:inline-block;padding:10px 16px;border-radius:999px;font-size:14px;text-decoration:none;background:#0ea5e9;color:white;}
.alert{margin-bottom:16px;padding:12px 16px;border-radius:12px;font-size:14px;}
.alert-warning{background:#fef3c7;color:#92400e;}
.alert-danger{background:#fee2e2;color:#b91c1c;}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div style="margin-left:260px; padding:24px;">

<div class="top">
    <div><strong>TurnosPro – Centro</strong></div>
    <div>
        <?= htmlspecialchars($_SESSION['user_name'] ?? 'Centro') ?>
        &nbsp;|&nbsp;
        <a href="../auth/logout.php" style="color:#0ea5e9;text-decoration:none;">Salir</a>
    </div>
</div>

<div class="main">

    <!-- CARTEL DE SUSCRIPCIÓN VENCIDA -->
    <?php if (!empty($_SESSION['suscripcion_vencida'])): ?>
        <div class="alert alert-danger">
            Tu suscripción está vencida.
            <a href="/centro/pago-preferencia.php" style="text-decoration:underline;font-weight:bold;color:inherit;">Renovar ahora</a>
        </div>
    <?php endif; ?>

    <!-- ALERTAS DE VENCIMIENTO PRÓXIMO -->
    <?php if ($alerta_suscripcion): ?>
        <div class="alert <?= (strpos($alerta_suscripcion, 'vencida') !== false) ? 'alert-danger' : 'alert-warning' ?>">
            <?= htmlspecialchars($alerta_suscripcion) ?>
            &nbsp;|&nbsp;
            <a href="planes.php" style="text-decoration:underline;font-weight:bold;color:inherit;">Ver planes</a>
        </div>
    <?php endif; ?>

    <h2>Panel del centro</h2>
    <p style="color:#475569;margin-bottom:24px;">Seleccioná una sección para administrar tu centro.</p>

    <div class="grid">

        <div class="card">
            <h3>Profesionales</h3>
            <p>Agregar, editar y ver profesionales del centro.</p>
            <a href="centro-profesionales.php" class="btn">Ir a profesionales</a>
        </div>

        <div class="card">
            <h3>Turnos</h3>
            <p>Ver, crear, modificar y cancelar turnos.</p>
            <a href="centro-turnos.php" class="btn">Ir a turnos</a>
        </div>

        <div class="card">
            <h3>Pacientes</h3>
            <p>Administrar pacientes del centro.</p>
            <a href="centro-pacientes.php" class="btn">Ir a pacientes</a>
        </div>

        <div class="card">
            <h3>Configuración</h3>
            <p>Editar datos del centro y preferencias.</p>
            <a href="centro-configuracion.php" class="btn">Ir a configuración</a>
        </div>

    </div>

</div> <!-- cierre .main -->

</div> <!-- cierre contenedor sidebar -->

</body>
</html>
