<?php
session_save_path(__DIR__ . '/../sessions');
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

        <div class="mb-4">
            <div class="mx-auto w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-3xl">
                ✓
            </div>
        </div>

        <h1 class="text-2xl font-bold mb-2 text-slate-900">Turno confirmado</h1>

        <?php if ($data): ?>
            <p class="text-slate-600 mb-4">
                Tu turno con <strong><?= htmlspecialchars($data['pro_name']) ?></strong> fue reservado correctamente.
            </p>

            <div class="bg-slate-100 rounded-xl px-4 py-3 mb-6 text-slate-700 font-medium">
                <?= date('d/m/Y', strtotime($data['date'])) ?> — <?= htmlspecialchars($data['time']) ?> hs
            </div>

            <?php if ($data['whatsapp_enabled'] && $data['telefono_normalizado']): ?>
                <?php
                $url_whatsapp = "https://api.whatsapp.com/send?phone={$data['telefono_normalizado']}&text=" . urlencode($data['mensaje_final']);
                ?>
                <a href="<?= $url_whatsapp ?>" target="_blank"
                   class="block w-full mb-4 px-4 py-3 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-500 transition">
                    Enviar confirmación por WhatsApp
                </a>
            <?php endif; ?>

        <?php else: ?>
            <p class="text-slate-600 mb-4">Tu turno fue registrado.</p>
        <?php endif; ?>

        <a href="paciente-dashboard.php"
           class="block mt-4 text-slate-500 hover:text-slate-700 text-sm">
            ← Ir a mis turnos
        </a>

        <a href="/"
           class="block mt-2 text-slate-400 hover:text-slate-600 text-xs">
            Volver al inicio
        </a>

    </div>
</div>

</body>
</html>