<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

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

if ($name === '' || $profession === '') {
    die("Datos incompletos.");
}

$stmt = $pdo->prepare("
    UPDATE users
    SET name=?, profession=?, phone=?, email=?, address=?, city=?, province=?, 
        public_description=?, specialties=?, slug=?
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
    $user_id
]);

// FOTO DE PERFIL
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

header("Location: /turnos-pro/pro/perfil.php?ok=1");
exit;