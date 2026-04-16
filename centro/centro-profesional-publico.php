<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/auth-centro.php';


$pro_id = $_GET['id'] ?? null;

if (!$pro_id) {
    die("Profesional no encontrado.");
}

// Obtener datos del profesional
$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE id = ? AND account_type = 'professional' AND parent_center_id = ?
");
$stmt->execute([$pro_id, $center_id]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    die("Profesional no encontrado o no pertenece a este centro.");
}

$errors = [];
$success = "";

/* ============================================================
   GUARDAR PERFIL
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {

    $name = trim($_POST['name'] ?? '');
    $profession = trim($_POST['profession'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $public_description = trim($_POST['public_description'] ?? '');
    $specialties = trim($_POST['specialties'] ?? '');
    $accepts_insurance = isset($_POST['accepts_insurance']) ? 1 : 0;
    $insurance_list = trim($_POST['insurance_list'] ?? '');
    $slug = trim($_POST['slug'] ?? '');

    if ($name === '') $errors[] = "El nombre es obligatorio.";
    if ($profession === '') $errors[] = "La profesión es obligatoria.";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido.";
    }

    // Validar slug único
    if ($slug === '') {
        $errors[] = "El slug público es obligatorio.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $pro_id]);
        if ($stmt->fetch()) {
            $errors[] = "Ese slug ya está en uso.";
        }
    }

    // Guardar imagen en la base de datos (BLOB)
    if (!empty($_FILES['profile_image']['tmp_name'])) {

        $imgData = file_get_contents($_FILES['profile_image']['tmp_name']);

        $stmt = $pdo->prepare("UPDATE users SET profile_image_blob = ? WHERE id = ?");
        $stmt->execute([$imgData, $pro_id]);

        $pro['profile_image_blob'] = $imgData;
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE users SET
                name = ?, profession = ?, phone = ?, email = ?,
                public_description = ?, specialties = ?, accepts_insurance = ?,
                insurance_list = ?, slug = ?
            WHERE id = ? AND parent_center_id = ?
        ");

        $stmt->execute([
            $name, $profession, $phone, $email,
            $public_description, $specialties, $accepts_insurance,
            $insurance_list, $slug,
            $pro_id, $center_id
        ]);

        $success = "Perfil actualizado correctamente.";

        // Refrescar datos
        $pro = array_merge($pro, [
            'name' => $name,
            'profession' => $profession,
            'phone' => $phone,
            'email' => $email,
            'public_description' => $public_description,
            'specialties' => $specialties,
            'accepts_insurance' => $accepts_insurance,
            'insurance_list' => $insurance_list,
            'slug' => $slug
        ]);
    }
}

/* ============================================================
   HORARIOS (TABLA schedules)
   ============================================================ */

