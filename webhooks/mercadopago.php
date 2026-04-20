<?php
// ===============================
// CONFIG INICIAL
// ===============================
ini_set('display_errors', 0);
error_reporting(E_ALL);

$log = __DIR__ . "/log.txt";

file_put_contents($log, "\n====================\n", FILE_APPEND);
file_put_contents($log, "ENTRO AL WEBHOOK\n", FILE_APPEND);

// ===============================
// CONEXIÓN
// ===============================
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Payment;
use MercadoPago\Preapproval;

// ⚠️ TU ACCESS TOKEN
SDK::setAccessToken("APP_USR-2199782378550930-041311-34b2c0ffa4f9d11ea7bf9a45982b8bdf-745664297");

// ===============================
// LEER EVENTO
// ===============================
$log = __DIR__ . "/log.txt";

file_put_contents($log, "\n====================\n", FILE_APPEND);
file_put_contents($log, "ENTRO AL WEBHOOK\n", FILE_APPEND);

// TODO el request
file_put_contents($log, "METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
file_put_contents($log, "GET: " . json_encode($_GET) . "\n", FILE_APPEND);
file_put_contents($log, "POST: " . json_encode($_POST) . "\n", FILE_APPEND);

// RAW real
$raw = file_get_contents("php://input");
file_put_contents($log, "RAW: $raw\n", FILE_APPEND);

$data = json_decode($raw, true);

if (!$data) {
    file_put_contents($log, "ERROR: JSON inválido\n", FILE_APPEND);
    http_response_code(200);
    exit;
}

$tipo = $data["type"] ?? $_GET["type"] ?? null;
$payment_id = $data["data"]["id"] ?? $_GET["data.id"] ?? null;
file_put_contents($log, "EVENTO: $tipo\n", FILE_APPEND);

// ===============================
// 1) PAGOS
// ===============================
if ($tipo === "payment") {

    $payment_id = $data["data"]["id"] ?? null;
    file_put_contents($log, "PAYMENT ID: $payment_id\n", FILE_APPEND);

    if ($payment_id && is_numeric($payment_id)) {

        try {
            $payment = Payment::find_by_id($payment_id);

            if (!$payment) {
                file_put_contents($log, "ERROR: Payment no encontrado\n", FILE_APPEND);
                exit;
            }

            file_put_contents($log, "STATUS: " . $payment->status . "\n", FILE_APPEND);

            if ($payment->status === "approved") {

                $email = $payment->payer->email ?? null;

                if ($email) {

                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET 
                            is_active = 1,
                            mp_subscription_status = 'active',
                            subscription_end = DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
                            last_payment = NOW()
                        WHERE email = ?
                    ");
                    $stmt->execute([$email]);

                    file_put_contents($log, "✅ USUARIO ACTIVADO: $email\n", FILE_APPEND);
                } else {
                    file_put_contents($log, "ERROR: email vacío\n", FILE_APPEND);
                }
            }

        } catch (Exception $e) {
            file_put_contents($log, "ERROR PAYMENT: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }

    http_response_code(200);
    exit;
}

// ===============================
// 2) SUSCRIPCIONES
// ===============================
if (in_array($tipo, ["preapproval", "subscription_preapproval"])) {

    $preapproval_id = $data["data"]["id"] ?? null;
    file_put_contents($log, "PREAPPROVAL ID: $preapproval_id\n", FILE_APPEND);

    if ($preapproval_id && is_numeric($preapproval_id)) {

        try {
            $pre = Preapproval::find_by_id($preapproval_id);

            if (!$pre) {
                file_put_contents($log, "ERROR: Preapproval no encontrado\n", FILE_APPEND);
                exit;
            }

            $email  = $pre->payer_email ?? null;
            $status = $pre->status ?? null;

            file_put_contents($log, "STATUS: $status EMAIL: $email\n", FILE_APPEND);

            if ($email) {

                if (in_array($status, ["authorized", "active"])) {

                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET 
                            is_active = 1,
                            mp_subscription_status = 'active',
                            subscription_end = DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
                        WHERE email = ?
                    ");
                    $stmt->execute([$email]);

                    file_put_contents($log, "✅ ACTIVADO: $email\n", FILE_APPEND);

                } elseif (in_array($status, ["cancelled", "paused"])) {

                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET 
                            is_active = 0,
                            mp_subscription_status = 'inactive'
                        WHERE email = ?
                    ");
                    $stmt->execute([$email]);

                    file_put_contents($log, "❌ DESACTIVADO: $email\n", FILE_APPEND);
                }

            } else {
                file_put_contents($log, "ERROR: email vacío\n", FILE_APPEND);
            }

        } catch (Exception $e) {
            file_put_contents($log, "ERROR PREAPPROVAL: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }

    http_response_code(200);
    exit;
}

// ===============================
// 3) IGNORAR OTROS EVENTOS
// ===============================
file_put_contents($log, "IGNORADO: $tipo\n", FILE_APPEND);

http_response_code(200);
exit;