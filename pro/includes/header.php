<?php
// /pro/includes/header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Título por defecto
if (!isset($page_title)) {
    $page_title = 'Panel profesional';
}

// Función h() por si no está definida
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

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Estilos propios -->
    <link rel="stylesheet" href="/assets/css/app.css">
</head>

<body class="bg-slate-100 text-slate-900">

<!-- CONTENEDOR PRINCIPAL DEL PANEL -->
<div class="min-h-screen flex">
    