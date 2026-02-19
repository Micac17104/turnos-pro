<?php
session_start();
require __DIR__ . '/../../config.php';

// Detectar si estoy en _template o en un tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

// Validar login del profesional
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

$patient_id = $_GET['id'] ?? null;

if (!$patient_id) {
    die("Paciente no encontrado.");
}

// Datos del paciente (validación multi-tenant)
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$patient_id, $user_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente no pertenece a este profesional.");
}

// Datos clínicos extra
$stmt = $pdo->prepare("SELECT * FROM patients_extra WHERE patient_id = ?");
$stmt->execute([$patient_id]);
$extra = $stmt->fetch(PDO::FETCH_ASSOC);

// Evoluciones clínicas
$stmt = $pdo->prepare("
    SELECT * FROM clinical_records
    WHERE patient_id = ? AND user_id = ?
    ORDER BY fecha DESC
");
$stmt->execute([$patient_id, $user_id]);
$evoluciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historia Clínica</title>

    <link rel="stylesheet" href="/turnos-pro/assets/style.css">

    <style>
        .page-box { max-width: 850px; margin: 40px auto; }
        h1 { color: #0f172a; margin-bottom: 20px; }
        .card {
            background: #ffffff;
            padding: 20px;
            border-radius: 14px;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
            border: 1px solid rgba(148, 163, 184, 0.25);
        }
        .section-title-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .evolucion {
            background: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
        }
        .evolucion h3 { margin: 0 0 10px 0; color: #0f172a; }
        .btn-primary {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            padding: 10px 18px;
            border-radius: 999px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: 0.2s ease;
        }
        .btn-primary:hover { filter: brightness(1.05); }
        .btn-ghost {
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid #94a3b8;
            color: #334155;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s ease;
        }
        .btn-ghost:hover { background: #e2e8f0; }
        ul { padding-left: 18px; }

        .editar-btn {
    margin-top: 20px;
    display: inline-block;
}

.adjuntar-btn {
    margin-top: 15px;
    display: inline-block;
}

.hc-nav {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
}
    </style>
</head>
<body>

<div class="page-box">

    <h1>Historia Clínica de <?= htmlspecialchars($paciente['name']) ?></h1>

<div class="hc-nav">
    <a href="/turnos-pro/profiles/<?= $user_id ?>/paciente-historia.php?id=<?= $patient_id ?>" class="btn-ghost">Historia</a>
    <a href="/turnos-pro/profiles/<?= $user_id ?>/paciente-editar-clinico.php?id=<?= $patient_id ?>" class="btn-ghost">Datos clínicos</a>
    <a href="/turnos-pro/profiles/<?= $user_id ?>/nueva-evolucion.php?patient_id=<?= $patient_id ?>" class="btn-ghost">Nueva evolución</a>
</div>

    <!-- DATOS CLÍNICOS -->
    <div class="card">
        <h2>Datos clínicos</h2>

        <p><strong>Antecedentes:</strong> <?= nl2br($extra['antecedentes'] ?? 'No registrado') ?></p>
        <p><strong>Alergias:</strong> <?= nl2br($extra['alergias'] ?? 'No registrado') ?></p>
        <p><strong>Medicación:</strong> <?= nl2br($extra['medicacion'] ?? 'No registrado') ?></p>
        <p><strong>Patologías:</strong> <?= nl2br($extra['patologias'] ?? 'No registrado') ?></p>
        <p><strong>Obra social:</strong> <?= $extra['obra_social'] ?? 'No registrado' ?></p>
        <p><strong>Nro afiliado:</strong> <?= $extra['nro_afiliado'] ?? 'No registrado' ?></p>

        <a href="/turnos-pro/profiles/<?= $user_id ?>/paciente-editar-clinico.php?id=<?= $patient_id ?>" 
   class="btn-primary editar-btn">
    Editar datos clínicos
</a>
    </div>

    <!-- EVOLUCIONES -->
    <div class="card">
        <div class="section-title-row">
            <h2>Evoluciones</h2>
            <a href="/turnos-pro/profiles/<?= $user_id ?>/nueva-evolucion.php?patient_id=<?= $patient_id ?>" class="btn-primary">
                Nueva evolución
            </a>
        </div>

        <?php if (empty($evoluciones)): ?>
            <p>No hay evoluciones registradas.</p>
        <?php else: ?>
            <?php foreach ($evoluciones as $e): ?>
                <div class="evolucion">

                    <h3><?= date("d/m/Y H:i", strtotime($e['fecha'])) ?></h3>

                    <p><strong>Motivo:</strong> <?= nl2br($e['motivo']) ?></p>
                    <p><strong>Evolución:</strong> <?= nl2br($e['evolucion']) ?></p>
                    <p><strong>Indicaciones:</strong> <?= nl2br($e['indicaciones']) ?></p>
                    <p><strong>Diagnóstico:</strong> <?= $e['diagnostico'] ?></p>

                    <!-- ARCHIVOS ADJUNTOS -->
                    <?php
                    $stmtFiles = $pdo->prepare("SELECT * FROM clinical_files WHERE record_id = ?");
                    $stmtFiles->execute([$e['id']]);
                    $archivos = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php if (!empty($archivos)): ?>
                        <div style="margin-top:15px;">
                            <strong>Archivos adjuntos:</strong>
                            <ul>
                                <?php foreach ($archivos as $f): ?>
                                    <li>
                                        <a href="/turnos-pro/uploads/<?= $f['file_path'] ?>" target="_blank">
                                            <?= htmlspecialchars($f['file_name']) ?>
                                        </a>

                                        <a href="/turnos-pro/profiles/<?= $user_id ?>/eliminar-archivo.php?id=<?= $f['id'] ?>"
                                           style="color:red; margin-left:10px;"
                                           onclick="return confirm('¿Eliminar archivo?');">
                                            Eliminar
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <a href="/turnos-pro/profiles/<?= $user_id ?>/adjuntar-archivo.php?record_id=<?= $e['id'] ?>" 
   class="btn-ghost adjuntar-btn">
    Adjuntar archivo
</a>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

</body>
</html>