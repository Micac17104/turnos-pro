<?php
// /pro/includes/auth.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];