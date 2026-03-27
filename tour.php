<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tour del Sistema - TurnosAura</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .video-frame {
            aspect-ratio: 16 / 9;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .video-frame video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>

<body class="bg-slate-50">

    <!-- HEADER -->
    <header class="py-6 bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">TurnosAura</h1>
            <a href="/" class="text-slate-700 hover:underline">Volver al inicio</a>
        </div>
    </header>

    <!-- HERO -->
    <section class="py-20 text-center">
        <h2 class="text-4xl font-bold text-slate-900 mb-4">Recorrido del sistema</h2>
        <p class="text-slate-600 max-w-2xl mx-auto">
            Mirá cómo funciona TurnosAura desde adentro: panel del profesional, panel del centro y flujo real de pacientes.
        </p>
    </section>

    <!-- PANEL PROFESIONAL -->
    <section class="py-16 bg-white">
        <div class="max-w-5xl mx-auto px-6">

            <h3 class="text-2xl font-bold text-slate-900 mb-8">Panel del Profesional</h3>

            <div class="space-y-12">

                <!-- VIDEO 1 -->
                <div>
                    <h4 class="text-lg font-semibold text-slate-800 mb-3">Gestión de pacientes</h4>
                    <div class="video-frame">
                        <video controls>
                            <source src="/assets/paciente-profesional.mp4" type="video/mp4">
                        </video>
                    </div>
                </div>

                <!-- VIDEO 2 -->
                <div>
                    <h4 class="text-lg font-semibold text-slate-800 mb-3">Pagos y suscripciones</h4>
                    <div class="video-frame">
                        <video controls>
                            <source src="/assets/pago-profesional.mp4" type="video/mp4">
                        </video>
                    </div>
                </div>

                <!-- VIDEO 3 -->
                <div>
                    <h4 class="text-lg font-semibold text-slate-800 mb-3">Preferencias y configuración</h4>
                    <div class="video-frame">
                        <video controls>
                            <source src="/assets/preferencias-profesional.mp4" type="video/mp4">
                        </video>
                    </div>
                </div>

                <!-- FOTOS PROFESIONAL -->
                <div>
                    <h4 class="text-lg font-semibold text-slate-800 mb-4">Vistas del panel profesional</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <img src="/assets/profesional-1.jpeg" class="rounded-xl shadow-md border border-slate-200 object-cover">
                        <img src="/assets/profesional-2.jpeg" class="rounded-xl shadow-md border border-slate-200 object-cover">
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- PANEL DEL CENTRO -->
    <section class="py-16 bg-slate-100">
        <div class="max-w-5xl mx-auto px-6">

            <h3 class="text-2xl font-bold text-slate-900 mb-8">Panel del Centro</h3>

            <div class="space-y-10">

                <!-- VIDEO CENTRO -->
                <div>
                    <h4 class="text-lg font-semibold text-slate-800 mb-3">Gestión integral del centro</h4>
                    <div class="video-frame">
                        <video controls>
                            <source src="/assets/video-centro.mp4" type="video/mp4">
                        </video>
                    </div>
                </div>

                <!-- FOTOS CENTRO -->
                <div>
                    <h4 class="text-lg font-semibold text-slate-800 mb-4">Vistas del panel del centro</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <img src="/assets/centro-1.jpeg" class="rounded-xl shadow-md border border-slate-200 object-cover">
                        <img src="/assets/centro-2.jpeg" class="rounded-xl shadow-md border border-slate-200 object-cover">
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- CTA FINAL -->
    <section class="py-20 text-center">
        <h3 class="text-3xl font-bold text-slate-900 mb-6">¿Listo para empezar?</h3>
        <a href="/auth/register-type.php"
           class="px-10 py-4 bg-slate-900 text-white rounded-xl text-lg font-semibold shadow-md hover:bg-slate-800 transition">
            Crear cuenta ahora →
        </a>
    </section>

</body>
</html>