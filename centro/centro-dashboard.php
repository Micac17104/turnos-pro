<?php
session_start();
require '../config.php';

$center_id = $_SESSION['user_id'] ?? $_SESSION['center_id'] ?? null;
if (!$center_id) { header("Location: ../auth/login.php"); exit; }
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
            <h3>Secretarias</h3>
            <p>Agregar y gestionar cuentas de secretarias.</p>
            <a href="centro-secretarias.php" class="btn">Ir a secretarias</a>
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