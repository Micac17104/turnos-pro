<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Payment;
use MercadoPago\Preapproval;

SDK::setAccessToken("APP_USR-2199782378550930-041311-34b2c0ffa4f9d11ea7bf9a45982b8bdf-745664297");

// Leer JSON crudo
$raw = file_get_contents("php://input");
file_put_contents("log.txt", "RAW: $raw\n", FILE_APPEND);

$data = json_decode($raw, true);

if (!$data) {
    file_put_contents("log.txt", "ERROR: JSON inválido\n", FILE_APPEND);
    http_response_code(200);
    exit;
}

$tipo = $data["type"] ?? null;
file_put_contents("log.txt", "EVENTO RECIBIDO: $tipo\n", FILE_APPEND);

/* ============================================================
   1) EVENTO DE PAGO REAL (payment.approved)
   ============================================================ */
if ($tipo === "payment") {

    $payment_id = $data["data"]["id"] ?? null;
    file_put_contents("log.txt", "PAGO RECIBIDO ID=$payment_id\n", FILE_APPEND);

    if ($payment_id) {
        try {
            $payment = Payment::find_by_id($payment_id);

            if ($payment && $payment->status === "approved") {

                $email = $payment->payer->email;
                file_put_contents("log.txt", "PAGO APROBADO DE $email\n", FILE_APPEND);

                // Activar usuario por email
                $stmt = $pdo->prepare("
                    UPDATE users
                    SET 
                        is_active = 1,
                        mp_subscription_status = 'active',
                        subscription_end = DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
                    WHERE email = ?
                ");
                $stmt->execute([$email]);

                file_put_contents("log.txt", "USUARIO ACTIVADO: $email\n", FILE_APPEND);
            }

        } catch (Exception $e) {
            file_put_contents("log.txt", "ERROR PAYMENT: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }

    http_response_code(200);
    exit;
}

/* ============================================================
   2) EVENTOS DE SUSCRIPCIÓN (preapproval)
   ============================================================ */
if (in_array($tipo, ["preapproval", "subscription_preapproval"])) {

    $preapproval_id = $data["data"]["id"] ?? null;
    file_put_contents("log.txt", "PREAPPROVAL ID=$preapproval_id\n", FILE_APPEND);

    if ($preapproval_id) {
        try {
            $pre = Preapproval::find_by_id($preapproval_id);

            if ($pre) {
                $email = $pre->payer_email;
                $status = $pre->status;

                file_put_contents("log.txt", "PREAPPROVAL STATUS=$status EMAIL=$email\n", FILE_APPEND);

                if ($status === "authorized" || $status === "active") {

                    // Activar usuario
                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET 
                            is_active = 1,
                            mp_subscription_status = 'active',
                            subscription_end = DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
                        WHERE email = ?
                    ");
                    $stmt->execute([$email]);

                    file_put_contents("log.txt", "USUARIO ACTIVADO POR PREAPPROVAL: $email\n", FILE_APPEND);

                } elseif ($status === "cancelled") {

                    // Desactivar usuario
                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET 
                            is_active = 0,
                            mp_subscription_status = 'inactive'
                        WHERE email = ?
                    ");
                    $stmt->execute([$email]);

                    file_put_contents("log.txt", "USUARIO DESACTIVADO POR CANCELACIÓN: $email\n", FILE_APPEND);
                }
            }

        } catch (Exception $e) {
            file_put_contents("log.txt", "ERROR PREAPPROVAL: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }

    http_response_code(200);
    exit;
}

/* ============================================================
   3) CUALQUIER OTRO EVENTO → IGNORAR
   ============================================================ */
file_put_contents("log.txt", "IGNORADO type=$tipo\n", FILE_APPEND);
http_response_code(200);
exit;
