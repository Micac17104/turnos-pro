<?php

$paths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
    '/app/vendor/autoload.php',
    '/vendor/autoload.php'
];

foreach ($paths as $p) {
    echo $p . ' => ' . (file_exists($p) ? 'ENCONTRADO' : 'NO') . "<br>";
}