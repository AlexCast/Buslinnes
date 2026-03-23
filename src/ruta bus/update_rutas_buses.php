<?php
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
=================================================================
Este archivo guarda los datos del formulario en donde se editan
las asignaciones ruta-bus usando fun_update_ruta_bus.
=================================================================
*/
?>

<?php
if (
    !isset($_POST["id_ruta_bus"]) ||
    !isset($_POST["id_ruta"]) ||
    !isset($_POST["id_bus"])
) {
    echo "Salió mal";
    exit();
}

$validarEntero = static function ($valor, string $campo, int $min = 1, int $max = 2147483647): int {
    $valor = trim((string) $valor);
    if (!preg_match('/^[0-9]+$/', $valor)) {
        exit("El campo {$campo} debe ser numerico entero.");
    }
    $numero = (int) $valor;
    if ($numero < $min || $numero > $max) {
        exit("El campo {$campo} debe estar entre {$min} y {$max}.");
    }
    return $numero;
};

include_once "../base_de_datos.php";
$id_ruta_bus = $validarEntero($_POST["id_ruta_bus"], "id_ruta_bus");
$id_ruta = $validarEntero($_POST["id_ruta"], "id_ruta");
$id_bus = $validarEntero($_POST["id_bus"], "id_bus");

$sentencia = $base_de_datos->prepare("SELECT fun_update_ruta_bus(?,?,?);");
$sentencia->execute([$id_ruta_bus, $id_ruta, $id_bus]);
$resultado = $sentencia->fetchColumn();

if ($resultado === true) {
    header("Location: listar_rutas_buses.php");
    exit();
} else {
    echo "Algo salió mal. Por favor verifica que la tabla exista, así como el ID de la ruta-bus.";
}
?>

