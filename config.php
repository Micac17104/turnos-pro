<?php
echo "DIR ACTUAL: " . __DIR__ . "<br>";
echo "INTENTANDO CARGAR: " . realpath(__DIR__ . '/../config.php') . "<br>";
exit;


// ===============================
// Cargar App Password de Gmail
// ===============================
putenv("GMAIL_APP_PASSWORD=qwom wmrp ckhz jmqt");
// Ejemplo: putenv("GMAIL_APP_PASSWORD=abcd efgh ijkl mnop");

// ===============================
// Conexión a Railway
// ===============================
$host = "mysql.railway.internal";
$dbname = "railway";
$user = "root";
$pass = "yjdBrLZlWrojESOUCJNSIySVjkcqAlXf"; // tu contraseña
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