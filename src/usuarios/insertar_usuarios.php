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
Este archivo inserta los datos enviados a trav�s de forma_usuarios.php
==================================================================
*/
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']);

if (!isset($_POST["nombre"]) || !isset($_POST["correo"]) || !isset($_POST["contrasena"])) {
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

$nombre = trim((string) $_POST["nombre"]);
if (function_exists('mb_strlen')) {
    $longitudNombre = mb_strlen($nombre);
} elseif (function_exists('iconv_strlen')) {
    $longitudNombre = iconv_strlen($nombre, 'UTF-8');
    if ($longitudNombre === false) {
        $longitudNombre = strlen($nombre);
    }
} else {
    $longitudNombre = strlen($nombre);
}
if ($longitudNombre < 3 || $longitudNombre > 120) {
    echo "El nombre debe tener entre 3 y 120 caracteres";
    exit();
}

$correo = trim((string) $_POST["correo"]);
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo "El correo no es valido";
    exit();
}

$contrasena = (string) $_POST["contrasena"];
$longitudContrasena = strlen($contrasena);
if ($longitudContrasena < 8 || $longitudContrasena > 72) {
    echo "La contrasena debe tener entre 8 y 72 caracteres";
    exit();
}

// Incluir archivos necesarios
include_once "../base_de_datos.php";
include_once "../../config/database.php";
include_once "../../app/userClass.php";

try {
    $tipo_doc = trim((string) ($_POST["tipo_doc"] ?? 'CD'));
    if (!in_array($tipo_doc, ['CD', 'TI', 'CE'], true)) {
        echo "El tipo de documento no es valido";
        exit();
    }

    $id_usuario = null;
    if (isset($_POST["id_usuario"]) && $_POST["id_usuario"] !== '') {
        $id_usuario_txt = trim((string) $_POST["id_usuario"]);
        if (!preg_match('/^[0-9]+$/', $id_usuario_txt) || (int) $id_usuario_txt <= 0) {
            echo "El id_usuario es invalido";
            exit();
        }
        $id_usuario = (int) $id_usuario_txt;
    } else {
        $seq = $base_de_datos->query("SELECT COALESCE(MAX(id_usuario), 0) + 1 AS next_id FROM tab_usuarios");
        $filaSeq = $seq->fetch(PDO::FETCH_OBJ);
        $id_usuario = (int) ($filaSeq->next_id ?? 0);
        if ($id_usuario <= 0) {
            echo "No fue posible generar id_usuario";
            exit();
        }
    }

    // Usar el mismo m�todo que el registro p�blico para mantener consistencia
    // Este m�todo hashea la contrase�a correctamente con password_hash()
    $userClass = new userClass();
    $resultado = $userClass->fun_insert_usuario($tipo_doc, $id_usuario, $nombre, $correo, $contrasena);

    if ($resultado === true) {
        header("Location: listar_usuarios.php");
        exit();
    } else {
        echo "Error: El usuario ya existe o hubo un problema al crear el usuario.";
    }
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>



