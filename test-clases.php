<?php
require '/app/vendor/autoload.php';

echo "Config: " . (class_exists('MercadoPago\MercadoPagoConfig') ? "SI" : "NO") . "<br>";
echo "Client: " . (class_exists('MercadoPago\Client\Preapproval\PreapprovalClient') ? "SI" : "NO") . "<br>";
echo "SDK viejo: " . (class_exists('MercadoPago\SDK') ? "SI" : "NO") . "<br>";