<?php
require __DIR__ . '/../config.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    die("Profesional no encontrado.");
}

// Obtener datos del profesional
$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE slug = ? AND account_type = 'professional'
");
$stmt->execute([$slug]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    die("Profesional no encontrado.");
}

$user_id = $pro['id'];

// Obtener horarios configurados
$stmt = $pdo->prepare("
    SELECT *
    FROM schedules
    WHERE user_id = ?
    ORDER BY day_of_week, start_time
");
$stmt->execute([$user_id]);
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener centro (si pertenece a uno)
$center = null;
if ($pro['parent_center_id']) {
    $stmt = $pdo->prepare("SELECT name, slug FROM users WHERE id = ?");
    $stmt->execute([$pro['parent_center_id']]);
    $center = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($pro['name']) ?> – Profesional</title>

<style>
body { margin:0; font-family:Arial; background:#f8fafc; color:#0f172a; }
.container { max-width:900px; margin:40px auto; padding:20px; }
.card { background:white; padding:30px; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.06); margin-bottom:30px; }
h1 { margin:0; font-size:32px; }
h2 { margin-top:0; font-size:22px; }
.profile-img { width:140px; height:140px; border-radius:100%; object-fit:cover; border:4px solid #e2e8f0; }
.btn { display:inline-block; padding:12px 20px; background:#0ea5e9; color:white; border-radius:12px; text-decoration:none; font-weight:600; }
.btn:hover { opacity:0.9; }
.badge { display:inline-block; padding:6px 12px; background:#e2e8f0; border-radius:8px; margin-right:6px; font-size:13px; }
</style>

</head>
<body>

<div class="container">

    <div class="card" style="text-align:center;">
        <?php if ($pro['profile_image']): ?>
            <img src="/uploads/<?= htmlspecialchars($pro['profile_image']) ?>" class="profile-img">
        <?php else: ?>
            <img src="/default-avatar.png" class="profile-img">
        <?php endif; ?>

        <h1><?= htmlspecialchars($pro['name']) ?></h1>
        <p style="font-size:18px; color:#475569;"><?= htmlspecialchars($pro['profession']) ?></p>

        <?php if ($center): ?>
            <p style="margin-top:10px;">
                Atiende en:
                <a href="/centro/<?= htmlspecialchars($center['slug']) ?>" style="color:#0ea5e9;">
                    <?= htmlspecialchars($center['name']) ?>
                </a>
            </p>
        <?php endif; ?>

        <a class="btn" href="/public/profesional.php?user_id=<?= $user_id ?>&modo=rapido">
            Sacar turno
        </a>
    </div>

    <div class="card">
        <h2>Sobre mí</h2>
        <p><?= nl2br(htmlspecialchars($pro['public_description'] ?: 'Este profesional aún no agregó una descripción.')) ?></p>
    </div>

    <?php if ($pro['specialties']): ?>
    <div class="card">
        <h2>Especialidades</h2>
        <?php foreach (explode(',', $pro['specialties']) as $esp): ?>
            <span class="badge"><?= htmlspecialchars(trim($esp)) ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($pro['accepts_insurance']): ?>
    <div class="card">
        <h2>Obras sociales</h2>
        <?php foreach (explode(',', $pro['insurance_list']) as $os): ?>
            <span class="badge"><?= htmlspecialchars(trim($os)) ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <h2>Ubicación</h2>
        <p>
            <?= htmlspecialchars($pro['address']) ?><br>
            <?= htmlspecialchars($pro['city']) ?>, <?= htmlspecialchars($pro['province']) ?>
        </p>
    </div>

</div>

</body>
</html>