<?php 
require __DIR__ . '/includes/auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Planes</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div style="margin-left:260px; padding:24px;">
    <h2>Elegí tu plan</h2>

    <div class="plan-card">
        <h3>Básico</h3>
        <p>Ideal para profesionales individuales.</p>
        <a href="contratar.php?plan=basico" class="btn">Contratar</a>
    </div>

    <div class="plan-card">
        <h3>Pro</h3>
        <p>Para centros pequeños con varios profesionales.</p>
        <a href="contratar.php?plan=pro" class="btn">Contratar</a>
    </div>

    <div class="plan-card">
        <h3>Premium</h3>
        <p>Para centros grandes con automatizaciones.</p>
        <a href="contratar.php?plan=premium" class="btn">Contratar</a>
    </div>
</div>

</body>
</html>
