<?php

$host='localhost';
$bd='db_buslinnes';
$user='postgres';
$pass='0149';

$conexion=pg_connect("host=$host dbname=$bd user=$user password=$pass");

$query=("INSERT INTO clientes(nombre,correo,telefono,direccion)
	VALUES('$_REQUEST[nombre]','$_REQUEST[correo]',
	'$_REQUEST[tel]','$_REQUEST[direccion]')");

$consulta=pg_query($conexion,$query);
pg_close();
echo 'El cliente fue dado de alta';


?>

