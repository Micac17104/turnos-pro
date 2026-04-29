<?php
require __DIR__ . '/../config.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    die("Centro no encontrado.");
}

// Cargar la landing real del centro
require __DIR__ . '/centro-landing.php';
