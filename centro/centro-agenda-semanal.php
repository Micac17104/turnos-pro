<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

// 🚨 BLOQUEO DE SUSCRIPCIÓN (middleware)
require __DIR__ . '/includes/check_subscription.php';
?>

$center_id = $_SESSION['user_id'];


// Fecha base (cualquier día dentro de la semana)
$base = $_GET['date'] ?? date('Y-m-d');
$base_time = strtotime($base);

// Calcular lunes y domingo de la semana
$lunes = date('Y-m-d', strtotime('monday this week', $base_time));
$domingo = date('Y-m-d', strtotime('sunday this week', $base_time));

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
    SELECT a.id, a.date, a.time, a.status, a.motivo,
           u.name AS profesional,
           u.id AS profesional_id,
           c.name AS paciente
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN clients c ON a.client_id = c.id
    WHERE a.date BETWEEN ? AND ?
";

$params = [$lunes, $domingo];

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

$query .= " ORDER BY a.date ASC, a.time ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar turnos por día y hora
$agenda = [];
foreach ($turnos as $t) {
    $agenda[$t['date']][$t['time']][] = $t;
}

// Colores por profesional
$colores = [
    "#0ea5e9", "#22c55e", "#f97316", "#a855f7",
    "#ef4444", "#14b8a6", "#6366f1", "#d946ef"
];
$color_map = [];
$i = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agenda semanal del centro</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:1200px;margin:0 auto;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
table{width:100%;border-collapse:collapse;}
th{background:#e2e8f0;padding:10px;text-align:center;}
td{border:1px solid #e2e8f0;vertical-align:top;height:80px;padding:4px;}
.slot{padding:6px;border-radius:8px;color:white;margin-bottom:4px;font-size:12px;}
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
        <h2>Agenda semanal</h2>

        <?php
        $prev = date('Y-m-d', strtotime('-7 days', strtotime($lunes)));
        $next = date('Y-m-d', strtotime('+7 days', strtotime($lunes)));
        ?>

        <a class="btn" href="?date=<?= $prev ?>&prof=<?= $prof_filter ?>">← Semana anterior</a>
        <a class="btn" href="?date=<?= $next ?>&prof=<?= $prof_filter ?>">Semana siguiente →</a>

        <br><br>

        <form method="GET" style="margin-bottom:15px;">
            <input type="hidden" name="date" value="<?= $lunes ?>">

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

        <table>
            <tr>
                <th>Hora</th>
                <?php for ($d = 0; $d < 7; $d++): ?>
                    <?php $dia = date('Y-m-d', strtotime("+$d days", strtotime($lunes))); ?>
                    <th>
                        <?php
                        $dias = [
                            'Mon' => 'Lun',
                            'Tue' => 'Mar',
                            'Wed' => 'Mié',
                            'Thu' => 'Jue',
                            'Fri' => 'Vie',
                            'Sat' => 'Sáb',
                            'Sun' => 'Dom'
                        ];
                        $diaSemana = $dias[date('D', strtotime($dia))];
                        ?>
                        <?= $diaSemana . " " . date('d/m', strtotime($dia)) ?>
                    </th>
                <?php endfor; ?>
            </tr>

            <?php for ($h = 8; $h <= 21; $h++): ?>
                <?php $hora = sprintf("%02d:00:00", $h); ?>
                <tr>
                    <td style="text-align:center;font-weight:bold;"><?= substr($hora, 0, 5) ?></td>

                    <?php for ($d = 0; $d < 7; $d++): ?>
                        <?php
                        $dia = date('Y-m-d', strtotime("+$d days", strtotime($lunes)));
                        $slots = $agenda[$dia][$hora] ?? [];
                        ?>
                        <td>
                            <?php foreach ($slots as $t): ?>

                                <?php
                                if (!isset($color_map[$t['profesional_id']])) {
                                    $color_map[$t['profesional_id']] = $colores[$i % count($colores)];
                                    $i++;
                                }
                                $color = $color_map[$t['profesional_id']];
                                ?>

                                <div class="slot" style="background: <?= $color ?>;">
                                    <?= htmlspecialchars($t['paciente']) ?><br>
                                    <small><?= htmlspecialchars($t['profesional']) ?></small>

                                    <?php if (!empty($t['motivo'])): ?>
                                        <br>
                                        <small style="font-size:10px;opacity:0.9;">
                                            <?= htmlspecialchars($t['motivo']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>

                            <?php endforeach; ?>
                        </td>
                    <?php endfor; ?>

                </tr>
            <?php endfor; ?>
        </table>

    </div>

</div>

</div>
</body>
</html>
