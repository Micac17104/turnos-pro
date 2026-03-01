<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';

// Verificar que el usuario actual sea un centro
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'center') {
    die("Acceso no autorizado.");
}

$center_id = $_SESSION['user_id'];
$pro_id = $_GET['id'] ?? null;

if (!$pro_id) {
    die("Profesional no encontrado.");
}

// Verificar que el profesional pertenezca al centro
$stmt = $pdo->prepare("
    SELECT id, name 
    FROM users 
    WHERE id = ? 
    AND account_type = 'professional'
    AND parent_center_id = ?
");
$stmt->execute([$pro_id, $center_id]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    die("Este profesional no pertenece a tu centro.");
}

// Guardar sesión original del centro para volver después
$_SESSION['original_center_id'] = $center_id;

// Crear sesión como profesional
$_SESSION['user_id'] = $pro['id'];
$_SESSION['user_name'] = $pro['name'];
$_SESSION['account_type'] = 'professional';

// Redirigir al panel del profesional
header("Location: /pro/index.php");
exit;