<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear cuenta - TurnosPro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Estilos propios -->
    <link rel="stylesheet" href="/pro/assets/css/app.css">
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md border border-slate-200 text-center">

        <h2 class="text-2xl font-bold text-slate-900 mb-2">
            Crear cuenta
        </h2>

        <p class="text-slate-600 text-sm mb-6">
            Elegí el tipo de cuenta
        </p>

        <a href="register-profesional.php"
           class="block w-full bg-slate-900 text-white py-2 rounded-lg hover:bg-slate-800 transition mb-3">
            Profesional individual
        </a>

        <a href="register-centro.php"
           class="block w-full bg-emerald-600 text-white py-2 rounded-lg hover:bg-emerald-700 transition">
            Centro médico
        </a>

        <p class="mt-6 text-sm">
            <a href="login.php" class="text-slate-900 hover:underline">
                Volver al login
            </a>
        </p>

    </div>

</body>
</html>