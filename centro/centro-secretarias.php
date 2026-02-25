<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';

// Obtener secretarias del centro
$stmt = $pdo->prepare("
    SELECT id, name, email
    FROM users
    WHERE account_type='secretary'
    AND parent_center_id=?
    ORDER BY name
");
$stmt->execute([$center_id]);
$secretarias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Secretarias del centro</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:1100px;margin:0 auto;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
.btn{display:inline-block;padding:8px 14px;border-radius:999px;font-size:14px;text-decoration:none;}
.btn-primary{background:#0ea5e9;color:white;}
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
        <h2>Secretarias del centro</h2>

        <a href="centro-secretarias-nuevo.php" class="btn btn-primary">+ Agregar secretaria</a>

        <table style="margin-top:12px;">
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Acciones</th>
            </tr>

            <?php foreach ($secretarias as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td>
                    <a href="centro-secretarias-editar.php?id=<?= $s['id'] ?>">Editar</a>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($secretarias)): ?>
            <tr><td colspan="3">Todavía no agregaste secretarias.</td></tr>
            <?php endif; ?>
        </table>
    </div>

</div>

</body>
</html>