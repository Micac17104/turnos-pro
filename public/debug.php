<?php
echo "<pre>";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "DIR: " . __DIR__ . "\n";

echo "\nContenido de DOCUMENT_ROOT:\n";
print_r(scandir($_SERVER['DOCUMENT_ROOT']));

echo "\nContenido de /app:\n";
if (is_dir('/app')) print_r(scandir('/app'));

echo "\nContenido de /workspace:\n";
if (is_dir('/workspace')) print_r(scandir('/workspace'));

echo "\nContenido de /railway:\n";
if (is_dir('/railway')) print_r(scandir('/railway'));

echo "</pre>";