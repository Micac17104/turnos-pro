<?php
session_start();
require __DIR__ . '/includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;

$stmt = $pdo->prepare("SELECT account_type, subscription_end FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$is_prof = $user['account_type'] === 'professional';

// Fecha actual
$today = date('Y-m-d');

// ¿La suscripción está vencida?
$expired = ($user['subscription_end'] < $today);
?>

<h2>Tu suscripción está <?= $expired ? 'inactiva' : 'activa' ?></h2>

<?php if ($expired): ?>

    <?php if ($is_prof): ?>
        <a href="/pro/suscribirse-profesional.php" class="btn btn-primary">
            Suscribirme al plan profesional
        </a>
    <?php else: ?>
        <a href="/centro/planes.php" class="btn btn-primary">
            Suscribirme al plan del centro
        </a>
    <?php endif; ?>

<?php else: ?>
    <p>Ya tenés una suscripción activa.</p>
<?php endif; ?>
