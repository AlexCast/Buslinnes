<?php
/*
Archivo para procesar la restauración de propietarios
@BNPRO
*/
include_once "../base_de_datos.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_propietario'])) {
    $id_propietario = $_POST['id_propietario'];
    
    // Llamar a la función de restauración en PostgreSQL
    $sentencia = $base_de_datos->prepare("SELECT fun_restore_propietarios(?);");
    $resultado = $sentencia->execute([$id_propietario]);
    
    if ($resultado) {
        header("Location: listar_propietarios.php?restaurado=1");
    } else {
        header("Location: listar_propietarios.php?error_restore=1");
    }
    exit();
}

header("Location: listar_propietarios.php");


