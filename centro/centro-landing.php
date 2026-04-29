<?php
require __DIR__ . '/../config.php';

// El slug viene desde index.php
$slug = $slug_centro ?? null;

if (!$slug) {
    die("Centro no encontrado.");
}

// Obtener datos del centro
$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE slug = ? AND account_type = 'center'
");
$stmt->execute([$slug]);
$center = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$center) {
    die("Centro no encontrado.");
}

$center_id = $center['id'];

// Obtener profesionales del centro
$stmt = $pdo->prepare("
    SELECT id, name, profession, profile_image, public_description, specialties, slug
    FROM users
    WHERE parent_center_id = ? AND account_type = 'professional'
    ORDER BY name
");
$stmt->execute([$center_id]);
$profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dirección completa para el mapa
$direccion_completa = urlencode(
    trim(($center['address'] ?? '') . ' ' . ($center['city'] ?? '') . ' ' . ($center['province'] ?? ''))
);

if (empty(trim(urldecode($direccion_completa)))) {
    $direccion_completa = urlencode("Argentina");
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($center['name']) ?> – Centro Médico</title>

<style>
body { margin:0; font-family:Arial; background:#f8fafc; color:#0f172a; }
.header { background:white; padding:40px; text-align:center; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
.container { max-width:1000px; margin:40px auto; padding:20px; }
.card { background:white; padding:30px; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.06); margin-bottom:30px; }
h1 { margin:0; font-size:36px; }
h2 { margin-top:0; font-size:24px; }
.prof-card { display:flex; gap:20px; padding:20px 0; border-bottom:1px solid #e2e8f0; }
.prof-card:last-child { border-bottom:none; }
.prof-img { width:90px; height:90px; border-radius:100%; object-fit:cover; border:3px solid #e2e8f0; }
.center-img { width:160px; height:160px; border-radius:20px; object-fit:cover; border:4px solid #e2e8f0; margin-bottom:20px; }
.btn { display:inline-block; padding:10px 16px; background:#0ea5e9; color:white; border-radius:10px; text-decoration:none; font-weight:600; }
.btn:hover { opacity:0.9; }
.badge { display:inline-block; padding:6px 10px; background:#e2e8f0; border-radius:8px; margin-right:6px; font-size:13px; }
.map { width:100%; height:350px; border-radius:20px; border:0; margin-top:20px; }
</style>

</head>
<body>

<div class="header">

    <!-- FOTO DEL CENTRO -->
    <?php if (!empty($center['profile_image'])): ?>
        <img src="/uploads/<?= htmlspecialchars($center['profile_image']) ?>" class="center-img">
    <?php else: ?>
        <img src="/default-center.png" class="center-img">
    <?php endif; ?>

    <h1><?= htmlspecialchars($center['name']) ?></h1>
    <p style="font-size:18px; color:#475569;">
        <?= htmlspecialchars($center['city']) ?> – <?= htmlspecialchars($center['address']) ?>
    </p>
    <p style="font-size:16px;"><?= htmlspecialchars($center['phone']) ?></p>
</div>

<div class="container">

    <!-- SOBRE EL CENTRO -->
    <div class="card">
        <h2>Sobre el centro</h2>
        <p><?= nl2br(htmlspecialchars($center['description'] ?: 'Este centro aún no agregó una descripción.')) ?></p>

        <!-- MAPA -->
        <iframe
            class="map"
            loading="lazy"
            allowfullscreen
            src="https://www.google.com/maps/embed/v1/place?key=AIzaSyDNEW0j2lFJYO6_XZ9qtf4r8dl8M2RC5qI&q=<?= $direccion_completa ?>">
        </iframe>

        <p style="font-size:12px; color:#64748b; margin-top:8px;">
            *Si querés que el mapa funcione, agregá tu Google Maps API Key en el parámetro <strong>key=</strong>.
        </p>
    </div>

    <!-- PROFESIONALES -->
    <div class="card">
        <h2>Profesionales del centro</h2>

        <?php if (empty($profesionales)): ?>
            <p>Aún no hay profesionales cargados.</p>
        <?php endif; ?>

        <?php foreach ($profesionales as $p): ?>
            <div class="prof-card">

                <!-- FOTO -->
                <?php if ($p['profile_image']): ?>
                    <img src="/uploads/<?= htmlspecialchars($p['profile_image']) ?>" class="prof-img">
                <?php else: ?>
                    <img src="/default-avatar.png" class="prof-img">
                <?php endif; ?>

                <div style="flex:1;">
                    <strong style="font-size:18px;"><?= htmlspecialchars($p['name']) ?></strong><br>
                    <span style="color:#475569;"><?= htmlspecialchars($p['profession']) ?></span><br><br>

                    <!-- ESPECIALIDADES -->
                    <?php if ($p['specialties']): ?>
                        <?php foreach (explode(',', $p['specialties']) as $esp): ?>
                            <span class="badge"><?= htmlspecialchars(trim($esp)) ?></span>
                        <?php endforeach; ?>
                        <br><br>
                    <?php endif; ?>

                    <!-- DESCRIPCIÓN -->
                    <p style="color:#334155; font-size:14px;">
                        <?= nl2br(htmlspecialchars($p['public_description'] ?: '')) ?>
                    </p>

                    <!-- BOTÓN -->
                    <a class="btn" href="/<?= htmlspecialchars($p['slug']) ?>">Ver perfil</a>
                </div>

            </div>
        <?php endforeach; ?>

    </div>

</div>

</body>
</html>
