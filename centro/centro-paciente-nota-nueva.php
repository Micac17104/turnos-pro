<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id'];
$client_id = $_GET['id'] ?? null;

if (!$client_id) {
    header("Location: centro-pacientes.php");
    exit;
}

// Verificar que el paciente pertenece al centro
$stmt = $pdo->prepare("SELECT id FROM clients WHERE id = ? AND center_id = ?");
$stmt->execute([$client_id, $center_id]);
$valid = $stmt->fetch();

if (!$valid) {
    die("Paciente no encontrado o no pertenece a este centro.");
}

$errors = [];
$success = "";

if ($_POST) {

    $note = trim($_POST['note'] ?? '');

    if ($note === '') {
        $errors[] = "La nota no puede estar vacía.";
    }

    // Validar usuario autor
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        $errors[] = "Error interno: usuario no identificado.";
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO patient_notes (client_id, center_id, user_id, note)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $client_id,
            $center_id,
            $user_id,
            $note
        ]);

        header("Location: centro-paciente-notas.php?id=$client_id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nueva nota interna</title>
<style>
body{background:#f1f5f9;font-family:Arial;display:flex;justify-content:center;align-items:center;padding:40px;}
.box{background:white;padding:40px;border-radius:20px;width:420px;box-shadow:0 10px 30px rgba(15,23,42,0.06);}
textarea{width:100%;padding:12px;margin:8px 0;border-radius:10px;border:1px solid #cbd5e1;height:150px;}
button{width:100%;padding:14px;background:#0ea5e9;color:white;border:none;border-radius:12px;font-weight:600;cursor:pointer;}
button:hover{opacity:0.9;}
.error{color:#b00020;margin-bottom:10px;}
a{color:#0ea5e9;text-decoration:none;font-size:14px;}
</style>
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div style="margin-left:260px; padding:24px;">

<div class="box">
    <h2>Nueva nota interna</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <textarea name="note" placeholder="Escribí la nota interna..." required></textarea>
        <button>Guardar nota</button>
    </form>

    <p style="margin-top:10px;"><a href="centro-paciente-notas.php?id=<?= $client_id ?>">Volver</a></p>
</div>

</div>
</body>
</html>