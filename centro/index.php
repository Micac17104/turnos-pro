<?php
require __DIR__ . '/../config.php';

// El slug viene desde el .htaccess
$slug = $_GET['slug'] ?? null;

if (!$slug) {
    die("Centro no encontrado.");
}

// Pasamos el slug a la landing
$slug_centro = $slug;

// Cargar la landing
require __DIR__ . '/centro-landing.php';
 