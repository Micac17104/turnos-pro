<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/db.php';

require __DIR__ . '/../pro/includes/auth-centro.php';
require __DIR__ . '/../pro/includes/helpers.php';

$center_id = $_SESSION['user_id'];
$patient_id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo plan</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input{padding:8px;border-radius:8px;border:1px solid #cbd5e1;width:100%;}
.btn{padding:8px 14px;border-radius:8px;background:#0ea5e9;color:white;text-decoration:none;display:inline-block;}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div style="margin-left:260px; padding:24px;">

<h2>Crear plan de sesiones</h2>

<div class="card">
    <form action="plan-guardar.php" method="post">

        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <label><strong>Nombre del plan</strong></label>
        <input type="text" name="nombre" required>

        <label><strong>Cantidad de sesiones</strong></label>
        <input type="number" name="total_sesiones" min="1" required>

        <br><br>
        <button class="btn">Crear plan</button>
    </form>
</div>

</div>
</body>
</html>
