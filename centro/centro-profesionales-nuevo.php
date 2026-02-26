<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';

$errors = [];
$success = "";

// Procesar formulario
if ($_POST) {

    // Normalizar datos
    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $profession = trim($_POST['profession'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $specialties = trim($_POST['specialties'] ?? '');
    $accepts_insurance = isset($_POST['accepts_insurance']) ? 1 : 0;

    // Lista de obras sociales seleccionadas
    $insurance_list = $_POST['insurance_list'] ?? [];

    // Si eligió "otra", agregamos lo que escribió
    if (isset($_POST['insurance_other']) && $_POST['insurance_other'] !== '') {
        $insurance_list[] = trim($_POST['insurance_other']);
    }

    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validaciones
    if ($password !== $password2) {
        $errors[] = "Las contraseñas no coinciden.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido.";
    }

    if ($name === '' || $profession === '') {
        $errors[] = "Nombre y profesión son obligatorios.";
    }

    // Validar email único
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Ese email ya está registrado.";
        }
    }

    // Crear slug
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));

    // Asegurar slug único
    $stmt = $pdo->prepare("SELECT id FROM users WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetch()) {
        $slug .= '-' . rand(1000, 9999);
    }

    // Convertir lista de obras sociales a JSON
    $insurance_json = json_encode($insurance_list);

    // Guardar profesional
    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO users 
            (name, email, password, profession, phone, city, description, specialties, accepts_insurance, insurance_list, slug, account_type, parent_center_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'professional', ?)
        ");

        $stmt->execute([
            $name,
            $email,
            password_hash($password, PASSWORD_BCRYPT),
            $profession,
            $phone,
            $city,
            $description,
            $specialties,
            $accepts_insurance,
            $insurance_json,
            $slug,
            $center_id
        ]);

        header("Location: centro-profesionales.php?ok=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agregar profesional</title>
<style>
body{background:#f1f5f9;font-family:Arial;display:flex;justify-content:center;align-items:center;padding:40px;}
.box{background:white;padding:40px;border-radius:20px;width:480px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input, textarea, select{width:100%;padding:12px;margin:8px 0;border-radius:10px;border:1px solid #cbd5e1;}
button{width:100%;padding:14px;background:#0ea5e9;color:white;border:none;border-radius:12px;font-weight:600;cursor:pointer;}
button:hover{opacity:0.9;}
.error{color:#b00020;margin-bottom:10px;}
a{color:#0ea5e9;text-decoration:none;font-size:14px;}
</style>
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div style="margin-left:260px; padding:24px;">

<div class="box">
    <h2>Agregar profesional</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <input name="name" placeholder="Nombre y apellido" required>

        <input name="email" type="email" placeholder="Email" required>

        <input name="profession" placeholder="Profesión (psicólogo, nutricionista, etc.)" required>

        <input name="phone" placeholder="Teléfono">

        <input name="city" placeholder="Ciudad">

        <textarea name="description" placeholder="Descripción pública"></textarea>

        <textarea name="specialties" placeholder="Especialidades (separadas por coma)"></textarea>

        <label>
            <input type="checkbox" name="accepts_insurance" id="accepts_insurance" onclick="toggleInsurance()"> Acepta obra social
        </label>

        <div id="insurance_block" style="display:none; margin-top:10px;">

            <label>Obras sociales (Ctrl + click para varias):</label>
            <select name="insurance_list[]" multiple size="5">
                <option value="OSDE">OSDE</option>
                <option value="Swiss Medical">Swiss Medical</option>
                <option value="Galeno">Galeno</option>
                <option value="Medifé">Medifé</option>
                <option value="IOMA">IOMA</option>
                <option value="otra">Otra (escribir abajo)</option>
            </select>

            <input name="insurance_other" id="insurance_other" placeholder="Escribí otra obra social" style="display:none;">
        </div>

        <input name="password" type="password" placeholder="Contraseña" required>
        <input name="password2" type="password" placeholder="Repetir contraseña" required>

        <button>Crear profesional</button>
    </form>

    <p style="margin-top:10px;"><a href="centro-profesionales.php">Volver</a></p>
</div>

<script>
function toggleInsurance() {
    const block = document.getElementById('insurance_block');
    block.style.display = document.getElementById('accepts_insurance').checked ? 'block' : 'none';
}

document.querySelector("select[name='insurance_list[]']").addEventListener("change", function() {
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