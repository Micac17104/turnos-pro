<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/auth-centro.php';
require __DIR__ . '/../pro/includes/helpers.php';
require __DIR__ . '/../pro/includes/db.php';

$center_id = $_SESSION['user_id'];
$patient_id = $_GET['id'] ?? null;

if (!$patient_id) {
    die("Paciente no encontrado.");
}

// Obtener datos del paciente
$stmt = $pdo->prepare("
    SELECT *
    FROM clients
    WHERE id = ? AND center_id = ?
");
$stmt->execute([$patient_id, $center_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("No tenés permiso para ver este paciente.");
}

// Datos clínicos fijos
$stmt = $pdo->prepare("
    SELECT *
    FROM clinical_extra
    WHERE client_id = ?
");
$stmt->execute([$patient_id]);
$extra = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// PREGUNTAS PERSONALIZADAS DEL CENTRO
$stmt = $pdo->prepare("
    SELECT *
    FROM clinical_questions
    WHERE professional_id = ?
    ORDER BY id ASC
");
$stmt->execute([$center_id]);
$preguntas_centro = $stmt->fetchAll(PDO::FETCH_ASSOC);

// RESPUESTAS DEL PACIENTE
$answers_centro = [];
$stmt = $pdo->prepare("
    SELECT question_id, answer
    FROM clinical_answers
    WHERE client_id = ?
");
$stmt->execute([$patient_id]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $answers_centro[$row['question_id']] = $row['answer'];
}

// Packs asignados
$stmt = $pdo->prepare("
    SELECT pc.id, p.name, p.total_sessions, pc.sessions_used
    FROM packs_clients pc
    JOIN packs p ON p.id = pc.pack_id
    WHERE pc.client_id = ?
    ORDER BY pc.id DESC
");
$stmt->execute([$patient_id]);
$packs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Evoluciones
$stmt = $pdo->prepare("
    SELECT e.*, u.name AS profesional
    FROM evoluciones e
    JOIN users u ON u.id = e.professional_id
    WHERE e.client_id = ?
    ORDER BY e.created_at DESC
");
$stmt->execute([$patient_id]);
$evoluciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estudios médicos
$stmt = $pdo->prepare("
    SELECT *
    FROM estudios_medicos
    WHERE client_id = ?
    ORDER BY fecha DESC
");
$stmt->execute([$patient_id]);
$estudios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Historia clínica del paciente</title>
<style>
body{margin:0;font-family:Arial;background:#f1f5f9;}
.card{background:white;border-radius:16px;padding:20px;margin-bottom:20px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input,textarea,select{padding:8px;border-radius:8px;border:1px solid #cbd5e1;width:100%;}
.btn{padding:8px 14px;border-radius:8px;background:#0ea5e9;color:white;text-decoration:none;display:inline-block;margin-right:10px;margin-bottom:10px;}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div style="margin-left:260px; padding:24px;">

<h2 style="margin-bottom:20px;">Historia clínica de <?= h($paciente['name']) ?></h2>

<!-- BOTONES DE ACCESO A MÓDULOS ESTÉTICOS -->
<div style="margin-bottom:20px;">
    <a href="ficha-estetica.php?id=<?= $patient_id ?>" class="btn">Ficha estética</a>
    <a href="tratamientos.php?id=<?= $patient_id ?>" class="btn">Tratamientos realizados</a>
    <a href="planes-estetica.php?id=<?= $patient_id ?>" class="btn">Planes de sesiones</a>
</div>

<!-- NUEVOS BOTONES -->
<div style="margin-bottom:20px;">
    <a href="evolucion-crear.php?id=<?= $patient_id ?>" class="btn" style="background:#10b981;">Agregar evolución</a>
    <a href="estudios.php?id=<?= $patient_id ?>" class="btn" style="background:#6366f1;">Estudios médicos</a>
</div>

<!-- DATOS CLÍNICOS FIJOS -->
<div class="card">
    <h3>Datos clínicos</h3>

    <a href="paciente-datos-editar.php?id=<?= $patient_id ?>" class="btn">
        Editar datos clínicos
    </a>

    <p><strong>Antecedentes:</strong> <?= nl2br(h($extra['antecedentes'] ?? 'No registrado')) ?></p>
    <p><strong>Alergias:</strong> <?= nl2br(h($extra['alergias'] ?? 'No registrado')) ?></p>
    <p><strong>Medicación:</strong> <?= nl2br(h($extra['medicacion'] ?? 'No registrado')) ?></p>
    <p><strong>Patologías:</strong> <?= nl2br(h($extra['patologias'] ?? 'No registrado')) ?></p>
    <p><strong>Obra social:</strong> <?= h($extra['obra_social'] ?? 'No registrado') ?></p>
    <p><strong>Nro afiliado:</strong> <?= h($extra['nro_afiliado'] ?? 'No registrado') ?></p>
</div>

<!-- PREGUNTAS PERSONALIZADAS DEL CENTRO -->
<?php if (!empty($preguntas_centro)): ?>
<div class="card">
    <h3>Datos personalizados del centro</h3>

    <form action="paciente-preguntas-guardar-centro.php" method="post">
        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <?php foreach ($preguntas_centro as $q): ?>
            <?php $value = $answers_centro[$q['id']] ?? ''; ?>

            <label><strong><?= h($q['question_text']) ?></strong></label>

            <?php if ($q['type'] === 'textarea'): ?>
                <textarea name="q_<?= $q['id'] ?>"><?= h($value) ?></textarea>
            <?php elseif ($q['type'] === 'number'): ?>
                <input type="number" name="q_<?= $q['id'] ?>" value="<?= h($value) ?>">
            <?php else: ?>
                <input type="text" name="q_<?= $q['id'] ?>" value="<?= h($value) ?>">
            <?php endif; ?>

            <br><br>
        <?php endforeach; ?>

        <button class="btn">Guardar respuestas</button>
    </form>
</div>
<?php endif; ?>

<!-- AGREGAR PREGUNTA PERSONALIZADA -->
<div class="card">
    <h3>Agregar pregunta personalizada</h3>

    <form action="pregunta-crear-centro.php" method="post">
        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <label>Texto de la pregunta</label>
        <input type="text" name="question_text" required>

        <label>Tipo</label>
        <select name="type">
            <option value="text">Texto</option>
            <option value="number">Número</option>
            <option value="textarea">Texto largo</option>
        </select>

        <label>
            <input type="checkbox" name="required" value="1"> Obligatoria
        </label>

        <button class="btn">Crear pregunta</button>
    </form>
</div>

<!-- EVOLUCIONES -->
<div class="card">
    <h3>Evoluciones</h3>

    <?php if (empty($evoluciones)): ?>
        <p>No hay evoluciones registradas.</p>
    <?php endif; ?>

    <?php foreach ($evoluciones as $e): ?>
        <div style="margin-bottom:15px;">
            <strong><?= h($e['profesional']) ?></strong> — <?= $e['created_at'] ?><br>
            <?= nl2br(h($e['texto'])) ?>
        </div>
    <?php endforeach; ?>
</div>

</div>
</body>
</html>
