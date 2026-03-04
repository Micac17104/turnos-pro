<?php
require __DIR__ . '/../config.php';

$q = $_GET['q'] ?? '';

if (strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT DISTINCT city
    FROM users
    WHERE city LIKE ?
      AND city IS NOT NULL
      AND city != ''
    ORDER BY city ASC
    LIMIT 10
");

$stmt->execute(["%$q%"]);
$cities = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($cities);