<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$record_id = $_POST['record_id'] ?? null;
if (!$record_id) die("Evolución no encontrada.");

$stmt = $pdo->prepare("
    SELECT patient_id FROM clinical_records
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$record_id, $user_id]);
$evo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$evo) die("Evolución no pertenece a este profesional.");

$patient_id = $evo['patient_id'];

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    die("Error al subir archivo.");
}

$archivo = $_FILES['archivo'];
$permitidos = ['pdf','jpg','jpeg','png'];
$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $permitidos)) die("Formato no permitido.");

$upload_dir = __DIR__ . "/../uploads/";

if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$nombre_final = uniqid("file_") . "." . $ext;
$ruta_final = $upload_dir . $nombre_final;

if (!move_uploaded_file($archivo['tmp_name'], $ruta_final)) {
    die("Error al guardar archivo.");
}

$stmt = $pdo->prepare("
    INSERT INTO clinical_files (record_id, file_path, file_name)
    VALUES (?, ?, ?)
");
$stmt->execute([$record_id, $nombre_final, $archivo['name']]);

header("Location: /turnos-pro/pro/paciente-historia.php?id=" . $patient_id);
exit;