<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$patient_id = $_GET['id'] ?? null;

if (!$patient_id) {
    die("Paciente no encontrado.");
}

// Obtener ficha estética
$stmt = $pdo->prepare("SELECT * FROM ficha_estetica WHERE client_id = ?");
$stmt->execute([$patient_id]);
$ficha = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Ficha estética</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;}
.btn{padding:8px 14px;border-radius:8px;background:#0ea5e9;color:white;text-decoration:none;}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div style="margin-left:260px; padding:24px;">

<a href="paciente-historia.php?id=<?= $patient_id ?>" class="btn" style="background:#64748b;">← Volver</a>

<h2>Ficha estética</h2>

<div class="card">
    <form action="ficha-estetica-guardar.php" method="post">
        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <label>Objetivo estético</label>
        <textarea name="objetivo"><?= $ficha['objetivo'] ?? '' ?></textarea>

        <label>Observaciones</label>
        <textarea name="observaciones"><?= $ficha['observaciones'] ?? '' ?></textarea>

        <br><br>
        <button class="btn">Guardar</button>
    </form>
</div>

</div>
</body>
</html>
