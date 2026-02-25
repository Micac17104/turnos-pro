<?php
require __DIR__ . '/../config.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    die("Centro no encontrado.");
}

// Obtener datos del centro
$stmt = $pdo->prepare("
    SELECT id, name, email, phone, city, address, description
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
    SELECT name, profession, slug
    FROM users
    WHERE parent_center_id = ? AND account_type = 'professional'
    ORDER BY name
");
$stmt->execute([$center_id]);
$profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($center['name']) ?> – Centro Médico</title>
<style>
body{margin:0;font-family:Arial;background:#f8fafc;color:#0f172a;}
.header{background:white;padding:30px;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,0.05);}
.container{max-width:900px;margin:40px auto;padding:20px;}
.card{background:white;padding:20px;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.06);margin-bottom:30px;}
h1{margin:0;font-size:32px;}
h2{margin-top:0;}
.prof{padding:12px 0;border-bottom:1px solid #e2e8f0;}
.prof:last-child{border-bottom:none;}
a.btn{display:inline-block;padding:10px 16px;background:#0ea5e9;color:white;border-radius:10px;text-decoration:none;margin-top:10px;}
a.btn:hover{opacity:0.9;}
</style>
</head>
<body>

<div class="header">
    <h1><?= htmlspecialchars($center['name']) ?></h1>
    <p><?= htmlspecialchars($center['city']) ?> – <?= htmlspecialchars($center['address']) ?></p>
    <p><?= htmlspecialchars($center['phone']) ?></p>
</div>

<div class="container">

    <div class="card">
        <h2>Sobre el centro</h2>
        <p><?= nl2br(htmlspecialchars($center['description'] ?: 'Este centro aún no agregó una descripción.')) ?></p>
    </div>

    <div class="card">
        <h2>Profesionales del centro</h2>

        <?php if (empty($profesionales)): ?>
            <p>Aún no hay profesionales cargados.</p>
        <?php endif; ?>

        <?php foreach ($profesionales as $p): ?>
            <div class="prof">
                <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                <?= htmlspecialchars($p['profession']) ?><br>
                <a class="btn" href="/<?= htmlspecialchars($p['slug']) ?>">Ver perfil</a>
            </div>
        <?php endforeach; ?>
    </div>

</div>

</body>
</html>