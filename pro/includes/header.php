<?php
// /pro/includes/header.php
if (!isset($page_title)) {
    $page_title = 'Panel profesional';
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

    <!-- Estilos propios opcionales -->
    <link rel="stylesheet" href="/turnos-pro/pro/assets/css/app.css">
</head>
<body class="bg-slate-100 text-slate-900">
<div class="min-h-screen flex">