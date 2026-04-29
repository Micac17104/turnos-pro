<?php
echo "INDEX OK<br>";

echo "Buscando archivo: " . __DIR__ . "/centro-landing.php<br>";

if (file_exists(__DIR__ . "/centro-landing.php")) {
    echo "ARCHIVO ENCONTRADO";
} else {
    echo "ARCHIVO NO ENCONTRADO";
}

exit;
 