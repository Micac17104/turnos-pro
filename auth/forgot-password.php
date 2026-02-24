<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña - TurnosPro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Estilos propios -->
    <link rel="stylesheet" href="/pro/assets/css/app.css">
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md border border-slate-200">

        <h2 class="text-2xl font-bold text-slate-900 mb-4 text-center">
            Recuperar contraseña
        </h2>

        <p class="text-slate-600 text-sm mb-6 text-center">
            Ingresá tu email y te enviaremos un enlace para restablecerla.
        </p>

        <form method="post" action="send-reset-email.php" class="space-y-4">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:outline-none"
                       required>
            </div>

            <button type="submit"
                    class="w-full bg-slate-900 text-white py-2 rounded-lg hover:bg-slate-800 transition">
                Enviar enlace
            </button>

        </form>

        <p class="mt-4 text-center text-sm">
            <a href="login.php" class="text-slate-900 hover:underline">
                Volver al login
            </a>
        </p>

    </div>

</body>
</html>