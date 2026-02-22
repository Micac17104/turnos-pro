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

// Si no viene definido, asignar título por defecto
if (!isset($page_title)) {
    $page_title = 'Panel profesional';
}

// Función h() por si no está cargada aún
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= h($page_title) ?> - TurnosPro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Estilos propios -->
    <!-- RUTA CORREGIDA: ahora es relativa y funciona en Railway -->
    <link rel="stylesheet" href="assets/css/app.css">
</head>

<body class="bg-slate-100 text-slate-900">
<div class="min-h-screen flex">