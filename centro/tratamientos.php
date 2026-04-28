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

// Obtener paciente
$stmt = $pdo->prepare("
    SELECT *
    FROM clients
    WHERE id = ? AND center_id = ?
");
$stmt->execute([$patient_id, $center_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("No tenés permiso para ver este paciente.");
}

// Obtener tratamientos
$stmt = $pdo->prepare("
    SELECT t.*, u.name AS profesional
    FROM tratamientos t
    LEFT JOIN users u ON u.id = t.professional_id
    WHERE t.client_id = ? AND t.center_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$patient_id, $center_id]);
$tratamientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Tratamientos realizados</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
.btn{padding:8px 14px;border-radius:8px;background:#0ea5e9;color:white;text-decoration:none;display:inline-block;}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<a href="paciente-historia.php?id=<?= $patient_id ?>" class="btn" style="background:#64748b;">
    ← Volver
</a>


<div style="margin-left:260px; padding:24px;">

<a href="paciente-historia.php?id=<?= $patient_id ?>" 
   class="btn" 
   style="background:#64748b; margin-bottom:15px; display:inline-block;">
   ← Volver
</a>

<h2>Tratamientos realizados — <?= h($paciente['name']) ?></h2>

<a href="tratamiento-nuevo.php?id=<?= $patient_id ?>" class="btn" style="margin:10px 0;display:inline-block;">
    Registrar nuevo tratamiento
</a>

<div class="card">
<?php if (empty($tratamientos)): ?>
    <p>No hay tratamientos registrados.</p>
<?php endif; ?>

<?php foreach ($tratamientos as $t): ?>
    <div style="margin-bottom:20px;">
        <strong><?= h($t['tratamiento']) ?></strong><br>
        <small><?= $t['created_at'] ?></small><br>

        <?php if ($t['profesional']): ?>
            <small><strong>Profesional:</strong> <?= h($t['profesional']) ?></small><br>
        <?php endif; ?>

        <?php if ($t['productos']): ?>
            <p><strong>Productos:</strong> <?= nl2br(h($t['productos'])) ?></p>
        <?php endif; ?>

        <?php if ($t['parametros']): ?>
            <p><strong>Parámetros:</strong> <?= nl2br(h($t['parametros'])) ?></p>
        <?php endif; ?>

        <?php if ($t['observaciones']): ?>
            <p><strong>Observaciones:</strong> <?= nl2br(h($t['observaciones'])) ?></p>
        <?php endif; ?>

        <hr style="opacity:0.2;">
    </div>
<?php endforeach; ?>
</div>

</div>
</body>
</html>
