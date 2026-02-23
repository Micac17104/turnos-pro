<?php
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">
    <h1>Dashboard test sin consultas</h1>
    <?php
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments
    WHERE user_id = ?
      AND date IS NOT NULL
      AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$user_id]);
$turnos_mes = $stmt->fetchColumn() ?: 0;
?>

<p>Turnos del mes: <?= $turnos_mes ?></p>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>