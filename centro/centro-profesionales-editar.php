<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';

$center_id = $_SESSION['user_id'];
$prof_id = $_GET['id'] ?? null;

if (!$prof_id) {
    header("Location: centro-profesionales.php");
    exit;
}

// Obtener datos actuales del profesional
$stmt = $pdo->prepare("
    SELECT id, name, email, profession, phone, city, description, specialties,
           accepts_insurance, insurance_list, slug,
           video_link   -- 🔥 AGREGADO: video_link
    FROM users
    WHERE id = ? AND parent_center_id = ? AND account_type='professional'
");
$stmt->execute([$prof_id, $center_id]);
$prof = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prof) {
    die("Profesional no encontrado.");
}

$errors = [];
$success = "";

// Procesar formulario
if ($_POST) {

    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $profession = trim($_POST['profession'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $specialties = trim($_POST['specialties'] ?? '');
    $accepts_insurance = isset($_POST['accepts_insurance']) ? 1 : 0;

    // 🔥 AGREGADO: video_link
    $video_link = trim($_POST['video_link'] ?? '');

    // Lista de obras sociales seleccionadas
    $insurance_list = $_POST['insurance_list'] ?? [];

    if (isset($_POST['insurance_other']) && $_POST['insurance_other'] !== '') {
        $insurance_list[] = trim($_POST['insurance_other']);
    }

    $slug = trim($_POST['slug'] ?? '');

    if ($name === '' || $profession === '') {
        $errors[] = "Nombre y profesión son obligatorios.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido.";
    }

    // Validar email único (excepto el propio)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $prof_id]);
    if ($stmt->fetch()) {
        $errors[] = "Ese email ya está registrado por otro profesional.";
    }

    // Validar slug único
    $stmt = $pdo->prepare("SELECT id FROM users WHERE slug = ? AND id != ?");
    $stmt->execute([$slug, $prof_id]);
    if ($stmt->fetch()) {
        $errors[] = "Ese slug ya está en uso. Elegí otro.";
    }

    $insurance_json = json_encode($insurance_list);

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE users SET
                name=?, email=?, profession=?, phone=?, city=?, 
                description=?, specialties=?, accepts_insurance=?, 
                insurance_list=?, slug=?, video_link=?   -- 🔥 AGREGADO
            WHERE id=? AND parent_center_id=?
        ");

        $stmt->execute([
            $name, $email, $profession, $phone, $city,
            $description, $specialties, $accepts_insurance,
            $insurance_json, $slug, $video_link,   // 🔥 AGREGADO
            $prof_id, $center_id
        ]);

        $success = "Datos actualizados correctamente.";
    }
}

// Recargar datos actualizados
$stmt = $pdo->prepare("
    SELECT id, name, email, profession, phone, city, description, specialties,
           accepts_insurance, insurance_list, slug,
           video_link   -- 🔥 AGREGADO
    FROM users
    WHERE id = ? AND parent_center_id = ? AND account_type='professional'
");
$stmt->execute([$prof_id, $center_id]);
$prof = $stmt->fetch(PDO::FETCH_ASSOC);

$insurance_list = $prof['insurance_list'] ? json_decode($prof['insurance_list'], true) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar profesional</title>
<style>
body{background:#f1f5f9;font-family:Arial;display:flex;justify-content:center;align-items:center;padding:40px;}
.box{background:white;padding:40px;border-radius:20px;width:480px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input, textarea, select{width:100%;padding:12px;margin:8px 0;border-radius:10px;border:1px solid #cbd5e1;}
button{width:100%;padding:14px;background:#0ea5e9;color:white;border:none;border-radius:12px;font-weight:600;cursor:pointer;}
button:hover{opacity:0.9;}
.error{color:#b00020;margin-bottom:10px;}
.success{color:#22c55e;margin-bottom:10px;}
a{color:#0ea5e9;text-decoration:none;font-size:14px;}
</style>
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div style="margin-left:260px; padding:24px;">

<div class="box">
    <h2>Editar profesional</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post">

        <input name="name" value="<?= htmlspecialchars($prof['name']) ?>" placeholder="Nombre y apellido" required>

        <input name="email" type="email" value="<?= htmlspecialchars($prof['email']) ?>" placeholder="Email" required>

        <input name="profession" value="<?= htmlspecialchars($prof['profession']) ?>" placeholder="Profesión" required>

        <input name="phone" value="<?= htmlspecialchars($prof['phone']) ?>" placeholder="Teléfono">

        <input name="city" value="<?= htmlspecialchars($prof['city']) ?>" placeholder="Ciudad">

        <textarea name="description" placeholder="Descripción pública"><?= htmlspecialchars($prof['description']) ?></textarea>

        <textarea name="specialties" placeholder="Especialidades (separadas por coma)"><?= htmlspecialchars($prof['specialties']) ?></textarea>

        <!-- 🔥 CAMPO: Link de videollamada -->
        <input name="video_link"
               value="<?= htmlspecialchars($prof['video_link']) ?>"
               placeholder="Link de videollamada (Meet, Zoom, etc.)">

        <label>
            <input type="checkbox" name="accepts_insurance" id="accepts_insurance" <?= $prof['accepts_insurance'] ? 'checked' : '' ?> onclick="toggleInsurance()">
            Acepta obra social
        </label>

        <div id="insurance_block" style="display: <?= $prof['accepts_insurance'] ? 'block' : 'none' ?>; margin-top:10px;">

            <label>Obras sociales (Ctrl + click para varias):</label>
            <select name="insurance_list[]" multiple size="5" id="insurance_select">
                <?php
                $all_insurances = ["OSDE", "Swiss Medical", "Galeno", "Medifé", "IOMA", "otra"];
                foreach ($all_insurances as $i):
                ?>
                    <option value="<?= $i ?>" <?= in_array($i, $insurance_list) ? 'selected' : '' ?>>
                        <?= $i ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php
            $other_value = "";
            foreach ($insurance_list as $i) {
                if (!in_array($i, ["OSDE", "Swiss Medical", "Galeno", "Medifé", "IOMA"])) {
                    $other_value = $i;
                }
            }
            ?>

            <input name="insurance_other" id="insurance_other" placeholder="Escribí otra obra social"
                   value="<?= htmlspecialchars($other_value) ?>"
                   style="display: <?= $other_value ? 'block' : 'none' ?>;">
        </div>

        <input name="slug" value="<?= htmlspecialchars($prof['slug']) ?>" placeholder="Slug público (ej: dr-juan-perez)" required>

        <button>Guardar cambios</button>
    </form>

    <p style="margin-top:10px;"><a href="centro-profesional-ver.php?id=<?= $prof['id'] ?>">Volver</a></p>
</div>

<script>
function toggleInsurance() {
    const block = document.getElementById('insurance_block');
    block.style.display = document.getElementById('accepts_insurance').checked ? 'block' : 'none';
}

document.getElementById("insurance_select").addEventListener("change", function() {
    const otherInput = document.getElementById("insurance_other");
    if ([...this.options].some(opt => opt.selected && opt.value === "otra")) {
        otherInput.style.display = "block";
    } else {
        otherInput.style.display = "none";
        otherInput.value = "";
    }
});
</script>

</div>
</body>
</html>
