<?php
/*
==============================================================================
Este archivo se encarga de conectar a la base de datos y traer un objeto PDO
==============================================================================
 */
$contraseña = "0149";
$usuario = "postgres";
$nombre_base_datos = "db_buslinnes";
$server = "localhost";
$puerto = "5432";
try
{
    $base_de_datos = new PDO("pgsql:host=$server;port=$puerto;dbname=$nombre_base_datos", $usuario, $contraseña);
    $base_de_datos->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurar UTF-8 explícitamente para la conexión
    $base_de_datos->query("SET CLIENT_ENCODING TO 'UTF8'");
    $base_de_datos->query("SET NAMES 'UTF8'");

    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }

    $usuario_auditoria = 'anonimo';
    if (!empty($_SESSION['correo'])) {
        $usuario_auditoria = (string) $_SESSION['correo'];
    } elseif (!empty($_SESSION['nombre'])) {
        $usuario_auditoria = (string) $_SESSION['nombre'];
    } elseif (!empty($_SESSION['id_usuario'])) {
        $usuario_auditoria = 'id:' . (string) ((int) $_SESSION['id_usuario']);
    }

    try {
        $stmt_auditoria = $base_de_datos->prepare("SELECT set_config('app.current_user', :usuario_auditoria, false)");
        $stmt_auditoria->execute([':usuario_auditoria' => $usuario_auditoria]);
    } catch (Exception $e) {
        error_log("Auditoria config warning: " . $e->getMessage());
    }

}

catch (Exception $e)
{
    echo "Error al conectar a la base de datos: " . $e->getMessage();
}



