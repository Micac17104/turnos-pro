<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../auth/mailer.php'; // ← IMPORTANTE

// Turnos de mañana
$stmt = $pdo->prepare("
    SELECT 
        a.id,
        a.date,
        a.time,
        c.name AS paciente,
        c.phone AS paciente_phone,
        c.email AS paciente_email,
        u.name AS profesional
    FROM appointments a
    JOIN clients c ON c.id = a.client_id
    JOIN users u ON u.id = a.user_id
    WHERE a.center_id = ?
      AND a.date = CURDATE() + INTERVAL 1 DAY
    ORDER BY a.time
");
$stmt->execute([$center_id]);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recordatorios</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:900px;margin:0 auto;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
table{width:100%;border-collapse:collapse;font-size:14px;}
th,td{padding:8px 6px;border-bottom:1px solid #e5e7eb;text-align:left;}
.btn-wsp{padding:6px 10px;background:#25D366;color:white;border-radius:8px;text-decoration:none;font-size:13px;}
.btn-email{padding:6px 10px;background:#0ea5e9;color:white;border-radius:8px;text-decoration:none;font-size:13px;border:none;cursor:pointer;}
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
        <h2>Recordatorios de mañana</h2>

        <table>
            <tr>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Profesional</th>
                <th>Acciones</th>
            </tr>

            <?php foreach ($turnos as $t): 
                $msg = urlencode(
                    "Hola {$t['paciente']}, te recordamos tu turno mañana a las " .
                    substr($t['time'],0,5) . " con {$t['profesional']}."
                );
                $phone = preg_replace('/\D/', '', $t['paciente_phone']);

                // Email body
                $emailBody = "
                    <h2>Recordatorio de turno</h2>
                    <p>Hola {$t['paciente']}, te recordamos tu turno mañana a las 
                    <strong>" . substr($t['time'],0,5) . "</strong> con 
                    <strong>{$t['profesional']}</strong>.</p>
                ";
            ?>
            <tr>
                <td><?= substr($t['time'],0,5) ?></td>
                <td><?= htmlspecialchars($t['paciente']) ?></td>
                <td><?= htmlspecialchars($t['profesional']) ?></td>
                <td>

                    <!-- WhatsApp -->
                    <?php if ($phone): ?>
                        <a class="btn-wsp" target="_blank"
                           href="https://wa.me/<?= $phone ?>?text=<?= $msg ?>">
                           WhatsApp
                        </a>
                    <?php else: ?>
                        <span style="color:#b91c1c;">Sin teléfono</span>
                    <?php endif; ?>

                    <!-- Email -->
                    <?php if (!empty($t['paciente_email'])): ?>
                        <form method="POST" action="send-reminder.php" style="display:inline;">
    <input type="hidden" name="email" value="<?= $t['paciente_email'] ?>">
    <input type="hidden" name="body" value="<?= htmlspecialchars($emailBody) ?>">
    <button class="btn-email">Email</button>
</form>


                    <?php else: ?>
                        <span style="color:#b91c1c; margin-left:8px;">Sin email</span>
                    <?php endif; ?>

                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($turnos)): ?>
            <tr><td colspan="4">No hay turnos para mañana.</td></tr>
            <?php endif; ?>
        </table>
    </div>

</div>

</div>
</body>
</html>
