<?php

// 1) Detectar la URL solicitada
$request = trim($_SERVER['REQUEST_URI'], '/');

// 2) Evitar error fatal cuando Railway pide /favicon.ico
if ($request === 'favicon.ico') {
    http_response_code(204); // Sin contenido
    exit;
}

// 3) Si la URL está vacía → mostrar tu home actual
if ($request === '') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8">
    <title>TurnosPro</title>

    <style>
        body {
            margin:0;
            padding:0;
            background:#f1f5f9;
            font-family:Arial, sans-serif;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
            color:#0f172a;
        }

        .container {
            background:white;
            padding:40px;
            border-radius:20px;
            box-shadow:0 10px 30px rgba(0,0,0,0.08);
            text-align:center;
            width:350px;
        }

        h1 {
            margin-bottom:20px;
            font-size:26px;
            font-weight:700;
        }

        .btn {
            display:block;
            width:100%;
            padding:14px;
            margin-top:15px;
            border-radius:12px;
            text-decoration:none;
            font-size:16px;
            font-weight:600;
            transition:0.2s ease;
        }

        .btn-prof {
            background:linear-gradient(135deg, #0ea5e9, #22c55e);
            color:white;
        }

        .btn-prof:hover {
            opacity:0.9;
        }

        .btn-pac {
            background:#e2e8f0;
            color:#334155;
        }

        .btn-pac:hover {
            background:#cbd5e1;
        }
    </style>

    </head>
    <body>

    <div class="container">
        <h1>TurnosPro</h1>
        <p>Elegí cómo querés ingresar</p>

        <a href="auth/login.php" class="btn btn-prof">Soy profesional o centro</a>
        <a href="public/login-paciente.php" class="btn btn-pac">Soy paciente</a>
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