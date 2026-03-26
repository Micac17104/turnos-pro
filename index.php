<?php

// 1) Detectar la URL solicitada
$request = trim($_SERVER['REQUEST_URI'], '/');

// 2) Evitar error fatal cuando Railway pide /favicon.ico
if ($request === 'favicon.ico') {
    http_response_code(204); // Sin contenido
    exit;
}

// 3) Si la URL está vacía → mostrar la landing nueva
if ($request === '') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>TurnosAura - Gestión moderna de turnos</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Monoton&display=swap" rel="stylesheet">
        <style>
            .logo-font {
                font-family: "Monoton", system-ui;
                letter-spacing: 2px;
            }
        </style>
    </head>

    <body class="bg-slate-50">

        <div class="flex min-h-screen">

            <!-- IZQUIERDA: HERO -->
            <div class="flex-1 flex flex-col justify-center px-8 lg:px-20">

                <div class="mb-6">
                    <span class="logo-font text-3xl text-slate-900">TurnosAura</span>
                </div>

                <h1 class="text-4xl lg:text-5xl font-bold text-slate-900 leading-tight mb-6">
                    Gestión de turnos simple,<br>
                    moderna y profesional.
                </h1>

                <p class="text-lg text-slate-600 mb-8 max-w-xl">
                    Una plataforma pensada para centros, profesionales independientes y pacientes.
                    Agenda online, recordatorios y una experiencia clara y moderna.
                </p>

                <a href="auth/login.php"
                   class="inline-block bg-slate-900 text-white px-8 py-3 rounded-lg text-lg shadow hover:bg-slate-800 transition">
                    Comenzar ahora
                </a>

                <div class="mt-12">
                    <img src="https://via.placeholder.com/600x350"
                         class="rounded-xl shadow-lg border border-slate-200 max-w-full"
                         alt="Dashboard TurnosAura">
                </div>

            </div>

            <!-- DERECHA: PANEL DE USUARIO (MISMAS RUTAS QUE ANTES) -->
            <div class="w-full max-w-sm bg-white border-l border-slate-200 shadow-xl p-8 lg:p-10 flex flex-col justify-center">

                <h2 class="text-2xl font-semibold text-slate-900 mb-6 text-center">
                    Ingresar como
                </h2>

                <!-- Igual que antes: profesional o centro -->
                <a href="auth/login.php"
                   class="block w-full text-center bg-slate-900 text-white py-3 rounded-lg mb-4 hover:bg-slate-800 transition">
                    Soy profesional o centro
                </a>

                <!-- Igual que antes: paciente -->
                <a href="public/login-paciente.php"
                   class="block w-full text-center bg-slate-200 text-slate-800 py-3 rounded-lg hover:bg-slate-300 transition">
                    Soy paciente
                </a>

            </div>

        </div>

    </body>
    </html>
    <?php
    exit;
}

// 4) Si la URL parece un slug (solo letras, números y guiones)
if (preg_match('/^[a-z0-9-]+$/', $request)) {
    header("Location: /public/profesional-landing.php?slug=" . $request);
    exit;
}

// 5) Si no es slug ni home → cargar archivo normal
$path = __DIR__ . '/' . $request;

if (file_exists($path)) {
    require $path;
} else {
    http_response_code(404);
    echo "Página no encontrada.";
}
