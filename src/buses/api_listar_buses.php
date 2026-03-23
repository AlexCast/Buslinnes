<?php
// === Configurar encoding UTF-8 ===
header('Content-Type: text/html; charset=utf-8');
/**
 * API protegida para listar buses
 * Ejemplo de implementación con SecurityMiddleware
 */

// 1. Incluir el middleware de seguridad
require_once __DIR__ . '/../../app/SecurityMiddleware.php';

// 2. Proteger endpoint (GET no requiere CSRF)
SecurityMiddleware::protect([
    'csrf' => false,          // No requerimos CSRF para GET
    'rateLimit' => true,      // Sí límite de peticiones
    'origin' => true,         // Validar origen
    'userAgent' => true,      // Verificar User Agent
    'securityHeaders' => true // Headers de seguridad
]);

// 3. Validar JWT (autenticación)
if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor', 'pasajero']);

// 4. Lógica del endpoint
require_once __DIR__ . '/../base_de_datos.php';

header('Content-Type: application/json');

try {
    // Obtener parámetros de filtrado
    $incluirEliminados = isset($_GET['incluir_eliminados']) && $_GET['incluir_eliminados'] === 'true';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Query base
    $query = "
        SELECT 
            b.id_bus, 
            b.id_conductor, 
            b.num_chasis, 
            b.matricula, 
            b.anio_fab, 
            b.capacidad_pasajeros, 
            b.tipo_bus,
            b.gps, 
            b.ind_estado_buses, 
            b.fec_insert, 
            b.usr_insert,
            b.fec_delete,
            c.nom_conductor AS nombre_conductor
        FROM tab_buses b
        LEFT JOIN tab_conductores c ON b.id_conductor = c.id_conductor
    ";
    
    // Filtrar eliminados si es necesario
    if (!$incluirEliminados) {
        $query .= " WHERE b.fec_delete IS NULL";
    }
    
    $query .= " ORDER BY b.fec_insert DESC LIMIT ? OFFSET ?";
    
    $stmt = $base_de_datos->prepare($query);
    $stmt->execute([$limit, $offset]);
    $buses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar total
    $countQuery = "SELECT COUNT(*) FROM tab_buses";
    if (!$incluirEliminados) {
        $countQuery .= " WHERE fec_delete IS NULL";
    }
    $stmtCount = $base_de_datos->query($countQuery);
    $total = $stmtCount->fetchColumn();
    
    // Contar eliminados
    $stmtDeleted = $base_de_datos->query("SELECT COUNT(*) FROM tab_buses WHERE fec_delete IS NOT NULL");
    $totalEliminados = $stmtDeleted->fetchColumn();
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'data' => $buses,
        'pagination' => [
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset,
            'count' => count($buses)
        ],
        'stats' => [
            'total_activos' => (int)$total,
            'total_eliminados' => (int)$totalEliminados,
            'total_general' => (int)($total + $totalEliminados)
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos',
        'message' => 'No se pudieron obtener los buses'
    ]);
    error_log("Error en API buses: " . $e->getMessage());
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor',
        'message' => $e->getMessage()
    ]);
}


