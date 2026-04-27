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

// Obtener planes
$stmt = $pdo->prepare("
    SELECT *
    FROM planes
    WHERE client_id = ? AND center_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$patient_id, $center_id]);
$planes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Planes de sesiones</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
.btn{padding:8px 14px;border-radius:8px;background:#0ea5e9;color:white;text-decoration:none;display:inline-block;}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div style="margin-left:260px; padding:24px;">

<h2>Planes de sesiones — <?= h($paciente['name']) ?></h2>

<a href="plan-nuevo.php?id=<?= $patient_id ?>" class="btn" style="margin:10px 0;">
    Crear nuevo plan
</a>

<div class="card">
<?php if (empty($planes)): ?>
    <p>No hay planes creados.</p>
<?php endif; ?>

<?php foreach ($planes as $p): ?>

    <?php
    // Obtener sesiones del plan
    $stmt = $pdo->prepare("
        SELECT *
        FROM plan_sesiones
        WHERE plan_id = ?
        ORDER BY numero ASC
    ");
    $stmt->execute([$p['id']]);
    $sesiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div style="margin-bottom:25px;">
        <h3><?= h($p['nombre']) ?> (<?= count($sesiones) ?> sesiones)</h3>

        <?php foreach ($sesiones as $s): ?>
            <div style="margin:6px 0; padding:8px; background:#f8fafc; border-radius:8px;">
                <strong>Sesión <?= $s['numero'] ?>:</strong>
                <?= $s['realizada'] ? '✔ Realizada' : 'Pendiente' ?>

                <?php if (!$s['realizada']): ?>
                    <a href="plan-sesion-marcar.php?id=<?= $s['id'] ?>&plan=<?= $p['id'] ?>&paciente=<?= $patient_id ?>"
                       class="btn" style="background:#22c55e; padding:4px 10px; font-size:12px;">
                        Marcar realizada
                    </a>
                <?php endif; ?>

                <?php if ($s['notas']): ?>
                    <p><small><strong>Notas:</strong> <?= nl2br(h($s['notas'])) ?></small></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <hr style="opacity:0.2; margin:15px 0;">
    </div>

<?php endforeach; ?>
</div>

</div>
</body>
</html>
