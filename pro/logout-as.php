<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

// Verificar que exista una sesión original del centro
if (!isset($_SESSION['original_center_id'])) {
    die("No hay sesión de centro para restaurar.");
}

// Restaurar sesión del centro
$_SESSION['user_id'] = $_SESSION['original_center_id'];
$_SESSION['account_type'] = 'center';

// Limpiar datos temporales
unset($_SESSION['original_center_id']);
unset($_SESSION['user_name']); // opcional, se vuelve a cargar en el panel del centro

// Redirigir al panel del centro
header("Location: /centro/index.php");
exit;