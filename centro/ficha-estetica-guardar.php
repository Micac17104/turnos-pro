<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/db.php';

require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id'];
$patient_id = $_POST['patient_id'] ?? null;

if (!$patient_id) {
    die("Paciente inválido.");
}

$data = [
    'tipo_piel' => $_POST['tipo_piel'] ?? '',
    'zonas_tratar' => $_POST['zonas_tratar'] ?? '',
    'tratamientos_previos' => $_POST['tratamientos_previos'] ?? '',
    'contraindicaciones' => $_POST['contraindicaciones'] ?? '',
    'objetivo' => $_POST['objetivo'] ?? '',
    'productos_usados' => $_POST['productos_usados'] ?? '',
    'rutina_recomendada' => $_POST['rutina_recomendada'] ?? ''
];

// Ver si ya existe ficha
$stmt = $pdo->prepare("
    SELECT id FROM ficha_estetica
    WHERE client_id = ? AND center_id = ?
");
$stmt->execute([$patient_id, $center_id]);
$existe = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existe) {
    // Update
    $stmt = $pdo->prepare("
        UPDATE ficha_estetica
        SET tipo_piel=?, zonas_tratar=?, tratamientos_previos=?, contraindicaciones=?, objetivo=?, productos_usados=?, rutina_recomendada=?
        WHERE client_id=? AND center_id=?
    ");
    $stmt->execute([
        $data['tipo_piel'], $data['zonas_tratar'], $data['tratamientos_previos'],
        $data['contraindicaciones'], $data['objetivo'], $data['productos_usados'],
        $data['rutina_recomendada'], $patient_id, $center_id
    ]);
} else {
    // Insert
    $stmt = $pdo->prepare("
        INSERT INTO ficha_estetica
        (client_id, center_id, tipo_piel, zonas_tratar, tratamientos_previos, contraindicaciones, objetivo, productos_usados, rutina_recomendada)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $patient_id, $center_id,
        $data['tipo_piel'], $data['zonas_tratar'], $data['tratamientos_previos'],
        $data['contraindicaciones'], $data['objetivo'], $data['productos_usados'],
        $data['rutina_recomendada']
    ]);
}

header("Location: paciente-historia.php?id=" . $patient_id . "&ok=1");

exit;
