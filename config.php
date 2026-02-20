<?php
echo "<pre>";
echo "HOST: " . getenv("MYSQLHOST") . "\n";
echo "DB: " . getenv("MYSQLDATABASE") . "\n";
echo "USER: " . getenv("MYSQLUSER") . "\n";
echo "PASS: " . (getenv("MYSQLPASSWORD") ? "OK" : "VACIO") . "\n";
echo "PORT: " . getenv("MYSQLPORT") . "\n";
echo "</pre>";

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
    die("Error de conexión: " . $e->getMessage());
}