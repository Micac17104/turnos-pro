<?php
require '/app/vendor/autoload.php';

echo class_exists('MercadoPago\SDK') ? 'SDK VIEJO' : 'SDK NUEVO';