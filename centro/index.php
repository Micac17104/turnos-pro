<?php
require __DIR__ . '/../config.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    die("Centro no encontrado.");
}

// Hacer que centro-landing.php reciba el slug
$_GET['slug'] = $slug;

require __DIR__ . '/centro-landing.php';
