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

// Obtener ficha estética
$stmt = $pdo->prepare("
    SELECT *
    FROM ficha_estetica
    WHERE client_id = ? AND center_id = ?
");
$stmt->execute([$patient_id, $center_id]);
$ficha = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ficha estética</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input,textarea{padding:8px;border-radius:8px;border:1px solid #cbd5e1;width:100%;}
.btn{padding:8px 14px;border-radius:8px;background:#0ea5e9;color:white;text-decoration:none;display:inline-block;}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div style="margin-left:260px; padding:24px;">

<a href="paciente-historia.php?id=<?= $patient_id ?>" 
   class="btn" 
   style="background:#64748b; margin-bottom:15px; display:inline-block;">
   ← Volver
</a>

<h2 style="margin-bottom:20px;">Ficha estética de <?= h($paciente['name']) ?></h2>

<div class="card">
    <form action="ficha-estetica-guardar.php" method="post">

        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <label><strong>Tipo de piel</strong></label>
        <input type="text" name="tipo_piel" value="<?= h($ficha['tipo_piel'] ?? '') ?>">

        <label><strong>Zonas a tratar</strong></label>
        <textarea name="zonas_tratar" rows="2"><?= h($ficha['zonas_tratar'] ?? '') ?></textarea>

        <label><strong>Tratamientos previos</strong></label>
        <textarea name="tratamientos_previos" rows="2"><?= h($ficha['tratamientos_previos'] ?? '') ?></textarea>

        <label><strong>Contraindicaciones estéticas</strong></label>
        <textarea name="contraindicaciones" rows="2"><?= h($ficha['contraindicaciones'] ?? '') ?></textarea>

        <label><strong>Objetivo del tratamiento</strong></label>
        <textarea name="objetivo" rows="2"><?= h($ficha['objetivo'] ?? '') ?></textarea>

        <label><strong>Productos usados</strong></label>
        <textarea name="productos_usados" rows="2"><?= h($ficha['productos_usados'] ?? '') ?></textarea>

        <label><strong>Rutina recomendada</strong></label>
        <textarea name="rutina_recomendada" rows="2"><?= h($ficha['rutina_recomendada'] ?? '') ?></textarea>

        <br><br>
        <button class="btn">Guardar ficha estética</button>
    </form>
</div>

</div>
</body>
</html>
