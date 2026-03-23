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
# Validar que todos los campos requeridos est�n presentes
if (!isset($_POST["id_mantenimiento"]) ||
    !isset($_POST["id_bus"]) ||
    !isset($_POST["descripcion"]) ||
    !isset($_POST["fecha_mantenimiento"]) ||
    !isset($_POST["costo"])) {
    exit();
}

$redirigirConError = static function (string $mensaje): void {
    header("Location: forma_mantenimiento.php?error=" . urlencode($mensaje));
    exit();
};

$validarEntero = static function ($valor, string $campo, int $min, int $max) use ($redirigirConError): int {
    $valor = trim((string) $valor);
    if (!preg_match('/^[0-9]+$/', $valor)) {
        $redirigirConError("El campo {$campo} debe ser numerico entero.");
    }
    $numero = (int) $valor;
    if ($numero < $min || $numero > $max) {
        $redirigirConError("El campo {$campo} debe estar entre {$min} y {$max}.");
    }
    return $numero;
};

$validarTexto = static function ($valor, string $campo, int $min, int $max) use ($redirigirConError): string {
    $texto = trim((string) $valor);
    $longitud = mb_strlen($texto);
    if ($longitud < $min || $longitud > $max) {
        $redirigirConError("El campo {$campo} debe tener entre {$min} y {$max} caracteres.");
    }
    return $texto;
};

$normalizarFecha = static function ($valor, string $campo) use ($redirigirConError): string {
    $texto = trim((string) $valor);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?$/', $texto)) {
        $redirigirConError("El campo {$campo} no tiene un formato valido.");
    }
    $texto = str_replace('T', ' ', $texto);
    if (strlen($texto) === 16) {
        $texto .= ':00';
    }
    return $texto;
};
#Si todo va bien, se ejecuta esta parte del c�digo..., si no, nos jodimos

include_once "../base_de_datos.php";
$id_mantenimiento      = $validarEntero($_POST["id_mantenimiento"], "id_mantenimiento", 1, 2147483647);
$id_bus                = $validarEntero($_POST["id_bus"], "id_bus", 1, 2147483647);
$descripcion           = $validarTexto($_POST["descripcion"], "descripcion", 10, 500);
$fecha_mantenimiento   = $normalizarFecha($_POST["fecha_mantenimiento"], "fecha_mantenimiento");
$costo                 = $validarEntero($_POST["costo"], "costo", 0, 9999999999);
/*
Al incluir el archivo "base_de_datos.php", todas sus variables est�n
a nuestra disposici�n. Por lo que podemos acceder a ellas tal como si hubi�ramos
copiado y pegado el c�digo
 */

# Ajustar la consulta para incluir id_bus
$sentencia = $base_de_datos->prepare("SELECT fun_insert_mantenimiento(?, ?, ?, ?, ?);");
$resultado = $sentencia->execute([$id_mantenimiento, $id_bus, $descripcion, $fecha_mantenimiento, $costo]); # Pasar en el mismo orden de los ?
#execute regresa un booleano. True en caso de que todo vaya bien, falso en caso contrario.
#Con eso podemos evaluar*/
echo $resultado;
if ($resultado === true) {
    # Redireccionar a la lista
	header("Location: listar_mantenimiento.php");
} else
    {
    echo "Algo sali� mal. Por favor verifica que la tabla exista";
    }



