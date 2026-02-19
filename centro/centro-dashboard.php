<?php
session_start();
require '../config.php';

$center_id = $_SESSION['user_id'] ?? $_SESSION['center_id'] ?? null;
if (!$center_id) { header("Location: ../auth/login.php"); exit; }

// Profesionales del centro
$stmt = $pdo->prepare("
    SELECT id, name, email, profession
    FROM users
    WHERE account_type='professional'
    AND parent_center_id=?
    ORDER BY name
");
$stmt->execute([$center_id]);
$profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Turnos del centro (últimos 20)
$stmt = $pdo->prepare("
    SELECT a.date, a.time, a.status,
           u.name AS profesional,
           c.name AS paciente
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN clients c ON a.client_id = c.id
    WHERE u.parent_center_id = ?
    ORDER BY a.date DESC, a.time DESC
    LIMIT 20
");
$stmt->execute([$center_id]);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel del centro</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:1100px;margin:0 auto;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
.btn{display:inline-block;padding:8px 14px;border-radius:999px;font-size:14px;text-decoration:none;}
.btn-primary{background:#0ea5e9;color:white;}
.badge{padding:4px 10px;border-radius:999px;font-size:12px;}
.badge-pending{background:#fbbf24;color:#92400e;}
.badge-confirmed{background:#22c55e;color:white;}
.badge-cancelled{background:#fecaca;color:#b91c1c;}
table{width:100%;border-collapse:collapse;font-size:14px;}
th,td{padding:8px 6px;border-bottom:1px solid #e5e7eb;text-align:left;}
</style>
</head>
<body>

<div class="top">
    <div><strong>TurnosPro – Centro</strong></div>
    <div>
        <?= htmlspecialchars($_SESSION['user_name'] ?? 'Centro') ?>
        &nbsp;|&nbsp;
        <a href="../auth/logout.php" style="color:#0ea5e9;text-decoration:none;">Salir</a>
    </div>
</div>

<div class="main">

    <div class="card">
        <h2>Profesionales del centro</h2>
        <a href="centro-profesionales-nuevo.php" class="btn btn-primary">+ Agregar profesional</a>
        <table style="margin-top:12px;">
            <tr>
                <th>Nombre</th>
                <th>Profesión</th>
                <th>Email</th>
            </tr>
            <?php foreach ($profesionales as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['profession'] ?? '-') ?></td>
                <td><?= htmlspecialchars($p['email']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($profesionales)): ?>
            <tr><td colspan="3">Todavía no agregaste profesionales.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="card">
        <h2>Últimos turnos del centro</h2>
        <table>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Profesional</th>
                <th>Paciente</th>
                <th>Estado</th>
            </tr>
            <?php foreach ($turnos as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['date']) ?></td>
                <td><?= htmlspecialchars(substr($t['time'],0,5)) ?></td>
                <td><?= htmlspecialchars($t['profesional']) ?></td>
                <td><?= htmlspecialchars($t['paciente']) ?></td>
                <td>
                    <?php
                    $s = $t['status'];
                    $class = $s==='confirmed'?'badge-confirmed':($s==='cancelled'?'badge-cancelled':'badge-pending');
                    ?>
                    <span class="badge <?= $class ?>"><?= htmlspecialchars($s) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($turnos)): ?>
            <tr><td colspan="5">Todavía no hay turnos registrados.</td></tr>
            <?php endif; ?>
        </table>
    </div>

</div>
</body>
</html>