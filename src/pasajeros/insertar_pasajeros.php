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
@Carlos Eduardo Perez Rueda
@Marzo de 2023

Adaptado por
@yerson
@2025
==================================================================
Este archivo inserta los datos enviados a trav�s de formulario.php
==================================================================
*/
?>
<?php
if (!isset($_POST["id_pasajero"])      ||
    !isset($_POST["nom_pasajero"]) ||
    !isset($_POST["email_pasajero"]))
    {
    exit();
    }
#Si todo va bien, se ejecuta esta parte del c�digo..., si no, nos jodimos

include_once "../base_de_datos.php";
$id_pasajero       = $_POST["id_pasajero"];
$nom_pasajero      = $_POST["nom_pasajero"];
$email_pasajero    = $_POST["email_pasajero"];
/*
Al incluir el archivo "base_de_datos.php", todas sus variables est�n
a nuestra disposici�n. Por lo que podemos acceder a ellas tal como si hubi�ramos
copiado y pegado el c�digo
 */

$sentencia = $base_de_datos->prepare("SELECT fun_insert_pasajeros(?, ?, ?)");
$resultado = $sentencia->execute([$id_pasajero, $nom_pasajero, $email_pasajero]); # Pasar en el mismo orden de los ?
#execute regresa un booleano. True en caso de que todo vaya bien, falso en caso contrario.
#Con eso podemos evaluar*/
echo $resultado;
if ($resultado === true) {
    # Redireccionar a la lista
    echo "Registro Insertado";
	header("Location: listar_pasajeros.php");
} else
    {
    echo "Registro NO Insertado";
    echo "Algo sali� mal. Por favor verifica que la tabla exista";
    }



