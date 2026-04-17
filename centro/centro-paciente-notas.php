<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id'];
$client_id = $_GET['id'] ?? null;

if (!$client_id) {
    header("Location: centro-pacientes.php");
    exit;
}

// Obtener datos del paciente
$stmt = $pdo->prepare("
    SELECT id, name
    FROM clients
    WHERE id = ? AND center_id = ?
");
$stmt->execute([$client_id, $center_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente no encontrado.");
}

// Obtener notas del paciente
$stmt = $pdo->prepare("
    SELECT pn.id, pn.note, pn.created_at, u.name AS autor
    FROM patient_notes pn
    LEFT JOIN users u ON pn.user_id = u.id
    WHERE pn.client_id = ? AND pn.center_id = ?
    ORDER BY pn.created_at DESC
");
$stmt->execute([$client_id, $center_id]);
$notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Notas del paciente</title>
<style>
body{background:#f1f5f9;font-family:Arial;margin:0;}
.box{background:white;padding:30px;border-radius:20px;max-width:700px;margin:40px auto;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
.note{background:#f8fafc;border:1px solid #e2e8f0;padding:16px;border-radius:12px;margin-bottom:16px;}
.note small{color:#64748b;}
.btn{display:inline-block;padding:10px 16px;border-radius:10px;background:#0ea5e9;color:white;text-decoration:none;font-size:14px;}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div style="margin-left:260px; padding:24px;">

<div class="box">
    <h2>Notas internas de <?= htmlspecialchars($paciente['name']) ?></h2>

    <a href="centro-paciente-nota-nueva.php?id=<?= $client_id ?>" class="btn" style="margin-bottom:20px;display:inline-block;">
        + Nueva nota
    </a>

    <?php if (empty($notas)): ?>
        <p>No hay notas registradas para este paciente.</p>
    <?php endif; ?>

    <?php foreach ($notas as $n): ?>
        <div class="note">
            <p><?= nl2br(htmlspecialchars($n['note'])) ?></p>
            <small>
                <?= htmlspecialchars($n['autor'] ?? 'Centro') ?> —
                <?= date('d/m/Y H:i', strtotime($n['created_at'])) ?>
            </small>
        </div>
    <?php endforeach; ?>

    <p style="margin-top:20px;">
        <a href="centro-paciente-ver.php?id=<?= $client_id ?>" class="btn" style="background:#64748b;">Volver al paciente</a>
    </p>
</div>

</div>
</body>
</html>