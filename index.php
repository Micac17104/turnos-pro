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
    </head>

    <body class="bg-slate-50">

        <div class="flex min-h-screen">

            <!-- IZQUIERDA: HERO -->
            <div class="flex-1 flex flex-col justify-center px-20">

                <h1 class="text-5xl font-bold text-slate-900 leading-tight mb-6">
                    Gestión de turnos simple,<br>
                    moderna y profesional.
                </h1>

                <p class="text-lg text-slate-600 mb-8 max-w-xl">
                    TurnosAura es la plataforma diseñada para centros, profesionales independientes y pacientes.
                    Agenda online, recordatorios automáticos y una experiencia premium.
                </p>

                <a href="/pro/login.php"
                   class="inline-block bg-slate-900 text-white px-8 py-3 rounded-lg text-lg shadow hover:bg-slate-800 transition">
                    Comenzar ahora
                </a>

                <div class="mt-12">
                    <img src="https://via.placeholder.com/600x350"
                         class="rounded-xl shadow-lg border border-slate-200"
                         alt="Dashboard TurnosAura">
                </div>

            </div>

            <!-- DERECHA: PANEL DE USUARIO -->
            <div class="w-[380px] bg-white border-l border-slate-200 shadow-xl p-10 flex flex-col justify-center">

                <h2 class="text-2xl font-semibold text-slate-900 mb-6 text-center">
                    Ingresar como
                </h2>

                <a href="/pro/login.php"
                   class="block w-full text-center bg-slate-900 text-white py-3 rounded-lg mb-4 hover:bg-slate-800 transition">
                    Profesional
                </a>

                <a href="/centro/login.php"
                   class="block w-full text-center bg-slate-700 text-white py-3 rounded-lg mb-4 hover:bg-slate-600 transition">
                    Centro
                </a>

                <a href="/public/index.php"
                   class="block w-full text-center bg-slate-500 text-white py-3 rounded-lg hover:bg-slate-400 transition">
                    Paciente
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