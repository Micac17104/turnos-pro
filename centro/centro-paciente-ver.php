<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';

$client_id = $_GET['id'] ?? null;

if (!$client_id) {
    header("Location: centro-pacientes.php");
    exit;
}

// Verificar si appointments.center_id existe
$appointments_has_center = false;
$check = $pdo->query("SHOW COLUMNS FROM appointments LIKE 'center_id'");
if ($check->fetch()) {
    $appointments_has_center = true;
}

// Obtener datos del paciente SOLO si pertenece al centro
$stmt = $pdo->prepare("
    SELECT id, name, email, phone, dni
    FROM clients
    WHERE id = ? AND center_id = ?
");
$stmt->execute([$client_id, $center_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente no encontrado o no pertenece a este centro.");
}

// Profesionales que atienden a este paciente (centro)
$stmt = $pdo->prepare("
    SELECT cs.name
    FROM patient_professionals pp
    JOIN center_staff cs ON cs.id = pp.staff_id
    WHERE pp.patient_id = ? AND pp.center_id = ?
    ORDER BY cs.name
");
$stmt->execute([$client_id, $center_id]);
$profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Base query historial
$query = "
    SELECT a.date, a.time, a.status,
           u.name AS profesional
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.client_id = ?
";

$params = [$client_id];

// Filtrar por centro SOLO si la columna existe
if ($appointments_has_center) {
    $query .= " AND a.center_id = ? ";
    $params[] = $center_id;
}

$query .= " ORDER BY a.date DESC, a.time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Paciente: <?= htmlspecialchars($paciente['name']) ?></title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:900px;margin:0 auto;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
table{width:100%;border-collapse:collapse;font-size:14px;}
th,td{padding:8px 6px;border-bottom:1px solid #e5e7eb;text-align:left;}
.badge{padding:4px 10px;border-radius:999px;font-size:12px;}
.badge-pending{background:#fbbf24;color:#92400e;}
.badge-confirmed{background:#22c55e;color:white;}
.badge-cancelled{background:#fecaca;color:#b91c1c;}
.btn{display:inline-block;padding:10px 16px;background:#0ea5e9;color:white;border-radius:10px;text-decoration:none;margin-top:10px;}
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
        <h2><?= htmlspecialchars($paciente['name']) ?></h2>
        <p><strong>DNI:</strong> <?= htmlspecialchars($paciente['dni']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($paciente['email'] ?: '-') ?></p>
        <p><strong>Teléfono:</strong> <?= htmlspecialchars($paciente['phone'] ?: '-') ?></p>

        <p><strong>Profesionales que lo atienden:</strong>
            <?php if (!empty($profesionales)): ?>
                <?= htmlspecialchars(implode(', ', array_column($profesionales, 'name'))) ?>
            <?php else: ?>
                <span>- Sin profesionales asignados -</span>
            <?php endif; ?>
        </p>

        <br>

        <a class="btn" href="centro-paciente-notas.php?id=<?= $paciente['id'] ?>">
            📝 Notas internas
        </a>
    </div>

    <div class="card">
        <h2>Historial de turnos</h2>

        <table>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Profesional</th>
                <th>Estado</th>
            </tr>

            <?php foreach ($turnos as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['date']) ?></td>
                <td><?= htmlspecialchars(substr($t['time'], 0, 5)) ?></td>
                <td><?= htmlspecialchars($t['profesional']) ?></td>
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
            <tr><td colspan="4">Este paciente aún no tiene turnos.</td></tr>
            <?php endif; ?>
        </table>
    </div>

</div>

</div>
</body>
</html>