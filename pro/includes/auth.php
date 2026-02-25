<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'professional') {
    header("Location: /auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];