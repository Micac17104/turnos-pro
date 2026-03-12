<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: /auth/login.php");
    exit;
}