<?php
require __DIR__ . '/../config.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    die("Centro no encontrado.");
}

// PASAR EL SLUG A centro-landing.php
$_GET['slug'] = $slug;

require __DIR__ . '/centro-landing.php';
