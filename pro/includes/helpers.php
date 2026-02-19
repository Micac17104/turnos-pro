<?php
// /pro/includes/helpers.php

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function require_param(array $source, string $key, string $errorMessage = 'Datos incompletos.') {
    if (!isset($source[$key]) || trim($source[$key]) === '') {
        die($errorMessage);
    }
    return trim($source[$key]);
}