// Obtener horarios actuales
$stmt = $pdo->prepare("SELECT * FROM schedules WHERE user_id = ? ORDER BY day_of_week, start_time");
$stmt->execute([$pro_id]);
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Guardar horarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_schedule'])) {

    // Borrar horarios anteriores
    $pdo->prepare("DELETE FROM schedules WHERE user_id = ?")->execute([$pro_id]);

    if (!empty($_POST['day'])) {
        foreach ($_POST['day'] as $i => $day) {
            $start = $_POST['start'][$i];
            $end   = $_POST['end'][$i];
            $slot  = $_POST['interval'][$i] ?? 30;

            if ($day !== '' && $start !== '' && $end !== '') {
                $stmt = $pdo->prepare("
                    INSERT INTO schedules (user_id, day_of_week, start_time, end_time, slot_duration)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$pro_id, $day, $start, $end, $slot]);
            }
        }
    }

    $success = "Horarios actualizados correctamente.";

    // Recargar horarios
    $stmt = $pdo->prepare("SELECT * FROM schedules WHERE user_id = ? ORDER BY day_of_week, start_time");
    $stmt->execute([$pro_id]);
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Perfil público del profesional</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.top{background:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,0.06);}
.main{padding:24px;max-width:800px;margin:0 auto;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input, textarea, select{width:100%;padding:10px;margin:6px 0 14px;border-radius:10px;border:1px solid #cbd5e1;font-size:14px;}
button{padding:12px 20px;background:#0ea5e9;color:white;border:none;border-radius:12px;font-weight:600;cursor:pointer;}
button:hover{opacity:0.9;}
.error{color:#b00020;margin-bottom:10px;}
.success{color:#22c55e;margin-bottom:10px;}
.label{font-size:13px;font-weight:bold;color:#475569;margin-bottom:2px;display:block;}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div style="margin-left:260px; padding:24px;">

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
        <h2>Perfil público de <?= htmlspecialchars($pro['name']) ?></h2>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <!-- FORMULARIO PERFIL -->
        <form method="post" enctype="multipart/form-data">

            <input type="hidden" name="save_profile" value="1">

            <label class="label">Nombre</label>
            <input name="name" value="<?= htmlspecialchars($pro['name']) ?>" required>

            <label class="label">Profesión</label>
            <input name="profession" value="<?= htmlspecialchars($pro['profession']) ?>" required>

            <label class="label">Teléfono</label>
            <input name="phone" value="<?= htmlspecialchars($pro['phone']) ?>">

            <label class="label">Email</label>
            <input name="email" value="<?= htmlspecialchars($pro['email']) ?>">

            <label class="label">Descripción pública</label>
            <textarea name="public_description" rows="4"><?= htmlspecialchars($pro['public_description']) ?></textarea>

            <label class="label">Especialidades</label>
            <input name="specialties" value="<?= htmlspecialchars($pro['specialties']) ?>">

            <label class="label">Acepta obra social</label>
            <input type="checkbox" name="accepts_insurance" <?= $pro['accepts_insurance'] ? 'checked' : '' ?>>

            <label class="label">Lista de obras sociales</label>
            <textarea name="insurance_list" rows="3"><?= htmlspecialchars($pro['insurance_list']) ?></textarea>

            <label class="label">Slug público</label>
            <input name="slug" value="<?= htmlspecialchars($pro['slug']) ?>" required>

            <label class="label">Foto del profesional</label>
            <input type="file" name="profile_image" accept="image/*">

            <?php if (!empty($pro['profile_image_blob'])): ?>
                <img src="data:image/jpeg;base64,<?= base64_encode($pro['profile_image_blob']) ?>"
                     style="width:120px;height:120px;border-radius:16px;object-fit:cover;margin-top:10px;border:2px solid #e2e8f0;">
            <?php endif; ?>

            <br><br>

            <button>Guardar perfil</button>
        </form>

    </div>


    <!-- HORARIOS -->
    <div class="card">
        <h2>Horarios de atención</h2>

        <form method="post">

            <input type="hidden" name="save_schedule" value="1">

            <div id="horarios-container">
                <?php foreach ($horarios as $h): ?>
                    <div class="horario-item" style="display:flex; gap:10px; margin-bottom:10px;">

                        <select name="day[]">
                            <option value="1" <?= $h['day_of_week']==1?'selected':'' ?>>Lunes</option>
                            <option value="2" <?= $h['day_of_week']==2?'selected':'' ?>>Martes</option>
                            <option value="3" <?= $h['day_of_week']==3?'selected':'' ?>>Miércoles</option>
                            <option value="4" <?= $h['day_of_week']==4?'selected':'' ?>>Jueves</option>
                            <option value="5" <?= $h['day_of_week']==5?'selected':'' ?>>Viernes</option>
                            <option value="6" <?= $h['day_of_week']==6?'selected':'' ?>>Sábado</option>
                            <option value="7" <?= $h['day_of_week']==7?'selected':'' ?>>Domingo</option>
                        </select>

                        <input type="time" name="start[]" value="<?= $h['start_time'] ?>">
                        <input type="time" name="end[]" value="<?= $h['end_time'] ?>">

                        <input type="number" name="interval[]" value="<?= $h['slot_duration'] ?>" min="5" max="120" style="width:80px;">
                        <span>min</span>

                        <button type="button" onclick="this.parentNode.remove()">Eliminar</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" onclick="addHorario()">+ Agregar horario</button>

            <script>
            function addHorario() {
                const div = document.createElement('div');
                div.className = 'horario-item';
                div.style = "display:flex; gap:10px; margin-bottom:10px;";
                div.innerHTML = `
                    <select name="day[]">
                        <option value="1">Lunes</option>
                        <option value="2">Martes</option>
                        <option value="3">Miércoles</option>
                        <option value="4">Jueves</option>
                        <option value="5">Viernes</option>
                        <option value="6">Sábado</option>
                        <option value="7">Domingo</option>
                    </select>

                    <input type="time" name="start[]">
                    <input type="time" name="end[]">

                    <input type="number" name="interval[]" value="30" min="5" max="120" style="width:80px;">
                    <span>min</span>

                    <button type="button" onclick="this.parentNode.remove()">Eliminar</button>
                `;
                document.getElementById('horarios-container').appendChild(div);
            }
            </script>

            <br><br>

            <button>Guardar horarios</button>
        </form>

    </div>

</div>

</div>
</body>
</html>