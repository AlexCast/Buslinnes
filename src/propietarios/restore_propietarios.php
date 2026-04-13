<?php
/*
Archivo para procesar la restauración de propietarios
@BNPRO
*/
include_once "../base_de_datos.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_propietario'])) {
    $id_propietario = trim((string) $_POST['id_propietario']);
    if (!preg_match('/^[0-9]{10}$/', $id_propietario)) {
        header("Location: listar_propietarios.php?error_restore=1");
        exit();
    }
    
    // Llamar a la función de restauración en PostgreSQL
    $sentencia = $base_de_datos->prepare("SELECT fun_restore_propietarios(?);");
    $sentencia->execute([$id_propietario]);
    $resultado = $sentencia->fetchColumn();
    $ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';
    
    if ($ok) {
        header("Location: listar_propietarios.php?restaurado=1");
    } else {
        header("Location: listar_propietarios.php?error_restore=1");
    }
    exit();
}

header("Location: listar_propietarios.php");


