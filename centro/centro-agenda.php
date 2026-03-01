<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';

// Fecha seleccionada
$fecha = $_GET['date'] ?? date('Y-m-d');

// Profesional seleccionado
$prof_filter = $_GET['prof'] ?? '';

// Obtener profesionales del centro
$stmt = $pdo->prepare("
    SELECT id, name
    FROM users
    WHERE account_type='professional'
    AND parent_center_id=?
    ORDER BY name
");
$stmt->execute([$center_id]);
$profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si appointments.center_id existe
$appointments_has_center = false;
$checkA = $pdo->query("SHOW COLUMNS FROM appointments LIKE 'center_id'");
if ($checkA->fetch()) {
    $appointments_has_center = true;
}

// Verificar si clients.center_id existe
$clients_has_center = false;
$checkC = $pdo->query("SHOW COLUMNS FROM clients LIKE 'center_id'");
if ($checkC->fetch()) {
    $clients_has_center = true;
}

// Base query
$query = "
    SELECT a.id, a.time, a.status,
           u.name AS profesional,
           c.name AS paciente,
           u.id AS profesional_id
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN clients c ON a.client_id = c.id
    WHERE a.date = ?
";

$params = [$fecha];

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

$query .= " ORDER BY a.time ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Colores por profesional
$colores = [
    "#0ea5e9", "#22c55e", "#f97316", "#a855f7",
    "#ef4444", "#14b8a6", "#6366f1", "#d946ef"
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agenda del centro</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:1100px;margin:0 auto;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
.slot{padding:12px;border-radius:12px;margin-bottom:10px;color:white;}
.badge{padding:4px 10px;border-radius:999px;font-size:12px;background:white;color:#0f172a;margin-left:10px;}
input,select{padding:8px;border-radius:8px;border:1px solid #cbd5e1;margin-right:10px;}
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
        <h2>Agenda del centro</h2>

        <!-- Filtros -->
        <form method="GET" style="margin-bottom:15px;">
            <input type="date" name="date" value="<?= htmlspecialchars($fecha) ?>">

            <select name="prof">
                <option value="">Todos los profesionales</option>
                <?php foreach ($profesionales as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $prof_filter == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="btn">Filtrar</button>
        </form>

        <!-- Turnos del día -->
        <?php if (empty($turnos)): ?>
            <p>No hay turnos para esta fecha.</p>
        <?php endif; ?>

        <?php
        $color_map = [];
        $i = 0;
        ?>

        <?php foreach ($turnos as $t): ?>

            <?php
            if (!isset($color_map[$t['profesional_id']])) {
                $color_map[$t['profesional_id']] = $colores[$i % count($colores)];
                $i++;
            }
            $color = $color_map[$t['profesional_id']];
            ?>

            <div class="slot" style="background: <?= $color ?>;">
                <strong><?= substr($t['time'], 0, 5) ?></strong>
                <span class="badge"><?= htmlspecialchars($t['status']) ?></span>
                <br>
                <strong>Paciente:</strong> <?= htmlspecialchars($t['paciente']) ?><br>
                <strong>Profesional:</strong> <?= htmlspecialchars($t['profesional']) ?>
            </div>

        <?php endforeach; ?>

    </div>

</div>

</div>
</body>
</html>