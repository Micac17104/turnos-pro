<?php
// --- FIX DEFINITIVO PARA RAILWAY ---
$path = __DIR__ . '/../../sessions';

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

if (!is_writable($path)) {
    chmod($path, 0777);
}

session_save_path($path);
session_start();
// -----------------------------------

// VALIDAR SESIÓN DEL PROFESIONAL
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];