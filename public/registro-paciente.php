<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre   = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $dni      = $_POST['dni'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Verificar si ya existe una cuenta creada por el paciente
    $stmt = $pdo->prepare("SELECT id FROM clients WHERE email = ? AND user_id != 0");
    $stmt->execute([$email]);
    $cuentaExistente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cuentaExistente) {
        $mensaje = "Ya existe una cuenta creada con ese email.";
    } else {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        // 1️⃣ Buscar si el centro ya creó a este paciente por DNI o email
        $stmt = $pdo->prepare("
            SELECT * FROM clients 
            WHERE dni = ? OR email = ?
            LIMIT 1
        ");
        $stmt->execute([$dni, $email]);
        $clienteCentro = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($clienteCentro) {

            // 2️⃣ Si existe, lo vinculamos al usuario recién creado
            $stmt = $pdo->prepare("
                UPDATE clients
                SET user_id = ?, name = ?, phone = ?, dni = ?, email = ?, password = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $clienteCentro['id'], // user_id = id del cliente del centro
                $nombre,
                $telefono,
                $dni,
                $email,
                $hash,
                $clienteCentro['id']
            ]);

            // 3️⃣ Guardamos en sesión el ID del cliente del centro
            $_SESSION['paciente_id'] = $clienteCentro['id'];
            $_SESSION['paciente_nombre'] = $nombre;

        } else {

            // 4️⃣ Si no existe, lo creamos como nuevo paciente
            $stmt = $pdo->prepare("
                INSERT INTO clients (user_id, name, phone, dni, email, password)
                VALUES (0, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nombre, $telefono, $dni, $email, $hash]);

            $_SESSION['paciente_id'] = $pdo->lastInsertId();
            $_SESSION['paciente_nombre'] = $nombre;
        }

        header("Location: paciente-dashboard.php");
        exit;
    }
}
?>
