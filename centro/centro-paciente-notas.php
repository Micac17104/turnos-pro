<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';

$client_id = $_GET['id'] ?? null;

if (!$client_id) {
    header("Location: centro-pacientes.php");
    exit;
}

// Obtener datos del paciente
$stmt = $pdo->prepare("
    SELECT id, name
    FROM clients
    WHERE id = ?
");
$stmt->execute([$client_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente no encontrado.");
}

// Obtener notas internas
$stmt = $pdo->prepare("
    SELECT n.note, n.created_at, u.name AS autor
    FROM patient_notes n
    JOIN users u ON n.user_id = u.id
    WHERE n.client_id = ?
    AND n.center_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$client_id, $center_id]);
$notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Notas internas – <?= htmlspecialchars($paciente['name']) ?></title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:900px;margin:0 auto;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
.note{padding:12px;border-left:4px solid #0ea5e9;margin-bottom:12px;background:#f8fafc;border-radius:8px;}
.btn{padding:8px 14px;border-radius:999px;background:#0ea5e9;color:white;text-decoration:none;}
</style>
</head>
<body>

<div class="top">
    <div><strong>TurnosPro – Centro</strong></div>
    <div>
        <?= htmlspecialchars($_SESSION['user_name']) ?>
        &nbsp;|&nbsp;
        <a href="../auth/logout.php" style="color:#0ea5e9;">Salir</a>
    </div>
</div>

<div class="main">

    <div class="card">
        <h2>Notas internas de <?= htmlspecialchars($paciente['name']) ?></h2>

        <a class="btn" href="centro-paciente-nota-nueva.php?id=<?= $paciente['id'] ?>">+ Agregar nota</a>
        <a class="btn" style="background:#64748b;" href="centro-paciente-ver.php?id=<?= $paciente['id'] ?>">Volver</a>
    </div>

    <div class="card">
        <?php if (empty($notas)): ?>
            <p>No hay notas internas para este paciente.</p>
        <?php endif; ?>

        <?php foreach ($notas as $n): ?>
            <div class="note">
                <strong><?= htmlspecialchars($n['autor']) ?></strong>
                <small style="color:#64748b;"> – <?= $n['created_at'] ?></small>
                <p><?= nl2br(htmlspecialchars($n['note'])) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

</div>

</body>
</html>