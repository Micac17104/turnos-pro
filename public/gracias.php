<?php
session_start();
$data = $_SESSION['last_booking'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Turno confirmado</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">

<div class="max-w-md mx-auto py-16 px-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center">
        <h1 class="text-2xl font-bold mb-2">Turno confirmado</h1>

        <?php if ($data): ?>
            <p class="text-slate-600 mb-4">
                Tu turno con <strong><?= htmlspecialchars($data['pro_name']) ?></strong> fue reservado correctamente.
            </p>
            <div class="bg-slate-100 rounded-xl px-4 py-3 mb-4">
                <?= date('d/m/Y', strtotime($data['date'])) ?> — <?= htmlspecialchars($data['time']) ?> hs
            </div>

            <?php if ($data['whatsapp_enabled'] && $data['telefono_normalizado']): ?>
                <?php
                $url_whatsapp = "https://api.whatsapp.com/send?phone={$data['telefono_normalizado']}&text=" . urlencode($data['mensaje_final']);
                ?>
                <a href="<?= $url_whatsapp ?>" target="_blank"
                   class="block w-full mb-3 px-4 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-500">
                    Enviar confirmación por WhatsApp
                </a>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-slate-600 mb-4">Tu turno fue registrado.</p>
        <?php endif; ?>

        <a href="/turnos-pro/public/paciente-dashboard.php"
           class="text-sm text-slate-500 hover:text-slate-700">
            ← Ir a mis turnos
        </a>
    </div>
</div>

</body>
</html>