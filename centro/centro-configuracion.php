<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';

// Cargamos datos del centro desde users
$stmt = $pdo->prepare("
    SELECT name, email, phone, city, address, description, slug
    FROM users
    WHERE id = ? AND account_type = 'center'
");
$stmt->execute([$center_id]);
$center = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$center) {
    die("Centro no encontrado.");
}

$errors = [];
$success = "";

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $slug = trim($_POST['slug'] ?? '');

    if ($name === '') {
        $errors[] = "El nombre del centro es obligatorio.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido.";
    }

    // Validar email único (excepto el propio)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $center_id]);
    if ($stmt->fetch()) {
        $errors[] = "Ese email ya está en uso por otra cuenta.";
    }

    // Validar slug único (excepto el propio)
    if ($slug === '') {
        $errors[] = "El slug público es obligatorio.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $center_id]);
        if ($stmt->fetch()) {
            $errors[] = "Ese slug ya está en uso. Elegí otro.";
        }
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE users SET
                name = ?, email = ?, phone = ?, city = ?, address = ?,
                description = ?, slug = ?
            WHERE id = ? AND account_type = 'center'
        ");

        $stmt->execute([
            $name,
            $email,
            $phone,
            $city,
            $address,
            $description,
            $slug,
            $center_id
        ]);

        $success = "Datos del centro actualizados correctamente.";

        // Refrescar datos
        $center = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'city' => $city,
            'address' => $address,
            'description' => $description,
            'slug' => $slug
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Configuración del centro</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:800px;margin:0 auto;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input, textarea{width:100%;padding:10px;margin:6px 0 14px;border-radius:10px;border:1px solid #cbd5e1;font-size:14px;}
button{padding:12px 20px;background:#0ea5e9;color:white;border:none;border-radius:12px;font-weight:600;cursor:pointer;}
button:hover{opacity:0.9;}
.error{color:#b00020;margin-bottom:10px;}
.success{color:#22c55e;margin-bottom:10px;}
a{color:#0ea5e9;text-decoration:none;font-size:14px;}
.label{font-size:13px;font-weight:bold;color:#475569;margin-bottom:2px;display:block;}
.small{font-size:12px;color:#64748b;}
</style>
</head>
<body>

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
        <h2>Configuración del centro</h2>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <form method="post">

            <label class="label">Nombre del centro</label>
            <input name="name" value="<?= htmlspecialchars($center['name']) ?>" required>

            <label class="label">Email de contacto</label>
            <input name="email" type="email" value="<?= htmlspecialchars($center['email']) ?>" required>

            <label class="label">Teléfono</label>
            <input name="phone" value="<?= htmlspecialchars($center['phone'] ?? '') ?>">

            <label class="label">Ciudad</label>
            <input name="city" value="<?= htmlspecialchars($center['city'] ?? '') ?>">

            <label class="label">Dirección</label>
            <input name="address" value="<?= htmlspecialchars($center['address'] ?? '') ?>">

            <label class="label">Descripción pública</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($center['description'] ?? '') ?></textarea>

            <label class="label">Slug público del centro</label>
            <input name="slug" value="<?= htmlspecialchars($center['slug'] ?? '') ?>" required>
            <div class="small">
                Esto define la URL pública del centro. Ejemplo:  
                <code>tusitio.com/centro/<?= htmlspecialchars($center['slug'] ?: 'mi-centro') ?></code>
            </div>

            <br>
            <button>Guardar cambios</button>
        </form>
    </div>

</div>

</body>
</html>