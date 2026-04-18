<?php
// --- FIX DEFINITIVO PARA RAILWAY ---
$path = __DIR__ . '/../sessions';

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

if (!is_writable($path)) {
    chmod($path, 0777);
}

session_save_path($path);
session_start();
// -----------------------------------

require __DIR__ . '/../config.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    /*
    ---------------------------------------------------------
     1️⃣ BUSCAR PACIENTE POR EMAIL (VINCULADO O NO)
    ---------------------------------------------------------
    */
    $stmt = $pdo->prepare("
        SELECT * FROM clients 
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        $mensaje = "Email o contraseña incorrectos.";
    } else {

        // Si no tiene contraseña → no puede loguear
        if (empty($paciente['password'])) {
            $mensaje = "Tu cuenta aún no tiene contraseña. Si sos paciente cargado por un profesional, creá tu cuenta desde el enlace que te enviaron.";

        } elseif (!password_verify($password, $paciente['password'])) {
            $mensaje = "Email o contraseña incorrectos.";

        } else {

            /*
            ---------------------------------------------------------
             2️⃣ SI NO ESTÁ VINCULADO, VINCULAR AHORA
            ---------------------------------------------------------
            */
            if ($paciente['user_id'] == 0) {

                $stmt2 = $pdo->prepare("
                    UPDATE clients
                    SET user_id = ?
                    WHERE id = ?
                ");
                $stmt2->execute([$paciente['id'], $paciente['id']]);
            }

            /*
            ---------------------------------------------------------
             3️⃣ GUARDAR EL client_id REAL EN SESIÓN
            ---------------------------------------------------------
            */
            $_SESSION['paciente_id'] = $paciente['id'];
            $_SESSION['paciente_nombre'] = $paciente['name'];

            header("Location: paciente-dashboard.php");
            exit;
        }
    }
}
?>
