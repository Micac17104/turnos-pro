<?php
echo "CONFIG REAL EJECUTADO";
exit;

putenv("GMAIL_APP_PASSWORD=qwom wmrp ckhz jmqt");

$host = "mysql.railway.internal";
$dbname = "railway";
$user = "root";
$pass = "yjdBrLZlWrojESOUCJNSIySVjkcqAlXf";
$port = 3306;

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
