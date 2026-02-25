<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || 
   !in_array($_SESSION['account_type'], ['center', 'secretary'])) {

    header("Location: /auth/login.php");
    exit;
}

$center_id = $_SESSION['user_id'];