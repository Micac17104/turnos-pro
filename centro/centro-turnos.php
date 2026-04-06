<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';

// Filtros
$prof_filter = $_GET['prof'] ?? '';
$date_filter = $_GET['date'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Obtener profesionales del centro (para el filtro)
$stmt = $pdo->prepare("
    SELECT id, name 
    FROM users 
    WHERE account_type='professional' 
    AND parent_center_id=? 
    ORDER BY name
");
$stmt->execute([$center_id]);
$profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Detectar si clients.center_id existe
$clients_has_center = false;
$check = $pdo->query("SHOW COLUMNS FROM clients LIKE 'center_id'");
if ($check->fetch()) {
    $clients_has_center = true;
}

// Detectar si appointments.center_id existe
$appointments_has_center = false;
$check2 = $pdo->query("SHOW COLUMNS FROM appointments LIKE 'center_id'");
if ($check2->fetch()) {
    $appointments_has_center = true;
}

// Base query
$query = "
    SELECT a.id, a.date, a.time, a.status, a.motivo,
           u.name AS profesional,
           c.name AS paciente
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN clients c ON a.client_id = c.id
    WHERE 1=1
";

$params = [];

// Filtrar por centro SOLO si la columna existe
if ($appointments_has_center) {
    $query .= " AND a.center_id = ? ";
    $params[] = $center_id;
}

// Filtrar pacientes por centro SOLO si la columna existe
if ($clients_has_center) {
    $query .= " AND c.center_id = ? ";
    $params[] = $center_id;
}

// Filtro por profesional
if ($prof_filter !== '') {
    $query .= " AND u.id = ? ";
    $params[] = $prof_filter;
}

// Filtro por fecha
if ($date_filter !== '') {
    $query .= " AND a.date = ? ";
    $params[] = $date_filter;
}

// Filtro por estado
if ($status_filter !== '') {
    $query .= " AND a.status = ? ";
    $params[] = $status_filter;
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
<title>Turnos del centro</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:1100px;margin:0 auto;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
table{width:100%;border-collapse:collapse;font-size:14px;}
th,td{padding:8px 6px;border-bottom:1px solid #e5e7eb;text-align:left;}
.badge{padding:4px 10px;border-radius:999px;font-size:12px;}
.badge-pending{background:#fbbf24;color:#92400e;}
.badge-confirmed{background:#22c55e;color:white;}
.badge-cancelled{background:#fecaca;color:#b91c1c;}
select,input{padding:8px;border-radius:8px;border:1px solid #cbd5e1;margin-right:10px;}
.btn{padding:8px 14px;border-radius:999px;background:#0ea5e9;color:white;text-decoration:none;}
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

        <!-- Título + Botón Nuevo Turno -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h2>Turnos del centro</h2>

            <a href="centro-turnos-nuevo.php" 
               style="background:#0ea5e9; color:white; padding:8px 14px; border-radius:999px; text-decoration:none;">
                + Nuevo turno
            </a>
        </div>

        <!-- Filtros -->
        <form method="GET" style="margin-bottom:15px;">

            <select name="prof">
                <option value="">Todos los profesionales</option>
                <?php foreach ($profesionales as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $prof_filter == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="date" name="date" value="<?= htmlspecialchars($date_filter) ?>">

            <select name="status">
                <option value="">Todos los estados</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmado</option>
                <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
            </select>

            <button class="btn">Filtrar</button>
        </form>

        <!-- Tabla -->
        <table>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Profesional</th>
                <th>Paciente</th>
                <th>Motivo</th>
                <th>Estado</th>
            </tr>

            <?php foreach ($turnos as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['date']) ?></td>
                <td><?= htmlspecialchars(substr($t['time'], 0, 5)) ?></td>
                <td><?= htmlspecialchars($t['profesional']) ?></td>
                <td><?= htmlspecialchars($t['paciente']) ?></td>

                <!-- MOTIVO -->
                <td>
                    <?php if (!empty($t['motivo'])): ?>
                        <?= nl2br(htmlspecialchars($t['motivo'])) ?>
                    <?php else: ?>
                        <span style="color:#94a3b8;">—</span>
                    <?php endif; ?>
                </td>

                <td>
                    <?php
                    $s = $t['status'];

                    $class = $s==='confirmed'
                        ? 'badge-confirmed'
                        : ($s==='cancelled' ? 'badge-cancelled' : 'badge-pending');

                    $label = [
                        'confirmed' => 'Confirmado',
                        'pending'   => 'Pendiente',
                        'cancelled' => 'Cancelado'
                    ][$s] ?? $s;
                    ?>

                    <span class="badge <?= $class ?>"><?= $label ?></span>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($turnos)): ?>
            <tr><td colspan="6">No se encontraron turnos con los filtros seleccionados.</td></tr>
            <?php endif; ?>
        </table>
    </div>

</div>

</div>
</body>
</html>
