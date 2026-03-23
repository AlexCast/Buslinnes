<?php
/*
CRUD con PostgreSQL y PHP
@Carlos Eduardo Perez Rueda
@Marzo de 2023
==================================================================
Este archivo inserta los datos enviados a través de formulario.php
==================================================================
*/
?>
<?php

if (!isset($_POST["id_propietario"]) ||
    !isset($_POST["nom_propietario"]) ||
    !isset($_POST["ape_propietario"]) ||
    !isset($_POST["tel_propietario"]) ||
    !isset($_POST["email_propietario"]) ||
    !isset($_POST["id_bus"])) {
    exit();
}





$id_propietario    = $_POST["id_propietario"];
$nom_propietario   = $_POST["nom_propietario"];
$ape_propietario   = $_POST["ape_propietario"];
$tel_propietario   = $_POST["tel_propietario"];
$email_propietario = $_POST["email_propietario"];
$id_bus            = $_POST["id_bus"];

// Validar que id_bus sea numérico
if (!is_numeric($id_bus)) {
    echo "Error: El valor de id_bus recibido es inválido: ".$id_bus;
    exit();
}
// Convertir a entero por seguridad
$id_bus = (int)$id_bus;

include_once "../base_de_datos.php";



// El orden correcto es: id_propietario, id_bus, nom_propietario, ape_propietario, tel_propietario, email_propietario
$sentencia = $base_de_datos->prepare("SELECT fun_insert_propietarios(?, ?, ?, ?, ?, ?);");
$resultado = $sentencia->execute([
    $id_propietario,
    $id_bus,
    $nom_propietario,
    $ape_propietario,
    $tel_propietario,
    $email_propietario
]);

if ($resultado === true) {
    echo "Registro Insertado";
    header("Location: listar_propietarios.php");
    exit();
} else {
    echo "Registro NO Insertado";
    echo "Algo salió mal. Por favor verifica que la tabla exista";
}

