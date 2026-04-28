<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/auth-centro.php';
require __DIR__ . '/../pro/includes/db.php';

$patient_id = $_POST['patient_id'];

$stmt = $pdo->prepare("
    INSERT INTO clinical_extra (client_id, antecedentes, alergias, medicacion, patologias, obra_social, nro_afiliado)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        antecedentes = VALUES(antecedentes),
        alergias = VALUES(alergias),
        medicacion = VALUES(medicacion),
        patologias = VALUES(patologias),
        obra_social = VALUES(obra_social),
        nro_afiliado = VALUES(nro_afiliado)
");

$stmt->execute([
    $patient_id,
    $_POST['antecedentes'],
    $_POST['alergias'],
    $_POST['medicacion'],
    $_POST['patologias'],
    $_POST['obra_social'],
    $_POST['nro_afiliado']
]);

header("Location: paciente-historia.php?id=" . $patient_id . "&ok=1");
exit;
