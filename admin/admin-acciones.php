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
        $stmt = $pdo->prepare("
            UPDATE users 
            SET 
                is_active = 1,
                subscription_start = IFNULL(subscription_start, CURDATE()),
                subscription_end = 
                    CASE 
                        WHEN subscription_end IS NULL OR subscription_end < CURDATE()
                        THEN DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
                        ELSE DATE_ADD(subscription_end, INTERVAL 1 MONTH)
                    END
            WHERE id = ?
        ");
        $stmt->execute([$id]);
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