<?php
session_start();
require __DIR__ . '/../../config.php';

// Detectar tenant
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

// Verificar login
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Turnos totales del mes actual
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM appointments
    WHERE user_id = ?
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$turnos_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Pacientes nuevos del mes
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM clients
    WHERE user_id = ?
      AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$pacientes_nuevos = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Evoluciones del mes
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM clinical_records
    WHERE user_id = ?
      AND DATE_FORMAT(fecha, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$evoluciones_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM appointments
    WHERE user_id = ?
      AND payment_status = 'pagado'
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$pagos_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Estadísticas</title>
<link rel="stylesheet" href="/turnos-pro/assets/style.css">
<style>
    body { background:#f5f5f5; }
    .dashboard {
        max-width:900px;
        margin:40px auto;
    }
    .card {
        background:white;
        padding:20px;
        border-radius:14px;
        box-shadow:0 10px 25px rgba(15,23,42,0.06);
        border:1px solid rgba(148,163,184,0.25);
        margin-bottom:20px;
    }
    .stats-grid {
        display:grid;
        grid-template-columns:repeat(3, 1fr);
        gap:20px;
    }
    .stat {
        padding:16px;
        border-radius:12px;
        background:#f8fafc;
        text-align:center;
    }
    .stat-label {
        font-size:14px;
        color:#64748b;
        margin-bottom:6px;
    }
    .stat-value {
        font-size:24px;
        font-weight:700;
        color:#0f172a;
    }
    .btn-ghost {
        background:#e2e8f0;
        padding:8px 14px;
        border-radius:8px;
        text-decoration:none;
        color:#334155;
        display:inline-block;
        margin-bottom:15px;
    }
</style>
</head>
<body>

<div class="dashboard">
    <a href="/turnos-pro/profiles/<?= $user_id ?>/dashboard.php" class="btn-ghost">← Volver al panel</a>

    <div class="card">
        <h2>📊 Estadísticas del mes</h2>
        <div class="stats-grid">
            <div class="stat">
                <div class="stat-label">Turnos del mes</div>
                <div class="stat-value"><?= $turnos_mes ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Pacientes nuevos</div>
                <div class="stat-value"><?= $pacientes_nuevos ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Evoluciones cargadas</div>
                <div class="stat-value"><?= $evoluciones_mes ?></div>
            </div>

            <div class="stat">
    <div class="stat-label">Turnos pagados</div>
    <div class="stat-value"><?= $pagos_mes ?></div>
</div>
        </div>
    </div>
</div>

</body>
</html>