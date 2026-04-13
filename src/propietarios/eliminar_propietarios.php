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

$id_propietario = trim((string) $_POST["id_propietario"]);
if (!preg_match('/^[0-9]{10}$/', $id_propietario)) {
    echo "ID de propietario invalido";
    exit();
}
include_once "../base_de_datos.php";

// Si tienes soft delete, cambia la función aquí:
$sentencia = $base_de_datos->prepare("SELECT fun_softdelete_propietarios(?);");
$sentencia->execute([$id_propietario]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header("Location: listar_propietarios.php");
    exit();
} else {
    echo "Algo salió mal. Verifica que el propietario exista.";
    $error = $sentencia->errorInfo();
    echo "Error en la consulta: " . $error[2];
}


