<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

// ===============================
// 1) Recibir datos del formulario
// ===============================
$name        = trim($_POST['name'] ?? '');
$profession  = trim($_POST['profession'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$email       = trim($_POST['email'] ?? '');
$address     = trim($_POST['address'] ?? '');
$city        = trim($_POST['city'] ?? '');
$province    = trim($_POST['province'] ?? '');
$public_description = trim($_POST['public_description'] ?? '');
$specialties = trim($_POST['specialties'] ?? '');
$slug        = trim($_POST['slug'] ?? '');
$accepts_insurance = isset($_POST['accepts_insurance']) ? 1 : 0;
$insurance_list = trim($_POST['insurance_list'] ?? '');

// ===============================
// 2) Validaciones básicas
// ===============================
if ($name === '' || $profession === '') {
    die("Datos incompletos.");
}

// ===============================
// 3) Normalizar slug
// ===============================
$slug = strtolower($slug);
$slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
$slug = preg_replace('/-+/', '-', $slug);

// ===============================
// 4) Validar slug único
// ===============================
if ($slug !== '') {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE slug = ? AND id != ?");
    $stmt->execute([$slug, $user_id]);
    if ($stmt->fetch()) {
        die("El slug ya está en uso por otro profesional.");
    }
}

// ===============================
// 5) Actualizar datos del profesional
// ===============================
$stmt = $pdo->prepare("
    UPDATE users
    SET name=?, profession=?, phone=?, email=?, address=?, city=?, province=?, 
        public_description=?, specialties=?, slug=?, accepts_insurance=?, insurance_list=?
    WHERE id=?
");

$stmt->execute([
    $name,
    $profession,
    $phone,
    $email,
    $address,
    $city,
    $province,
    $public_description,
    $specialties,
    $slug,
    $accepts_insurance,
    $insurance_list,
    $user_id
]);

// ===============================
// 6) FOTO DE PERFIL
// ===============================
if (!empty($_FILES['profile_image']['name'])) {

    $file = $_FILES['profile_image'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $permitidos = ['jpg','jpeg','png'];

    if (in_array($ext, $permitidos)) {

        $upload_dir = __DIR__ . "/../uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $nombre_final = uniqid("profile_") . "." . $ext;
        $ruta_final = $upload_dir . $nombre_final;

        if (move_uploaded_file($file['tmp_name'], $ruta_final)) {
            $stmt = $pdo->prepare("UPDATE users SET profile_image=? WHERE id=?");
            $stmt->execute([$nombre_final, $user_id]);
        }
    }
}

// ===============================
// 7) Redirección final
// ===============================
redirect("perfil.php?ok=1");