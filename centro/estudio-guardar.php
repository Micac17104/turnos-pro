<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../pro/includes/auth-centro.php';
require __DIR__ . '/../pro/includes/db.php';

// Cargar Cloudinary
require_once __DIR__ . '/../vendor/autoload.php';
use Cloudinary\Cloudinary;

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'Raíz',
        'api_key'    => '456722813821841',
        'api_secret' => '8pWHGWCqvLVzviELSSe_MaFXQ3w',
    ],
]);

$center_id  = $_SESSION['user_id'];
$patient_id = $_POST['patient_id'];
$titulo     = trim($_POST['titulo']);
$descripcion= trim($_POST['descripcion']);
$fecha      = $_POST['fecha'];

$archivo = null;

// SUBIR ARCHIVO A CLOUDINARY
if (!empty($_FILES['archivo']['tmp_name'])) {

    try {
        $uploadResult = $cloudinary->uploadApi()->upload(
            $_FILES['archivo']['tmp_name'],
            [
                'folder' => 'estudios_medicos'
            ]
        );

        // Guardamos la URL segura
        $archivo = $uploadResult['secure_url'];

    } catch (Exception $e) {
        die("ERROR al subir a Cloudinary: " . $e->getMessage());
    }
}

$stmt = $pdo->prepare("
    INSERT INTO estudios_medicos (client_id, professional_id, titulo, descripcion, archivo, fecha, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->execute([$patient_id, $center_id, $titulo, $descripcion, $archivo, $fecha]);

header("Location: estudios.php?id=" . $patient_id);
exit;
