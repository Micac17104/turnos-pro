<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';

$user_id = $_GET['user_id'] ?? null;
$modo = $_GET['modo'] ?? null;

if (!$user_id) {
    die("Profesional no encontrado.");
}

// Datos del profesional
$stmt = $pdo->prepare("SELECT name, profession FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    die("Profesional no encontrado.");
}

// Horarios configurados
$stmt = $pdo->prepare("
    SELECT * FROM schedules 
    WHERE user_id = ? 
    ORDER BY day_of_week, start_time
");
$stmt->execute([$user_id]);
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Turnos reservados
$stmt = $pdo->prepare("
    SELECT date, time 
    FROM appointments 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$reservados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$reservados_map = [];
foreach ($reservados as $r) {
    $reservados_map[$r['date'] . ' ' . substr($r['time'], 0, 5)] = true;
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
                        "hora" => date("H:i", $t)
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
body { background:#f1f5f9; font-family:Arial; padding:40px; }
.container { max-width:800px; margin:auto; background:white; padding:30px; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.08); }

h2 { margin-bottom:10px; font-size:24px; font-weight:700; color:#0f172a; }
.sub { color:#475569; margin-bottom:20px; }

.btn-primary {
    background: linear-gradient(135deg, #22c55e, #0ea5e9);
    padding: 14px 22px;
    border-radius: 12px;
    color: white;
    text-decoration: none;
    font-weight: 600;
    display:block;
    text-align:center;
    margin-bottom:15px;
}

.btn-ghost {
    padding: 14px 22px;
    border-radius: 12px;
    border: 1px solid #94a3b8;
    color: #334155;
    text-decoration: none;
    font-weight: 600;
    display:block;
    text-align:center;
}

.days-nav { display:flex; gap:12px; overflow-x:auto; padding:10px 0; margin-bottom:20px; }
.day-tab { padding:14px 22px; background:white; border-radius:12px; cursor:pointer; border:2px solid #ddd; font-weight:600; white-space:nowrap; }
.day-tab.active { background:#0ea5e9; color:white; border-color:#0ea5e9; }

.horarios-dia { display:none; }
.horarios-dia.visible { display:block; }

.turnos-grid { display:flex; flex-wrap:wrap; gap:12px; }

.turno-btn {
    padding:12px 18px;
    background:#e8f7e8;
    border:1px solid #8fd48f;
    border-radius:12px;
    color:#2d7a2d;
    font-weight:600;
    text-decoration:none;
}

.turno-ocupado {
    padding:12px 18px;
    background:#f2f2f2;
    border-radius:12px;
    color:#999;
}
</style>
</head>
<body>

<div class="container">

    <h2>Sacar turno con <?= htmlspecialchars($pro['name']) ?></h2>
    <div class="sub"><?= htmlspecialchars($pro['profession'] ?? '') ?></div>

    <!-- MODO RÁPIDO / LOGIN -->
    <?php if (!$modo): ?>
        <a href="paciente-turnos.php?user_id=<?= $user_id ?>&modo=rapido" class="btn-primary">
            Sacar turno sin registrarme
        </a>

        <a href="login-paciente.php?user_id=<?= $user_id ?>" class="btn-ghost">
            Iniciar sesión o crear cuenta
        </a>

        <?php exit; ?>
    <?php endif; ?>

    <!-- NAV DE DÍAS -->
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

    <!-- HORARIOS -->
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

                    $clave = $t['fecha'] . ' ' . $t['hora'];
                    $ocupado = isset($reservados_map[$clave]);
                ?>

                    <?php if ($ocupado): ?>
                        <div class="turno-ocupado"><?= $t['hora'] ?></div>
                    <?php else: ?>
                        <a class="turno-btn"
                           href="datos-paciente.php?user_id=<?= $user_id ?>&fecha=<?= $t['fecha'] ?>&hora=<?= $t['hora'] ?>">
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