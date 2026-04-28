<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/auth-centro.php';
require __DIR__ . '/../pro/includes/helpers.php';
require __DIR__ . '/../pro/includes/db.php';

$center_id = $_SESSION['user_id'];
$patient_id = $_GET['id'] ?? null;

if (!$patient_id) {
    die("Paciente no encontrado.");
}

// Obtener datos clínicos actuales
$stmt = $pdo->prepare("
    SELECT *
    FROM clinical_extra
    WHERE client_id = ?
");
$stmt->execute([$patient_id]);
$extra = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar datos clínicos</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input,textarea{padding:8px;border-radius:8px;border:1px solid #cbd5e1;width:100%;}
.btn{padding:8px 14px;border-radius:8px;background:#0ea5e9;color:white;text-decoration:none;display:inline-block;margin-right:10px;}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div style="margin-left:260px; padding:24px;">

<h2>Editar datos clínicos</h2>

<a href="paciente-historia.php?id=<?= $patient_id ?>" class="btn" style="background:#64748b;">
    ← Volver
</a>

<div class="card">
    <form action="paciente-datos-guardar.php" method="post">

        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <label><strong>Antecedentes</strong></label>
        <textarea name="antecedentes"><?= h($extra['antecedentes'] ?? '') ?></textarea>

        <label><strong>Alergias</strong></label>
        <textarea name="alergias"><?= h($extra['alergias'] ?? '') ?></textarea>

        <label><strong>Medicación</strong></label>
        <textarea name="medicacion"><?= h($extra['medicacion'] ?? '') ?></textarea>

        <label><strong>Patologías</strong></label>
        <textarea name="patologias"><?= h($extra['patologias'] ?? '') ?></textarea>

        <label><strong>Obra social</strong></label>
        <input type="text" name="obra_social" value="<?= h($extra['obra_social'] ?? '') ?>">

        <label><strong>Nro afiliado</strong></label>
        <input type="text" name="nro_afiliado" value="<?= h($extra['nro_afiliado'] ?? '') ?>">

        <br><br>
        <button class="btn">Guardar cambios</button>
    </form>
</div>

</div>
</body>
</html>
