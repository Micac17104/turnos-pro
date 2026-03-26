<?php

// 1) Detectar la URL solicitada
$request = trim($_SERVER['REQUEST_URI'], '/');

// 2) Evitar error fatal cuando Railway pide /favicon.ico
if ($request === 'favicon.ico') {
    http_response_code(204);
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

        <style>
            .hero-logo {
                max-width: 150px;
            }
            @media (max-width: 768px) {
                .hero-logo {
                    max-width: 120px;
                    margin: 0 auto;
                }
            }
            .mockup {
                max-width: 420px;
            }
            @media (max-width: 768px) {
                .mockup {
                    max-width: 300px;
                    margin: 0 auto;
                }
            }
        </style>
    </head>

    <body class="bg-slate-50">

        <!-- LAYOUT PRINCIPAL -->
        <div class="flex flex-col lg:flex-row min-h-screen">

            <!-- IZQUIERDA: HERO COMPACTO -->
            <div class="flex-1 flex flex-col justify-center px-8 lg:px-20 pt-10 lg:pt-16">

                <!-- LOGO REAL (CHICO) -->
                <img src="assets/logo.jpeg" alt="TurnosAura" class="hero-logo mb-4 lg:mb-6">

                <!-- TITULAR -->
                <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 leading-tight mb-3">
                    Gestión de turnos simple,<br>
                    moderna y profesional.
                </h1>

                <!-- FRASE PROBLEMA → SOLUCIÓN -->
                <p class="text-lg text-slate-700 font-semibold mb-4">
                    Un sistema que trabaja por vos.
                </p>

                <!-- SUBTÍTULO -->
                <p class="text-base text-slate-600 mb-6 max-w-xl">
                    Una plataforma pensada para centros, profesionales independientes y pacientes.
                    Agenda online, recordatorios automáticos y una experiencia clara y moderna.
                </p>

                <!-- CTA -->
                <a href="auth/login.php"
                   class="inline-block bg-slate-900 text-white px-6 py-3 rounded-lg text-base shadow hover:bg-slate-800 transition">
                    Comenzar ahora
                </a>

                <!-- MOCKUP REAL (CHICO) -->
                <div class="mt-8 lg:mt-10">
                    <img src="assets/dashboard.jpeg"
                         class="mockup rounded-xl shadow-lg border border-slate-200"
                         alt="Dashboard TurnosAura">
                </div>

            </div>

            <!-- DERECHA: PANEL DE USUARIO (COMPACTO) -->
            <div class="w-full lg:w-[340px] bg-white border-l border-slate-200 shadow-xl p-6 lg:p-8 flex flex-col justify-center mt-10 lg:mt-0">

                <h2 class="text-xl font-semibold text-slate-900 mb-5 text-center">
                    Ingresar como
                </h2>

                <a href="auth/login.php"
                   class="block w-full text-center bg-slate-900 text-white py-3 rounded-lg mb-3 hover:bg-slate-800 transition">
                    Soy profesional o centro
                </a>

                <a href="public/login-paciente.php"
                   class="block w-full text-center bg-slate-200 text-slate-800 py-3 rounded-lg hover:bg-slate-300 transition">
                    Soy paciente
                </a>

            </div>

        </div>
                <!-- SECCIÓN: CÓMO FUNCIONA -->
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-6xl mx-auto px-6">
                <h2 class="text-3xl font-bold text-slate-900 text-center mb-12">
                    ¿Cómo funciona TurnosAura?
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-10 text-center">

                    <div>
                        <div class="text-4xl mb-4">1️⃣</div>
                        <h3 class="text-xl font-semibold mb-2">Creás tu cuenta</h3>
                        <p class="text-slate-600">Profesional o centro, en menos de un minuto.</p>
                    </div>

                    <div>
                        <div class="text-4xl mb-4">2️⃣</div>
                        <h3 class="text-xl font-semibold mb-2">Configurás tu agenda</h3>
                        <p class="text-slate-600">Horarios, especialidades, salas y más.</p>
                    </div>

                    <div>
                        <div class="text-4xl mb-4">3️⃣</div>
                        <h3 class="text-xl font-semibold mb-2">Empezás a recibir turnos</h3>
                        <p class="text-slate-600">Los pacientes reservan online y reciben recordatorios.</p>
                    </div>

                </div>
            </div>
        </section>

        <!-- SECCIÓN: TESTIMONIOS -->
        <section class="py-16 lg:py-20 bg-slate-50">
            <div class="max-w-6xl mx-auto px-6">
                <h2 class="text-3xl font-bold text-slate-900 text-center mb-12">
                    Profesionales que confían en TurnosAura
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <p class="text-slate-700 italic">
                            “Desde que uso TurnosAura, mis pacientes llegan más organizados y yo trabajo más tranquilo.”
                        </p>
                        <p class="mt-4 font-semibold text-slate-900">Dr. Juan Pérez</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <p class="text-slate-700 italic">
                            “El sistema es simple, rápido y mis secretarias lo aprendieron en un día.”
                        </p>
                        <p class="mt-4 font-semibold text-slate-900">Centro KinePlus</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <p class="text-slate-700 italic">
                            “Mis pacientes aman los recordatorios automáticos. Yo también.”
                        </p>
                        <p class="mt-4 font-semibold text-slate-900">Lic. María Gómez</p>
                    </div>

                </div>
            </div>
        </section>

        <!-- CTA FINAL -->
        <section class="py-16 lg:py-20 bg-white text-center">
            <h2 class="text-3xl font-bold text-slate-900 mb-4">
                Empezá a organizar tus turnos hoy
            </h2>
            <p class="text-slate-600 mb-8">
                Miles de pacientes y profesionales ya confían en TurnosAura.
            </p>
            <a href="auth/login.php"
               class="inline-block bg-slate-900 text-white px-10 py-4 rounded-lg text-lg shadow hover:bg-slate-800 transition">
                Crear cuenta
            </a>
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

// 4) Slug de profesional
if (preg_match('/^[a-z0-9-]+$/', $request)) {
    header("Location: /public/profesional-landing.php?slug=" . $request);
    exit;
}

// 5) Cargar archivo normal
$path = __DIR__ . '/' . $request;

if (file_exists($path)) {
    require $path;
} else {
    http_response_code(404);
    echo "Página no encontrada.";
}


