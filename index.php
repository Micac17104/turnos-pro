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

        <!-- Fuente Monoton -->
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

        <!-- LAYOUT PRINCIPAL -->
        <div class="flex min-h-screen">

            <!-- IZQUIERDA: HERO -->
            <div class="flex-1 flex flex-col justify-center px-8 lg:px-20">

                <!-- LOGO -->
                <div class="mb-6">
                    <span class="logo-font text-3xl text-slate-900">TurnosAura</span>
                </div>

                <!-- TITULAR -->
                <h1 class="text-4xl lg:text-5xl font-bold text-slate-900 leading-tight mb-6">
                    Gestión de turnos simple,<br>
                    moderna y profesional.
                </h1>

                <!-- FRASE PROBLEMA → SOLUCIÓN -->
                <p class="text-xl text-slate-700 font-semibold mb-6">
                    Un sistema que trabaja por vos.
                </p>

                <!-- SUBTÍTULO -->
                <p class="text-lg text-slate-600 mb-8 max-w-xl">
                    Una plataforma pensada para centros, profesionales independientes y pacientes.
                    Agenda online, recordatorios automáticos y una experiencia clara y moderna.
                </p>

                <!-- CTA -->
                <a href="auth/login.php"
                   class="inline-block bg-slate-900 text-white px-8 py-3 rounded-lg text-lg shadow hover:bg-slate-800 transition">
                    Comenzar ahora
                </a>

                <!-- MOCKUP -->
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

        <!-- SECCIÓN BENEFICIOS -->
        <section class="py-20 bg-white">
            <div class="max-w-6xl mx-auto px-6">
                <h2 class="text-3xl font-bold text-slate-900 text-center mb-12">
                    Todo lo que necesitás para trabajar mejor
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">

                    <div class="p-6 bg-slate-50 rounded-xl shadow-sm border border-slate-200">
                        <h3 class="text-xl font-semibold text-slate-900 mb-3">Para profesionales</h3>
                        <p class="text-slate-600">
                            Agenda online, recordatorios automáticos, historial de pacientes y una experiencia moderna.
                        </p>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-xl shadow-sm border border-slate-200">
                        <h3 class="text-xl font-semibold text-slate-900 mb-3">Para centros</h3>
                        <p class="text-slate-600">
                            Múltiples profesionales, salas, reportes, permisos y administración centralizada.
                        </p>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-xl shadow-sm border border-slate-200">
                        <h3 class="text-xl font-semibold text-slate-900 mb-3">Para pacientes</h3>
                        <p class="text-slate-600">
                            Turnos rápidos, recordatorios, historial y una experiencia simple desde cualquier dispositivo.
                        </p>
                    </div>

                </div>
            </div>
        </section>

        <!-- PREGUNTAS FRECUENTES -->
        <section class="py-20 bg-slate-100">
            <div class="max-w-4xl mx-auto px-6">
                <h2 class="text-3xl font-bold text-slate-900 text-center mb-12">
                    Preguntas frecuentes
                </h2>

                <div class="space-y-6">

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">¿Necesito instalar algo?</h3>
                        <p class="text-slate-600">No. TurnosAura funciona 100% online desde cualquier dispositivo.</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">¿Puedo usarlo si trabajo solo?</h3>
                        <p class="text-slate-600">Sí. Está pensado tanto para profesionales independientes como para centros.</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">¿Los pacientes necesitan registrarse?</h3>
                        <p class="text-slate-600">No. Pueden sacar turnos sin crear una cuenta.</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">¿Cómo funcionan los recordatorios?</h3>
                        <p class="text-slate-600">El sistema envía recordatorios automáticos por WhatsApp o email.</p>
                    </div>

                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="bg-slate-900 text-slate-300 py-10 mt-20">
            <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row justify-between">

                <div>
                    <h3 class="text-xl font-semibold text-white mb-3">TurnosAura</h3>
                    <p class="text-slate-400">Gestión moderna de turnos para profesionales y centros.</p>
                </div>

                <div class="mt-6 md:mt-0">
                    <p class="text-slate-400">© <?php echo date('Y'); ?> TurnosAura</p>
                </div>

            </div>
        </footer>

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