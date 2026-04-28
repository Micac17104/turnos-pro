<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id'];

$search = trim($_GET['search'] ?? '');

// Buscar pacientes SOLO del centro
if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT id, name, email, phone, dni
        FROM clients
        WHERE center_id = ?
        AND (name LIKE ? OR email LIKE ? OR dni LIKE ?)
        ORDER BY name
    ");
    $like = "%$search%";
    $stmt->execute([$center_id, $like, $like, $like]);
} else {
    $stmt = $pdo->prepare("
        SELECT id, name, email, phone, dni
        FROM clients
        WHERE center_id = ?
        ORDER BY name
    ");
    $stmt->execute([$center_id]);
}

$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pacientes del centro</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:1100px;margin:0 auto;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input{padding:10px;border-radius:10px;border:1px solid #cbd5e1;width:250px;}
.btn{padding:6px 12px;border-radius:999px;background:#0ea5e9;color:white;text-decoration:none;font-size:12px;}
.btn-green{background:#22c55e;}
table{width:100%;border-collapse:collapse;font-size:14px;margin-top:15px;}
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

    <div class="card">
        <h2>Pacientes del centro</h2>

        <form method="GET" style="margin-bottom:15px;">
            <input type="text" name="search" placeholder="Buscar por nombre, email o DNI" value="<?= htmlspecialchars($search) ?>">
            <button class="btn">Buscar</button>
            <a href="centro-pacientes-nuevo.php" class="btn btn-green">+ Nuevo paciente</a>
        </form>

        <table>
            <tr>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>

            <?php foreach ($pacientes as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['dni']) ?></td>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td><?= htmlspecialchars($p['phone']) ?></td>

                <td style="white-space: nowrap;">
                    <!-- Ver historial de turnos -->
                    <a href="centro-paciente-ver.php?id=<?= $p['id'] ?>" 
                       class="btn" 
                       style="background:#0ea5e9;">
                       Ver historial
                    </a>

                    <!-- Ver historia clínica -->
                    <a href="paciente-historia.php?id=<?= $p['id'] ?>" 
                       class="btn" 
                       style="background:#6366f1;">
                       Ver historia clínica
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($pacientes)): ?>
            <tr><td colspan="5">No se encontraron pacientes.</td></tr>
            <?php endif; ?>
        </table>
    </div>

</div>

</div>
</body>
</html>
