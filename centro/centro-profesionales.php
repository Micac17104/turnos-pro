<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id'];

// Obtener profesionales del centro
$stmt = $pdo->prepare("
    SELECT id, name, email, profession, accepts_insurance, slug,
           video_link   -- 🔥 AGREGADO: video_link
    FROM users
    WHERE account_type='professional'
    AND parent_center_id=?
    ORDER BY name
");
$stmt->execute([$center_id]);
$profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Profesionales del centro</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:1100px;margin:0 auto;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
.btn{display:inline-block;padding:8px 14px;border-radius:999px;font-size:14px;text-decoration:none;}
.btn-primary{background:#0ea5e9;color:white;}
table{width:100%;border-collapse:collapse;font-size:14px;}
th,td{padding:8px 6px;border-bottom:1px solid #e5e7eb;text-align:left;}
.badge{padding:4px 10px;border-radius:999px;font-size:12px;}
.badge-yes{background:#22c55e;color:white;}
.badge-no{background:#fecaca;color:#b91c1c;}
.action-link{margin-right:8px;color:#0ea5e9;text-decoration:none;}
</style>
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div style="margin-left:260px; padding:24px;">

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
                <th>Obra social</th>

                <!-- 🔥 COLUMNA VIDEOLLAMADA -->
                <th>Videollamada</th>

                <th>Acciones</th>
            </tr>

            <?php foreach ($profesionales as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['profession'] ?? '-') ?></td>
                <td><?= htmlspecialchars($p['email']) ?></td>

                <td>
                    <?php if ($p['accepts_insurance']): ?>
                        <span class="badge badge-yes">Sí</span>
                    <?php else: ?>
                        <span class="badge badge-no">No</span>
                    <?php endif; ?>
                </td>

                <!-- 🔥 MOSTRAR SI TIENE LINK -->
                <td>
                    <?php if (!empty($p['video_link'])): ?>
                        <span class="badge badge-yes">Sí</span>
                    <?php else: ?>
                        <span class="badge badge-no">No</span>
                    <?php endif; ?>
                </td>

                <td>

                    <a class="action-link" href="centro-profesional-ver.php?id=<?= $p['id'] ?>">Ver</a>

                    <a class="action-link" href="centro-profesionales-editar.php?id=<?= $p['id'] ?>">Editar</a>

                    <a class="action-link" href="centro-profesional-publico.php?id=<?= $p['id'] ?>">
                        Perfil público
                    </a>

                    <?php if (!empty($p['slug'])): ?>
                        <a class="action-link" href="/<?= htmlspecialchars($p['slug']) ?>" target="_blank">
                            Ver landing
                        </a>
                    <?php endif; ?>

                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($profesionales)): ?>
            <tr><td colspan="6">Todavía no agregaste profesionales.</td></tr>
            <?php endif; ?>
        </table>
    </div>

</div>

</div>
</body>
</html>
