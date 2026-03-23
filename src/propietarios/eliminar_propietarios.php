<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
Elimina un propietario por su ID (soft delete si tienes campo, o hard delete si no)
*/

if (!isset($_POST["id_propietario"])) {
    echo "No se especificó el propietario a eliminar";
    exit();
}

$id_propietario = $_POST["id_propietario"];
include_once "../base_de_datos.php";

// Si tienes soft delete, cambia la función aquí:
$sentencia = $base_de_datos->prepare("SELECT fun_softdelete_propietarios(?);");
$resultado = $sentencia->execute([$id_propietario]);

if ($resultado === true) {
    header("Location: listar_propietarios.php");
    exit();
} else {
    echo "Algo salió mal. Verifica que el propietario exista.";
    $error = $sentencia->errorInfo();
    echo "Error en la consulta: " . $error[2];
}


