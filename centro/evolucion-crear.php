<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../pro/includes/auth-centro.php';
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../pro/includes/helpers.php';

$center_id  = $_SESSION['user_id'];
$patient_id = $_GET['id'] ?? null;

if (!$patient_id) {
    die("Paciente no encontrado.");
}

// Verificar que el paciente pertenece al centro
$stmt = $pdo->prepare("SELECT name FROM clients WHERE id = ? AND center_id = ?");
$stmt->execute([$patient_id, $center_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("No tenés permiso para ver este paciente.");
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Nueva evolución</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.card{background:white;padding:20px;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.06);}
input,textarea{width:100%;padding:10px;border-radius:8px;border:1px solid #cbd5e1;}
.btn{padding:10px 16px;background:#0ea5e9;color:white;border-radius:8px;text-decoration:none;}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div style="margin-left:260px;padding:24px;">

<h2>Nueva evolución de <?= h($paciente['name']) ?></h2>

<div class="card">
<form method="post" action="evolucion-guardar.php">

    <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

    <label><strong>Texto de la evolución</strong></label>
    <textarea name="texto" rows="5" required></textarea>

    <br><br>
    <button class="btn">Guardar evolución</button>
    <a href="paciente-historia.php?id=<?= $patient_id ?>" class="btn" style="background:#64748b;">Cancelar</a>

</form>
</div>

</div>
</body>
</html>
