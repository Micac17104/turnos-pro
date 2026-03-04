<?php
$host = getenv("MYSQLHOST");
$dbname = getenv("MYSQLDATABASE");
$user = getenv("MYSQLUSER");
$pass = getenv("MYSQLPASSWORD");
$port = getenv("MYSQLPORT");

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    file_put_contents(__DIR__ . "/cron-log.txt", "ERROR DB: " . $e->getMessage() . "\n", FILE_APPEND);
    die("Error de conexión: " . $e->getMessage());
}