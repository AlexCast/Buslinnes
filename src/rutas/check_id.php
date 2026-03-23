<?php
/*
AJAX endpoint para verificar si un ID de ruta ya existe
*/
header('Content-Type: application/json');

if (!isset($_GET['id_ruta'])) {
    echo json_encode(['exists' => false, 'error' => 'ID no proporcionado']);
    exit();
}

include_once "../base_de_datos.php";

$id_ruta = $_GET['id_ruta'];

// Verificar si el ID existe
$stmt = $base_de_datos->prepare("SELECT id_ruta FROM tab_rutas WHERE id_ruta = ? LIMIT 1");
$stmt->execute([$id_ruta]);
$exists = $stmt->fetchColumn() ? true : false;

echo json_encode(['exists' => $exists]);
exit();


