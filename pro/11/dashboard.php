<?php
session_start();
require __DIR__ . '/../../config.php';

// Detectar tenant real o template
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

// Verificar login
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// ===============================
// RECORDATORIOS AUTOMÁTICOS
// ===============================
$mañana = date("Y-m-d", strtotime("+1 day"));

$stmt = $pdo->prepare("
    SELECT a.id, a.date, a.time,
           c.name, c.phone, c.email
    FROM appointments a
    JOIN clients c ON a.client_id = c.id
    WHERE a.user_id = ?
      AND a.date = ?
      AND a.reminder_sent = 0
");
$stmt->execute([$user_id, $mañana]);
$turnos_recordatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($turnos_recordatorios as $t) {

    $mensaje = "Hola {$t['name']}, te recordamos tu turno de mañana ({$t['date']} a las {$t['time']}).";

    // WhatsApp
    if (!empty($t['phone'])) {
        $tel = preg_replace('/[^0-9]/', '', $t['phone']);
        $msg = urlencode($mensaje);
        $url = "https://api.whatsapp.com/send?phone=$tel&text=$msg";
        echo "<script>window.open('$url', '_blank');</script>";
    }

    // Email
    if (!empty($t['email'])) {
        @mail(
            $t['email'],
            "Recordatorio de turno",
            $mensaje,
            "From: notificaciones@turnospro.com"
        );
    }

    // Marcar como enviado
    $stmt2 = $pdo->prepare("UPDATE appointments SET reminder_sent = 1 WHERE id = ?");
    $stmt2->execute([$t['id']]);
}

// Datos del profesional
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Clientes
$stmt = $pdo->prepare("SELECT * FROM clients WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Turnos
$stmt2 = $pdo->prepare("
    SELECT appointments.*, clients.name AS client_name, clients.phone AS client_phone
    FROM appointments 
    JOIN clients ON appointments.client_id = clients.id
    WHERE appointments.user_id = ?
    ORDER BY date ASC, time ASC
");
$stmt2->execute([$user_id]);
$appointments = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel</title>

    <link rel="stylesheet" href="/turnos-pro/assets/style.css">

    <style>
        body {
            background:#f5f5f5;
        }

        .dashboard {
            display:flex;
            gap:25px;
            max-width:1100px;
            margin:40px auto;
        }

        /* COLUMNA PRINCIPAL */
        .main-column {
            flex:2;
            display:flex;
            flex-direction:column;
            gap:25px;
        }

        /* SIDEBAR */
        .side-column {
            flex:1;
            display:flex;
            flex-direction:column;
            gap:25px;
        }

        .card {
            background:white;
            padding:20px;
            border-radius:14px;
            box-shadow:0 10px 25px rgba(15,23,42,0.06);
            border:1px solid rgba(148,163,184,0.25);
        }

        .item {
            padding:12px 0;
            border-bottom:1px solid #e5e7eb;
        }

        .item:last-child {
            border-bottom:none;
        }

        .item-title {
            font-weight:600;
            color:#0f172a;
        }

        .item-meta {
            font-size:14px;
            color:#475569;
        }

        .btn-small {
            padding:6px 12px;
            border-radius:8px;
            font-size:14px;
        }

       .btn-primary {
    background: linear-gradient(135deg, #22c55e, #0ea5e9);
    padding: 10px 18px;
    border-radius: 999px;
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: 0.2s ease;
    display: inline-block;
}

        .btn-ghost {
    padding: 8px 14px;
    border-radius: 999px;
    border: 1px solid #94a3b8;
    color: #334155;
    text-decoration: none;
    font-size: 14px;
    transition: 0.2s ease;
    display: inline-block;
}

        .btn-big {
            display:block;
            width:100%;
            padding:12px;
            text-align:center;
            background:#0ea5e9;
            color:white;
            border-radius:10px;
            margin-top:10px;
            text-decoration:none;
        }
    </style>
</head>
<body>

<a href="/turnos-pro/logout.php" 
   class="btn-primary" 
   style="float:right; margin:20px 30px 0 0;">
    Cerrar sesión
</a>

<div class="dashboard">

    <!-- COLUMNA PRINCIPAL -->
    <div class="main-column">

        <!-- TURNOS -->
        <div class="card">
            <h2>📅 Turnos</h2>

            <?php if (empty($appointments)): ?>
                <p>No tenés turnos cargados.</p>
            <?php else: ?>
                <?php foreach ($appointments as $a): ?>
                    <?php
                    $mensaje = urlencode("Hola {$a['client_name']}, tu turno es el {$a['date']} a las {$a['time']}.");
                    $whatsapp = "https://wa.me/{$a['client_phone']}?text={$mensaje}";
                    ?>
                    <div class="item">
                        <div class="item-title"><?= htmlspecialchars($a['client_name']) ?></div>
                        <div class="item-meta"><?= $a['date'] ?> — <?= $a['time'] ?></div>
                        <a href="<?= $whatsapp ?>" target="_blank" class="btn-primary btn-small">WhatsApp</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- CLIENTES -->
        <div class="card">
            <h2>👤 Clientes</h2>

            <?php if (empty($clients)): ?>
                <p>No tenés clientes cargados.</p>
            <?php else: ?>
                <?php foreach ($clients as $c): ?>
                    <?php
                    $mensajeCliente = urlencode("Hola {$c['name']}, ¿cómo estás?");
                    $whatsappCliente = "https://wa.me/{$c['phone']}?text={$mensajeCliente}";
                    ?>
                    <div class="item">
                        <div class="item-title"><?= htmlspecialchars($c['name']) ?></div>
                        <div class="item-meta">Teléfono: <?= $c['phone'] ?></div>

                        <a href="<?= $whatsappCliente ?>" target="_blank" class="btn-primary btn-small">WhatsApp</a>
                        <a href="/turnos-pro/profiles/<?= $user_id ?>/paciente-historia.php?id=<?= $c['id'] ?>" class="btn-ghost btn-small">Historia Clínica</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- SIDEBAR -->
    <div class="side-column">

        <!-- AGREGAR CLIENTE -->
        <div class="card">
            <h2>➕ Cliente</h2>
            <form method="post" action="/turnos-pro/profiles/<?= $user_id ?>/save_client.php">
                <input type="text" name="name" placeholder="Nombre" required>
                <input type="text" name="phone" placeholder="Teléfono" required>
                <button class="btn-big">Guardar</button>
            </form>
        </div>

        <!-- AGREGAR TURNO -->
        <div class="card">
            <h2>➕ Turno</h2>
            <form method="post" action="/turnos-pro/profiles/<?= $user_id ?>/save_appointment.php">
                <select name="client_id" required>
                    <option value="">Seleccionar cliente</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="date" name="date" required>
                <input type="time" name="time" required>

                <button class="btn-big">Guardar</button>
            </form>
        </div>

        <!-- NOTIFICACIONES -->
        <div class="card">
            <h2>🔔 Notificaciones</h2>

            <form method="post" action="/turnos-pro/profiles/<?= $user_id ?>/update-notificaciones.php">
                <label><input type="checkbox" name="notify_whatsapp" value="1" <?= $user['notify_whatsapp'] ? 'checked' : '' ?>> WhatsApp</label><br>
                <label><input type="checkbox" name="notify_email" value="1" <?= $user['notify_email'] ? 'checked' : '' ?>> Email</label><br>

                <button class="btn-big">Guardar</button>
            </form>
        </div>

        <!-- CONFIGURACIÓN -->
        <div class="card">
            <h2>⚙️ Configuración</h2>
            <a href="/turnos-pro/profiles/<?= $user_id ?>/editar-perfil.php" class="btn-big">Editar perfil</a>
            <a href="/turnos-pro/profiles/<?= $user_id ?>/editar-horarios.php" class="btn-big">Horarios</a>
        </div>

    </div>

</div>

</body>

<a href="/turnos-pro/logout.php" 
   class="btn-primary" 
   style="float:right; margin:20px 30px 0 0;">
    Cerrar sesión
</a>
</html>