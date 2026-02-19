<?php
session_start();
require __DIR__ . '/../../config.php';

// Detectar si estoy en _template o en un tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

$fecha = $_GET['fecha'] ?? null;
$hora  = $_GET['hora'] ?? null;

if (!$fecha || !$hora) {
    die("Datos incompletos.");
}

// ---------------------------------------------------------
// SI EL CLIENTE YA ESTÁ LOGUEADO → SALTAR PANTALLA INICIAL
// ---------------------------------------------------------
if (isset($_SESSION['cliente_id']) && !isset($_GET['modo'])) {
    header("Location: /turnos-pro/profiles/$user_id/reservar.php?fecha=$fecha&hora=$hora&modo=cuenta");
    exit;
}

// ---------------------------------------------------------
// 1) PANTALLA INICIAL (elige modo)
// ---------------------------------------------------------
if (!isset($_GET['modo'])) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar turno</title>
    <link rel="stylesheet" href="/turnos-pro/assets/style.css">

    <style>
        .opciones-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 30px;
        }
        .card-opcion {
            padding: 25px;
            border-radius: 15px;
            background: white;
            box-shadow: 0 10px 25px rgba(15,23,42,0.06);
            text-align: center;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            color: #0f172a;
            border: 1px solid rgba(148,163,184,0.25);
        }
        .card-opcion:hover {
            transform: scale(1.03);
        }
    </style>
</head>
<body>

<div class="container" style="max-width:500px; margin:40px auto;">

    <h2>Reservar turno</h2>

    <p><strong>Fecha:</strong> <?= date("d/m/Y", strtotime($fecha)) ?></p>
    <p><strong>Hora:</strong> <?= $hora ?> hs</p>

    <div class="opciones-container">

        <a href="/turnos-pro/profiles/<?= $user_id ?>/reservar.php?fecha=<?= $fecha ?>&hora=<?= $hora ?>&modo=sin_cuenta"
           class="card-opcion">
            <h3>Reservar sin cuenta</h3>
            <p>Completá tus datos y listo</p>
        </a>

        <a href="/turnos-pro/profiles/<?= $user_id ?>/reservar.php?fecha=<?= $fecha ?>&hora=<?= $hora ?>&modo=cuenta"
           class="card-opcion">
            <h3>Crear cuenta / Iniciar sesión</h3>
            <p>Guardamos tus datos para la próxima</p>
        </a>

    </div>

</div>

</body>
</html>
<?php
exit;
}

// ---------------------------------------------------------
// 2) MODO: RESERVAR SIN CUENTA
// ---------------------------------------------------------
if ($_GET['modo'] === 'sin_cuenta') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $nombre      = trim($_POST['nombre']);
        $apellido    = trim($_POST['apellido']);
        $telefono    = trim($_POST['telefono']);
        $email       = trim($_POST['email']);
        $obra_social = trim($_POST['obra_social']);

        // Guardar cliente sin cuenta
        $stmt = $pdo->prepare("
            INSERT INTO clientes (nombre, apellido, telefono, email, obra_social)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nombre, $apellido, $telefono, $email, $obra_social]);

        $client_id = $pdo->lastInsertId();

        // Guardar turno
        $stmt = $pdo->prepare("
            INSERT INTO appointments (user_id, client_id, date, time)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $client_id, $fecha, $hora]);

        header("Location: /turnos-pro/profiles/$user_id/confirmacion.php?fecha=$fecha&hora=$hora");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar sin cuenta</title>
    <link rel="stylesheet" href="/turnos-pro/assets/style.css">
</head>
<body>

<div class="container" style="max-width:500px; margin:40px auto;">

    <h2>Reservar sin cuenta</h2>

    <form method="post">

        <label>Nombre:</label>
        <input type="text" name="nombre" required>

        <label>Apellido:</label>
        <input type="text" name="apellido" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Obra social:</label>
        <input type="text" name="obra_social" placeholder="OSDE, Swiss Medical, Particular">

        <button type="submit" class="btn-big" style="margin-top:20px;">Confirmar reserva</button>
    </form>

</div>

</body>
</html>
<?php
exit;
}

// ---------------------------------------------------------
// 3) MODO: RESERVAR CON CUENTA
// ---------------------------------------------------------
if ($_GET['modo'] === 'cuenta') {

    // Si no está logueado → login
    if (!isset($_SESSION['cliente_id'])) {
        header("Location: /turnos-pro/profiles/$user_id/cliente-login.php?fecha=$fecha&hora=$hora");
        exit;
    }

    $cliente_id = $_SESSION['cliente_id'];

    // Guardar turno
    $stmt = $pdo->prepare("
        INSERT INTO appointments (user_id, client_id, date, time)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $cliente_id, $fecha, $hora]);

    header("Location: /turnos-pro/profiles/$user_id/confirmacion.php?fecha=$fecha&hora=$hora");
    exit;
}
?>