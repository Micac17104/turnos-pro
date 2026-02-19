<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar login del paciente
if (!isset($_SESSION['paciente_id'])) {
    header("Location: /turnos-pro/public/login-paciente.php");
    exit;
}

$paciente_nombre = $_SESSION['paciente_nombre'] ?? 'Paciente';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del paciente</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col py-6">

        <div class="px-6 mb-8">
            <h2 class="text-xl font-bold">Mi Salud</h2>
            <p class="text-slate-400 text-sm mt-1">Panel del paciente</p>
        </div>

        <nav class="flex-1 px-4 space-y-2">

            <a href="/turnos-pro/public/paciente-dashboard.php"
               class="block px-4 py-2 rounded-lg hover:bg-slate-800">
                🏠 Dashboard
            </a>

            <a href="/turnos-pro/public/paciente-sacar-turno.php"
               class="block px-4 py-2 rounded-lg hover:bg-slate-800">
                📅 Sacar turno
            </a>

            <a href="/turnos-pro/public/paciente-historia.php"
               class="block px-4 py-2 rounded-lg hover:bg-slate-800">
                📄 Historia clínica
            </a>

            <a href="/turnos-pro/public/paciente-perfil.php"
               class="block px-4 py-2 rounded-lg hover:bg-slate-800">
                👤 Mi perfil
            </a>

        </nav>

        <div class="px-4 mt-auto">
            <a href="/turnos-pro/public/paciente-logout.php"
               class="block px-4 py-2 rounded-lg bg-red-600 text-center hover:bg-red-700">
                Cerrar sesión
            </a>
        </div>

    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 p-8">