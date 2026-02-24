<?php
// --- SESIONES ---
$path = __DIR__ . '/../../sessions'; // sube 2 niveles: includes → pro → raíz
if (!is_dir($path)) mkdir($path, 0777, true);
session_save_path($path);
session_start();

// --- VALIDAR SESIÓN ---
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];