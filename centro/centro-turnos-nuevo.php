<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';

$errors = [];
$success = "";

// Obtener profesionales del centro
$stmt = $pdo->prepare("
    SELECT id, name 
    FROM users
    WHERE account_type='professional'
    AND parent_center_id=?
    ORDER BY name
");
$stmt->execute([$center_id]);
$profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener pacientes (clientes)
$stmt = $pdo->prepare("
    SELECT id, name 
    FROM clients
    ORDER BY name
");
$stmt->execute();
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $prof_id = $_POST['professional_id'] ?? '';
    $client_id = $_POST['client_id'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $status = $_POST['status'] ?? 'pending';

    if ($prof_id === '' || $client_id === '' || $date === '' || $time === '') {
        $errors[] = "Todos los campos son obligatorios.";
    }

    // Validar que el profesional pertenece al centro
    $stmt = $pdo->prepare("
        SELECT id FROM users 
        WHERE id=? AND parent_center_id=? AND account_type='professional'
    ");
    $stmt->execute([$prof_id, $center_id]);
    if (!$stmt->fetch()) {
        $errors[] = "Profesional inválido.";
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO appointments (user_id, client_id, date, time, status)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([$prof_id, $client_id, $date, $time, $status]);

        $success = "Turno creado correctamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo turno</title>
<style>
body{background:#f1f5f9;font-family:Arial;display:flex;justify-content:center;align-items:center;padding:40px;}
.box{background:white;padding:40px;border-radius:20px;width:420px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
input, select{width:100%;padding:12px;margin:8px 0;border-radius:10px;border:1px solid #cbd5e1;}
button{width:100%;padding:14px;background:#0ea5e9;color:white;border:none;border-radius:12px;font-weight:600;cursor:pointer;}
button:hover{opacity:0.9;}
.error{color:#b00020;margin-bottom:10px;}
.success{color:#22c55e;margin-bottom:10px;}
a{color:#0ea5e9;text-decoration:none;font-size:14px;}
</style>
</head>
<body>

<div class="box">
    <h2>Crear nuevo turno</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post">

        <label>Profesional</label>
        <select name="professional_id" required>
            <option value="">Seleccionar profesional</option>
            <?php foreach ($profesionales as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Paciente</label>
        <select name="client_id" required>
            <option value="">Seleccionar paciente</option>
            <?php foreach ($pacientes as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Fecha</label>
        <input type="date" name="date" required>

        <label>Hora</label>
        <input type="time" name="time" required>

        <label>Estado</label>
        <select name="status">
            <option value="pending">Pendiente</option>
            <option value="confirmed">Confirmado</option>
            <option value="cancelled">Cancelado</option>
        </select>

        <button>Crear turno</button>
    </form>

    <p style="margin-top:10px;"><a href="centro-turnos.php">Volver</a></p>
</div>

</body>
</html>