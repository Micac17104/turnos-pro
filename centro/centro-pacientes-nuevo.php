<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id'];

$errors = [];
$success = "";

// Cargar profesionales del centro desde users
$stmt = $pdo->prepare("
    SELECT id, name
    FROM users
    WHERE account_type = 'professional'
      AND parent_center_id = ?
    ORDER BY name
");
$stmt->execute([$center_id]);
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_POST) {

    $name  = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $dni   = trim($_POST['dni'] ?? '');
    $staff_ids = $_POST['staff_ids'] ?? [];

    if ($name === '') {
        $errors[] = "El nombre es obligatorio.";
    }

    if ($dni === '') {
        $errors[] = "El DNI es obligatorio.";
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido.";
    }

    if (empty($staff_ids)) {
        $errors[] = "Debes asignar al menos un profesional.";
    }

    if (empty($errors)) {

        // Verificar DNI único dentro del centro
        $stmt = $pdo->prepare("
            SELECT id FROM clients
            WHERE center_id = ? AND dni = ?
        ");
        $stmt->execute([$center_id, $dni]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $errors[] = "Ya existe un paciente con ese DNI en este centro.";
        } else {
            // Crear paciente
            $stmt = $pdo->prepare("
                INSERT INTO clients (name, email, phone, dni, center_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $phone, $dni, $center_id]);

            $patient_id = $pdo->lastInsertId();

            // Relacionar con profesionales del centro
            $stmtRel = $pdo->prepare("
                INSERT INTO patient_professionals (patient_id, staff_id, center_id)
                VALUES (?, ?, ?)
            ");

            foreach ($staff_ids as $sid) {
                if ($sid !== '') {
                    $stmtRel->execute([$patient_id, $sid, $center_id]);
                }
            }

            header("Location: centro-pacientes.php?ok=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo paciente</title>
<style>
body{background:#f1f5f9;font-family:Arial;display:flex;justify-content:center;align-items:center;padding:40px;}
.box{background:white;padding:40px;border-radius:20px;width:420px;text-align:center;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input, select{width:100%;padding:12px;margin:8px 0;border-radius:10px;border:1px solid #cbd5e1;}
button{width:100%;padding:14px;background:#0ea5e9;color:white;border:none;border-radius:12px;font-weight:600;cursor:pointer;}
button:hover{opacity:0.9;}
.error{color:#b00020;margin-bottom:10px;}
a{color:#0ea5e9;text-decoration:none;font-size:14px;}
small{display:block;color:#64748b;margin-top:-4px;margin-bottom:8px;text-align:left;}
</style>
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div style="margin-left:260px; padding:24px;">

<div class="box">
    <h2>Agregar paciente</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input name="name" placeholder="Nombre y apellido" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        <input name="dni" placeholder="DNI" required value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>">
        <small>El DNI se usa para identificar al paciente dentro del centro.</small>

        <input name="email" type="email" placeholder="Email (opcional)" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <input name="phone" placeholder="Teléfono (opcional)" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">

        <label style="display:block;text-align:left;margin-top:10px;margin-bottom:4px;">Profesionales que lo atienden</label>
        <select name="staff_ids[]" multiple size="4">
            <?php foreach ($staff as $s): ?>
                <option value="<?= $s['id'] ?>"
                    <?php
                    if (!empty($_POST['staff_ids']) && in_array($s['id'], (array)$_POST['staff_ids'])) {
                        echo 'selected';
                    }
                    ?>
                >
                    <?= htmlspecialchars($s['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small>Puedes seleccionar uno o varios profesionales del centro (Ctrl/Cmd + clic).</small>

        <button>Crear paciente</button>
    </form>

    <p style="margin-top:10px;"><a href="centro-pacientes.php">Volver</a></p>
</div>

</div>
</body>
</html>