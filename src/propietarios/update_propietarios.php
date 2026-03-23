<?php
/*
CRUD con PostgreSQL y PHP
@Carlos Eduardo Perez Rueda
@Marzo de 2023
=================================================================
Este archivo guarda los datos del formulario en donde se editan
=================================================================
*/
?>

<?php

# Salir si alguno de los datos no está presente
if (
    !isset($_POST["id_propietario"]) ||
    !isset($_POST["nom_propietario"]) ||
    !isset($_POST["ape_propietario"]) ||
    !isset($_POST["tel_propietario"]) ||
    !isset($_POST["email_propietario"]) ||
    !isset($_POST["id_bus"]) 
) {
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

# Validación mínima de datos

$id_propietario  = $_POST["id_propietario"];
$nom_propietario = $_POST["nom_propietario"];
$ape_propietario = $_POST["ape_propietario"];
$tel_propietario = $_POST["tel_propietario"];
$email_propietario = $_POST["email_propietario"];
$id_bus = $_POST["id_bus"];

if (!preg_match('/^\d{10}$/', $id_propietario)) {
    echo "El ID del propietario debe contener exactamente 10 dígitos.";
    exit();
}
if (strlen($nom_propietario) < 3 || strlen($nom_propietario) > 50) {
    echo "El nombre debe tener entre 3 y 50 caracteres.";
    exit();
}
if (strlen($ape_propietario) < 3 || strlen($ape_propietario) > 50) {
    echo "El apellido debe tener entre 3 y 50 caracteres.";
    exit();
}
if (!preg_match('/^\d{10}$/', $tel_propietario)) {
    echo "El teléfono debe contener exactamente 10 dígitos.";
    exit();
}
if (!filter_var($email_propietario, FILTER_VALIDATE_EMAIL)) {
    echo "El email no es válido.";
    exit();
}

include_once "../base_de_datos.php";
$id_propietario  = $_POST["id_propietario"];
$nom_propietario = $_POST["nom_propietario"];
$ape_propietario = $_POST["ape_propietario"];
$tel_propietario = $_POST["tel_propietario"];
$email_propietario = $_POST["email_propietario"];


$sentencia = $base_de_datos->prepare("SELECT fun_update_propietarios(?,?,?,?,?,?);");
$resultado = $sentencia->execute([
    $id_propietario,
    $id_bus,
    $nom_propietario,
    $ape_propietario,
    $tel_propietario,
    $email_propietario
]);

if ($resultado === true) {
    header("Location: listar_propietarios.php");
    exit();
} else {
    echo "Algo salió mal. Por favor verifica que la tabla exista, así como el ID del propietario.";
}

