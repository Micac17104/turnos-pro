<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';

$center_id = $_SESSION['user_id'];
$prof_id = $_GET['id'] ?? null;

if (!$prof_id) {
    header("Location: centro-profesionales.php");
    exit;
}

// Obtener datos del profesional
$stmt = $pdo->prepare("
    SELECT id, name, email, profession, phone, city, description, specialties,
           accepts_insurance, insurance_list, slug
    FROM users
    WHERE id = ? AND parent_center_id = ? AND account_type='professional'
");
$stmt->execute([$prof_id, $center_id]);
$prof = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prof) {
    die("Profesional no encontrado.");
}

// Obtener próximos turnos
$stmt = $pdo->prepare("
    SELECT a.date, a.time, a.status,
           c.name AS paciente
    FROM appointments a
    JOIN clients c ON a.client_id = c.id
    WHERE a.user_id = ?
    AND a.date >= CURDATE()
    ORDER BY a.date ASC, a.time ASC
    LIMIT 20
");
$stmt->execute([$prof_id]);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar obras sociales
$insurance_list = $prof['insurance_list'] ? json_decode($prof['insurance_list'], true) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($prof['name']) ?> - Profesional</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:1100px;margin:0 auto;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
.btn{display:inline-block;padding:8px 14px;border-radius:999px;font-size:14px;text-decoration:none;}
.btn-primary{background:#0ea5e9;color:white;}
.badge{padding:4px 10px;border-radius:999px;font-size:12px;background:#e2e8f0;}
table{width:100%;border-collapse:collapse;font-size:14px;}
th,td{padding:8px 6px;border-bottom:1px solid #e5e7eb;text-align:left;}
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

    <!-- Datos del profesional -->
    <div class="card">
        <h2><?= htmlspecialchars($prof['name']) ?></h2>
        <p><strong>Profesión:</strong> <?= htmlspecialchars($prof['profession']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($prof['email']) ?></p>
        <p><strong>Teléfono:</strong> <?= htmlspecialchars($prof['phone'] ?: '-') ?></p>
        <p><strong>Ciudad:</strong> <?= htmlspecialchars($prof['city'] ?: '-') ?></p>
        <p><strong>Especialidades:</strong> <?= htmlspecialchars($prof['specialties'] ?: '-') ?></p>
        <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($prof['description'] ?: '-')) ?></p>

        <p><strong>Obra social:</strong>
            <?php if ($prof['accepts_insurance']): ?>
                Sí
                <br>
                <?php foreach ($insurance_list as $i): ?>
                    <span class="badge"><?= htmlspecialchars($i) ?></span>
                <?php endforeach; ?>
            <?php else: ?>
                No
            <?php endif; ?>
        </p>

        <p><strong>Landing pública:</strong><br>
            <a href="/<?= htmlspecialchars($prof['slug']) ?>" target="_blank">
                /<?= htmlspecialchars($prof['slug']) ?>
            </a>
        </p>

        <br>

        <a href="centro-profesionales-editar.php?id=<?= $prof['id'] ?>" class="btn btn-primary">Editar profesional</a>
        <a href="centro-profesionales.php" class="btn">Volver</a>
    </div>

    <!-- Próximos turnos -->
    <div class="card">
        <h2>Próximos turnos</h2>

        <table>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Estado</th>
            </tr>

            <?php foreach ($turnos as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['date']) ?></td>
                <td><?= htmlspecialchars(substr($t['time'], 0, 5)) ?></td>
                <td><?= htmlspecialchars($t['paciente']) ?></td>
                <td><?= htmlspecialchars($t['status']) ?></td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($turnos)): ?>
            <tr><td colspan="4">No hay turnos próximos.</td></tr>
            <?php endif; ?>
        </table>
    </div>

</div>

</div>
</body>
</html>