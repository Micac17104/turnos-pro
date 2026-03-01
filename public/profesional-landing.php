<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    die("Profesional no encontrado.");
}

// Traer datos del profesional
$stmt = $pdo->prepare("
    SELECT id, name, profession, public_description, specialties,
           accepts_insurance, insurance_list, profile_image_blob
    FROM users
    WHERE slug = ? AND account_type = 'professional'
");
$stmt->execute([$slug]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    die("Profesional no encontrado.");
}

$pro_id = $pro['id'];

// Traer horarios del profesional
$stmt = $pdo->prepare("
    SELECT day_of_week, start_time, end_time, slot_duration
    FROM schedules
    WHERE user_id = ?
    ORDER BY day_of_week, start_time
");
$stmt->execute([$pro_id]);
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
<title><?= htmlspecialchars($pro['name']) ?> - Profesional</title>

<style>
body { background:#f1f5f9; font-family:Arial; margin:0; padding:0; }
.container { max-width:900px; margin:40px auto; background:white; padding:30px; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.08); }

.header { display:flex; gap:20px; align-items:center; margin-bottom:30px; }
.header img { width:140px; height:140px; border-radius:20px; object-fit:cover; border:3px solid #e2e8f0; }

h1 { margin:0; font-size:28px; color:#0f172a; }
.prof { color:#475569; margin-top:4px; }

.section-title { font-size:20px; font-weight:700; margin-top:30px; margin-bottom:10px; color:#0f172a; }

.btn-primary {
    background: linear-gradient(135deg, #22c55e, #0ea5e9);
    padding: 14px 22px;
    border-radius: 12px;
    color: white;
    text-decoration: none;
    font-weight: 600;
    display:inline-block;
    margin-top:20px;
}
</style>
</head>
<body>

<div class="container">

    <div class="header">
        <?php if (!empty($pro['profile_image_blob'])): ?>
            <img src="data:image/jpeg;base64,<?= base64_encode($pro['profile_image_blob']) ?>">
        <?php else: ?>
            <img src="https://via.placeholder.com/140x140?text=Sin+Foto">
        <?php endif; ?>

        <div>
            <h1><?= htmlspecialchars($pro['name']) ?></h1>
            <div class="prof"><?= htmlspecialchars($pro['profession']) ?></div>
        </div>
    </div>

    <?php if (!empty($pro['public_description'])): ?>
        <div>
            <div class="section-title">Sobre mí</div>
            <p><?= nl2br(htmlspecialchars($pro['public_description'])) ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($pro['specialties'])): ?>
        <div>
            <div class="section-title">Especialidades</div>
            <p><?= nl2br(htmlspecialchars($pro['specialties'])) ?></p>
        </div>
    <?php endif; ?>

    <div>
        <div class="section-title">Horarios de atención</div>

        <?php if (empty($horarios)): ?>
            <p>Este profesional aún no cargó horarios.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($horarios as $h): ?>
                    <li>
                        <strong><?= $dias[$h['day_of_week']] ?>:</strong>
                        <?= substr($h['start_time'],0,5) ?> a <?= substr($h['end_time'],0,5) ?>
                        (cada <?= $h['slot_duration'] ?> min)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <a href="paciente-turno.php?user_id=<?= $pro_id ?>" class="btn-primary">
        Sacar turno
    </a>

</div>

</body>
</html>