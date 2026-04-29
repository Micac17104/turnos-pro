<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../pro/includes/auth-centro.php';
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../pro/includes/helpers.php';

$center_id  = $_SESSION['user_id'];
$patient_id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("SELECT name FROM clients WHERE id = ? AND center_id = ?");
$stmt->execute([$patient_id, $center_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT *
    FROM estudios_medicos
    WHERE client_id = ?
    ORDER BY fecha DESC
");
$stmt->execute([$patient_id]);
$estudios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Estudios médicos</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.card{background:white;padding:20px;border-radius:16px;margin-bottom:20px;}
.btn{padding:10px 16px;background:#0ea5e9;color:white;border-radius:8px;text-decoration:none;}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div style="margin-left:260px;padding:24px;">

<h2>Estudios médicos de <?= h($paciente['name']) ?></h2>

<a href="estudio-crear.php?id=<?= $patient_id ?>" class="btn">Cargar estudio</a>
<a href="paciente-historia.php?id=<?= $patient_id ?>" class="btn" style="background:#64748b;">← Volver</a>

<?php foreach ($estudios as $e): ?>
<div class="card">
    <h3><?= h($e['titulo']) ?> — <?= $e['fecha'] ?></h3>
    <p><?= nl2br(h($e['descripcion'])) ?></p>

    <?php if ($e['archivo']): ?>
        <a href="../uploads/<?= h($e['archivo']) ?>" target="_blank">Ver archivo</a>
    <?php endif; ?>
</div>
<?php endforeach; ?>

</div>
</body>
</html>
