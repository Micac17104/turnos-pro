<?php
session_start();
require __DIR__ . '/includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;

$stmt = $pdo->prepare("SELECT account_type, mp_subscription_status FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$is_prof = $user['account_type'] === 'professional';
?>

<h2>Tu suscripción está inactiva</h2>

<?php if ($user['mp_subscription_status'] !== 'active'): ?>

    <?php if ($is_prof): ?>
        <a href="/pro/suscribirse-profesional.php" class="btn btn-primary">
            Suscribirme al plan profesional
        </a>
    <?php else: ?>
        <a href="LINK_DEL_PLAN_DEL_CENTRO" class="btn btn-primary">
            Suscribirme al plan del centro
        </a>
    <?php endif; ?>

<?php else: ?>
    <p>Ya tenés una suscripción activa.</p>
<?php endif; ?>
