<?php
header('Content-Type: text/html; charset=utf-8');

// === SEGURIDAD: Proteccion anti-scraping y CSRF ===
require_once __DIR__ . '/../../app/SecurityMiddleware.php';

SecurityMiddleware::protect([
    'csrf' => true,  // POST/PUT/DELETE requiere CSRF
    'rateLimit' => true,
    'origin' => true,
    'userAgent' => true,
    'securityHeaders' => true
]);
// === FIN SEGURIDAD ===

/*
CRUD con PostgreSQL y PHP
Elimina (soft delete) una ruta por su ID
Autor: oto
*/

if (!isset($_POST["id_ruta"])) {
    echo "No se especific� la ruta a eliminar";
    exit();
}
$id_ruta = $_POST["id_ruta"];

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT fun_softdelete_rutas(?);");
$resultado = $sentencia->execute([$id_ruta]);
$respuesta = $sentencia->fetch(PDO::FETCH_ASSOC);
if ($resultado === true && $respuesta && in_array('t', $respuesta) || in_array(true, $respuesta)) {
    header("Location: listar_rutas.php");
    exit();
} else {
    echo "No se pudo eliminar la ruta. Respuesta de la funci�n:";
    var_dump($respuesta);
    $error = $sentencia->errorInfo();
    echo "<br>Error en la consulta: " . $error[2];
}
?>



