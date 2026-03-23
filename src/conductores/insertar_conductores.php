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
@alexndrcastt
@2025
==================================================================
Este archivo inserta los datos enviados a trav�s de formulario.php
==================================================================
*/
?>
<?php
if (!isset($_POST["id_conductor"])      ||
    !isset($_POST["nom_conductor"]) ||
    !isset($_POST["ape_conductor"]) ||
    (!isset($_POST["email_conductor"]) && !isset($_POST["tel_conductor"])) ||
    !isset($_POST["licencia_conductor"]) ||
    !isset($_POST["tipo_licencia"]) ||
    !isset($_POST["fec_venc_licencia"]) ||
    !isset($_POST["estado_conductor"]) ||
    !isset($_POST["edad"]) ||
    !isset($_POST["tipo_sangre"])) {
    exit();
}

include_once "../base_de_datos.php";
$id_conductor       = $_POST["id_conductor"];
$nom_conductor      = $_POST["nom_conductor"];
$ape_conductor      = $_POST["ape_conductor"];
$email_conductor    = $_POST["email_conductor"] ?? $_POST["tel_conductor"];
$licencia_conductor = $_POST["licencia_conductor"];
$tipo_licencia      = $_POST["tipo_licencia"];
$fec_venc_licencia  = $_POST["fec_venc_licencia"];
$estado_conductor   = $_POST["estado_conductor"];
$edad               = $_POST["edad"];
$tipo_sangre        = $_POST["tipo_sangre"];

try {
    $sentencia = $base_de_datos->prepare("SELECT fun_insert_conductores(?::int, ?::varchar, ?::varchar, ?::varchar, ?::varchar, ?::char(2), ?::date, ?::char(1), ?::numeric, ?::varchar) AS resultado;");
    $sentencia->execute([
        $id_conductor,
        $nom_conductor,
        $ape_conductor,
        $email_conductor,
        $licencia_conductor,
        $tipo_licencia,
        $fec_venc_licencia,
        $estado_conductor,
        $edad,
        $tipo_sangre
    ]);

    $respuesta = $sentencia->fetch(PDO::FETCH_ASSOC);
    $mensaje = $respuesta['resultado'] ?? '';

    if (stripos($mensaje, 'insertado correctamente') !== false) {
        header("Location: listar_conductores.php");
        exit();
    }

    echo "Registro NO Insertado - " . htmlspecialchars($mensaje);
} catch (PDOException $e) {
    echo "Error al insertar conductor: " . htmlspecialchars($e->getMessage());
}



