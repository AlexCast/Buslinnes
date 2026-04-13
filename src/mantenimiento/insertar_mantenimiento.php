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
    (!isset($_POST["costo_mantenimiento"]) && !isset($_POST["costo"]))) {
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
    $longitud = function_exists('mb_strlen')
        ? mb_strlen($texto)
        : (function_exists('iconv_strlen') ? iconv_strlen($texto, 'UTF-8') : strlen($texto));
    if ($longitud < $min || $longitud > $max) {
        $redirigirConError("El campo {$campo} debe tener entre {$min} y {$max} caracteres.");
    }
    return $texto;
};

$normalizarPlacaBus = static function ($valor, string $campo) use ($redirigirConError): string {
    $texto = strtoupper(trim((string) $valor));
    $compacto = preg_replace('/\s+/', '', $texto);
    if (!preg_match('/^[A-Z]{3}[0-9]{3}$/', $compacto)) {
        $redirigirConError("El campo {$campo} debe tener formato AAA123.");
    }
    return $compacto;
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
$id_bus                = $normalizarPlacaBus($_POST["id_bus"], "id_bus");
$descripcion           = $validarTexto($_POST["descripcion"], "descripcion", 10, 500);
$fecha_mantenimiento   = $normalizarFecha($_POST["fecha_mantenimiento"], "fecha_mantenimiento");
$valorCosto            = $_POST["costo_mantenimiento"] ?? $_POST["costo"];
$costo                 = $validarEntero($valorCosto, "costo_mantenimiento", 0, 9999999999);
/*
Al incluir el archivo "base_de_datos.php", todas sus variables est�n
a nuestra disposici�n. Por lo que podemos acceder a ellas tal como si hubi�ramos
copiado y pegado el c�digo
 */

# Ajustar la consulta para incluir id_bus
try {
    $sentencia = $base_de_datos->prepare("SELECT fun_insert_mantenimiento(?, ?, ?, ?, ?);");
    $sentencia->execute([$id_mantenimiento, $id_bus, $descripcion, $fecha_mantenimiento, $costo]); # Pasar en el mismo orden de los ?
    $resultado = $sentencia->fetchColumn();
    $ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';
} catch (PDOException $e) {
    $redirigirConError("Error de base de datos al registrar mantenimiento.");
}
#execute regresa un booleano. True en caso de que todo vaya bien, falso en caso contrario.
#Con eso podemos evaluar*/
if ($ok) {
    # Redireccionar a la lista
	header("Location: listar_mantenimiento.php");
    exit();
} else
    {
    $redirigirConError("No se pudo registrar el mantenimiento. Verifique los datos ingresados.");
    }



