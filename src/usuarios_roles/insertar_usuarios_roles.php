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
autor: alexndrcastt
==================================================================
Este archivo inserta los datos enviados a trav�s de forma_usuarios_roles.php
==================================================================
*/
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']);

if (!isset($_POST["id_usuario"]) || !isset($_POST["id_rol"])) {
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

$id_usuario_txt = trim((string) $_POST["id_usuario"]);
$id_rol_txt = trim((string) $_POST["id_rol"]);
if (!preg_match('/^[0-9]+$/', $id_usuario_txt) || (int) $id_usuario_txt <= 0) {
    echo "El id_usuario es invalido";
    exit();
}
if (!preg_match('/^[0-9]+$/', $id_rol_txt) || (int) $id_rol_txt <= 0) {
    echo "El id_rol es invalido";
    exit();
}

include_once "../base_de_datos.php";

$id_usuario = (int) $id_usuario_txt;
$id_rol = (int) $id_rol_txt;
$usr_insert = '1'; // Usuario por defecto o tomar de sesi�n

try {
    $sentencia = $base_de_datos->prepare("SELECT fun_insert_tab_usuarios_roles(?, ?, ?);");
    $resultado = $sentencia->execute([$id_usuario, $id_rol, $usr_insert]);

    if ($resultado === true) {
        header("Location: listar_usuarios_roles.php");
    } else {
        echo "Algo sali� mal. Por favor verifica que la tabla exista";
    }
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
?>



