<?php
header('Content-Type: text/html; charset=utf-8');

// === SEGURIDAD: Proteccion anti-scraping y CSRF ===
require_once __DIR__ . '/../../app/SecurityMiddleware.php';

SecurityMiddleware::protect([
    'csrf' => true,
    'rateLimit' => true,
    'origin' => true,
    'userAgent' => true,
    'securityHeaders' => true
]);
// === FIN SEGURIDAD ===

if (!isset($_POST['id_propietario']) ||
    !isset($_POST['nom_propietario']) ||
    !isset($_POST['ape_propietario']) ||
    !isset($_POST['tel_propietario']) ||
    !isset($_POST['email_propietario']) ||
    !isset($_POST['id_bus'])) {
    exit();
}

$redirigirConError = static function (string $mensaje): void {
    header('Location: forma_propietarios.php?error=' . urlencode($mensaje));
    exit();
};

$longitudTexto = static function (string $texto): int {
    if (function_exists('mb_strlen')) {
        return mb_strlen($texto);
    }
    if (function_exists('iconv_strlen')) {
        $len = iconv_strlen($texto, 'UTF-8');
        if ($len !== false) {
            return $len;
        }
    }
    return strlen($texto);
};

$validarDocumento = static function ($valor, string $campo) use ($redirigirConError): string {
    $texto = trim((string) $valor);
    if (!preg_match('/^[0-9]{6,10}$/', $texto)) {
        $redirigirConError("El campo {$campo} debe tener entre 6 y 10 digitos.");
    }
    return $texto;
};

$validarNombre = static function ($valor, string $campo) use ($redirigirConError, $longitudTexto): string {
    $texto = trim((string) $valor);
    $len = $longitudTexto($texto);
    if ($len < 3 || $len > 50) {
        $redirigirConError("El campo {$campo} debe tener entre 3 y 50 caracteres.");
    }
    return $texto;
};

$validarTelefono = static function ($valor, string $campo) use ($redirigirConError): string {
    $texto = trim((string) $valor);
    if (!preg_match('/^[0-9]{10}$/', $texto)) {
        $redirigirConError("El campo {$campo} debe tener 10 digitos.");
    }
    if ((int) $texto < 2999999999) {
        $redirigirConError("El campo {$campo} es invalido.");
    }
    return $texto;
};

$validarEmail = static function ($valor) use ($redirigirConError): string {
    $texto = trim((string) $valor);
    if (!filter_var($texto, FILTER_VALIDATE_EMAIL)) {
        $redirigirConError('El email del propietario no es valido.');
    }
    return $texto;
};

$normalizarPlacaBus = static function ($valor) use ($redirigirConError): string {
    $texto = strtoupper(trim((string) $valor));
    $compacto = preg_replace('/\s+/', '', $texto);
    if (!preg_match('/^[A-Z]{3}[0-9]{3}$/', $compacto)) {
        $redirigirConError('El id_bus debe tener formato AAA123.');
    }
    return $compacto;
};

$id_propietario = $validarDocumento($_POST['id_propietario'], 'id_propietario');
$nom_propietario = $validarNombre($_POST['nom_propietario'], 'nom_propietario');
$ape_propietario = $validarNombre($_POST['ape_propietario'], 'ape_propietario');
$tel_propietario = $validarTelefono($_POST['tel_propietario'], 'tel_propietario');
$email_propietario = $validarEmail($_POST['email_propietario']);
$id_bus = $normalizarPlacaBus($_POST['id_bus']);

include_once '../base_de_datos.php';

try {
    $sentencia = $base_de_datos->prepare('SELECT fun_insert_propietarios(?, ?, ?, ?, ?, ?);');
    $sentencia->execute([
        $id_propietario,
        $id_bus,
        $nom_propietario,
        $ape_propietario,
        $tel_propietario,
        $email_propietario
    ]);

    $resultado = $sentencia->fetchColumn();
    $ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

    if ($ok) {
        header('Location: listar_propietarios.php?insertado=1');
        exit();
    }

    $redirigirConError('No fue posible registrar el propietario. Verifique los datos.');
} catch (PDOException $e) {
    $redirigirConError('Error de base de datos al registrar propietario.');
}

