<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/db.php';

require __DIR__ . '/../pro/includes/auth-centro.php';
require __DIR__ . '/../pro/includes/helpers.php';

$center_id = $_SESSION['user_id'];
$patient_id = $_GET['id'] ?? null;

if (!$patient_id) {
    die("Paciente no encontrado.");
}

// Obtener profesionales del centro
$stmt = $pdo->prepare("
    SELECT id, name
    FROM users
    WHERE account_type='professional'
    AND parent_center_id=?
    ORDER BY name
");
$stmt->execute([$center_id]);
$profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo tratamiento</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input,textarea,select{padding:8px;border-radius:8px;border:1px solid #cbd5e1;width:100%;}
.btn{padding:8px 14px;border-radius:8px;background:#0ea5e9;color:white;text-decoration:none;display:inline-block;}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div style="margin-left:260px; padding:24px;">

<h2>Registrar tratamiento</h2>

<div class="card">
    <form action="tratamiento-guardar.php" method="post">

        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <label><strong>Tratamiento realizado</strong></label>
        <input type="text" name="tratamiento" required>

        <label><strong>Profesional</strong></label>
        <select name="professional_id">
            <option value="">(Opcional)</option>
            <?php foreach ($profesionales as $p): ?>
                <option value="<?= $p['id'] ?>"><?= h($p['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label><strong>Productos usados</strong></label>
        <textarea name="productos" rows="2"></textarea>

        <label><strong>Parámetros (si aplica)</strong></label>
        <textarea name="parametros" rows="2"></textarea>

        <label><strong>Observaciones</strong></label>
        <textarea name="observaciones" rows="3"></textarea>

        <br><br>
        <button class="btn">Guardar tratamiento</button>
    </form>
</div>

</div>
</body>
</html>
