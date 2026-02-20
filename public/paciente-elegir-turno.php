<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';

// Verificar login del paciente
if (!isset($_SESSION['paciente_id'])) {
    header("Location: login-paciente.php");
    exit;
}

$paciente_id = $_SESSION['paciente_id'];

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    die("Profesional no encontrado.");
}

// Datos del profesional
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    die("Profesional no encontrado.");
}

// Horarios configurados
$stmt = $pdo->prepare("SELECT * FROM schedules WHERE user_id = ? ORDER BY day_of_week, start_time");
$stmt->execute([$user_id]);
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Turnos reservados (solo los NO cancelados)
$stmt = $pdo->prepare("
    SELECT date, time 
    FROM appointments 
    WHERE user_id = ? 
    AND status IN ('confirmed', 'pending')
");
$stmt->execute([$user_id]);
$reservados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$reservados_map = [];
foreach ($reservados as $r) {
    $hora_normalizada = substr($r['time'], 0, 5);
    $reservados_map[$r['date'] . ' ' . $hora_normalizada] = true;
}

// Generar turnos automáticos
function generarTurnos($horarios) {
    $turnos = [];
    $hoy = date("Y-m-d");
    $hasta = date("Y-m-d", strtotime("+7 days"));

    for ($f = strtotime($hoy); $f <= strtotime($hasta); $f += 86400) {
        $dia = date("N", $f);

        foreach ($horarios as $h) {
            if ($h['day_of_week'] == $dia) {

                $inicio = strtotime(date("Y-m-d", $f) . " " . $h['start_time']);
                $fin = strtotime(date("Y-m-d", $f) . " " . $h['end_time']);
                $dur = $h['slot_duration'] * 60;

                for ($t = $inicio; $t < $fin; $t += $dur) {
                    $turnos[] = [
                        "fecha" => date("Y-m-d", $f),
                        "hora"  => date("H:i", $t)
                    ];
                }
            }
        }
    }

    return $turnos;
}

$turnos = generarTurnos($horarios);

$dias = [
    1 => "Lunes",
    2 => "Martes",
    3 => "Miércoles",
    4 => "Jueves",
    5 => "Viernes",
    6 => "Sábado",
    7 => "Domingo"
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sacar turno con <?= htmlspecialchars($pro['name']) ?></title>

    <style>
        body { background:#f5f5f5; font-family:Arial; }
        .container { max-width:700px; margin:40px auto; background:white; padding:25px; border-radius:14px; }
        h2 { color:#0f172a; font-weight:700; margin-bottom:20px; }

        .days-nav { display:flex; gap:12px; overflow-x:auto; padding:10px 0; }
        .day-tab { padding:14px 22px; background:white; border-radius:12px; cursor:pointer; border:2px solid #ddd; font-weight:600; }
        .day-tab.active { background:#0ea5e9; color:white; border-color:#0ea5e9; }

        .horarios-dia { display:none; margin-top:25px; }
        .horarios-dia.visible { display:block; }

        .turnos-grid { display:flex; flex-wrap:wrap; gap:12px; }
        .turno-btn { padding:12px 18px; background:#e8f7e8; border:1px solid #8fd48f; border-radius:12px; color:#2d7a2d; font-weight:600; text-decoration:none; }
        .turno-ocupado { padding:12px 18px; background:#f2f2f2; border-radius:12px; color:#999; text-decoration:line-through; }
        .back { display:block; margin-top:20px; text-align:center; color:#0ea5e9; text-decoration:none; }
    </style>
</head>
<body>

<div class="container">

    <h2>Sacar turno con <?= htmlspecialchars($pro['name']) ?></h2>

    <div class="days-nav">
        <?php
        $dias_unicos = [];
        foreach ($turnos as $t) {
            $dias_unicos[$t['fecha']] =
                $dias[date("N", strtotime($t['fecha']))] . " " .
                date("d/m", strtotime($t['fecha']));
        }

        $i = 0;
        foreach ($dias_unicos as $fecha => $label):
        ?>
            <div class="day-tab <?= $i === 0 ? 'active' : '' ?>" data-fecha="<?= $fecha ?>">
                <?= $label ?>
            </div>
        <?php
        $i++;
        endforeach;
        ?>
    </div>

    <div id="horarios-container">
        <?php
        $i = 0;
        foreach ($dias_unicos as $fecha => $label):
        ?>
            <div class="horarios-dia <?= $i === 0 ? 'visible' : '' ?>" data-fecha="<?= $fecha ?>">

                <div class="turnos-grid">
                <?php
                foreach ($turnos as $t):
                    if ($t['fecha'] !== $fecha) continue;

                    $hora_normalizada = substr($t['hora'], 0, 5);
                    $clave = $t['fecha'] . ' ' . $hora_normalizada;
                    $ocupado = isset($reservados_map[$clave]);
                ?>

                    <?php if ($ocupado): ?>
                        <div class="turno-ocupado"><?= $t['hora'] ?></div>
                    <?php else: ?>
                        <!-- RUTA CORRECTA -->
                        <a class="turno-btn"
                           href="paciente-confirmar-turno.php?user_id=<?= $user_id ?>&fecha=<?= $t['fecha'] ?>&hora=<?= $t['hora'] ?>">
                           <?= $t['hora'] ?>
                        </a>
                    <?php endif; ?>

                <?php endforeach; ?>
                </div>

            </div>
        <?php
        $i++;
        endforeach;
        ?>
    </div>

    <!-- RUTA CORRECTA -->
    <a class="back" href="paciente-sacar-turno.php">← Elegir otro profesional</a>
</div>

<script>
document.querySelectorAll(".day-tab").forEach(tab => {
    tab.addEventListener("click", () => {

        document.querySelectorAll(".day-tab").forEach(t => t.classList.remove("active"));
        tab.classList.add("active");

        let fecha = tab.dataset.fecha;

        document.querySelectorAll(".horarios-dia").forEach(div => {
            div.classList.remove("visible");
        });

        document.querySelector('.horarios-dia[data-fecha="' + fecha + '"]').classList.add("visible");
    });
});
</script>

</body>
</html>