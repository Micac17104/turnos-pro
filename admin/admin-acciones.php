<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../pro/includes/db.php';

// Solo admin
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'admin') {
    die("Acceso denegado");
}

$action = $_GET['action'] ?? null;
$id = (int)($_GET['id'] ?? 0);

if (!$action || !$id) {
    die("Parámetros inválidos");
}

switch ($action) {

    case 'activar':

    // Obtener tipo de cuenta y plan
    $stmtUser = $pdo->prepare("SELECT account_type, chosen_plan FROM users WHERE id = ?");
    $stmtUser->execute([$id]);
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

    $limite = 1; // default

    // SOLO si es centro aplicamos límite
    if ($userData['account_type'] === 'center') {

        switch ($userData['chosen_plan']) {
            case '1': $limite = 1; break;
            case '2': $limite = 2; break;
            case '3': $limite = 3; break;
            case '4': $limite = 4; break;
            case '5': $limite = 5; break;
            default:  $limite = 1;
        }

    } else {
        // Profesional individual → sin límite (o 1 si querés ser estricta)
        $limite = 1;
    }

    $stmt = $pdo->prepare("
        UPDATE users 
        SET 
            is_active = 1,
            mp_subscription_status = 'active',
            subscription_end = DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
            max_professionals = ?
        WHERE id = ?
    ");
    $stmt->execute([$limite, $id]);

    break;
    
    case 'desactivar':
        $stmt = $pdo->prepare("
            UPDATE users 
            SET 
                is_active = 0
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        break;

    case 'sumar_mes':
        $stmt = $pdo->prepare("
            UPDATE users 
            SET 
                subscription_end = 
                    CASE 
                        WHEN subscription_end IS NULL
                        THEN DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
                        ELSE DATE_ADD(subscription_end, INTERVAL 1 MONTH)
                    END
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        break;

    default:
        die("Acción no válida");
}

// Volver a suscripciones (no usuarios)
header("Location: /admin/suscripciones.php");
exit;