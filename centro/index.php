<?php
require __DIR__ . '/../config.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    die("Centro no encontrado.");
}

$slug_centro = $slug;

require __DIR__ . '/centro-landing.php';
