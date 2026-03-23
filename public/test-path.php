<?php
echo "DIR: " . __DIR__ . "<br>";
echo "ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "FILES:<br>";

foreach (scandir(__DIR__ . '/../') as $f) {
    echo $f . "<br>";
}