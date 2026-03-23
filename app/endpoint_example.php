<?php
/**
 * EJEMPLO: Endpoint protegido con SecurityMiddleware
 * Copia este patrón en tus endpoints
 */

// 1. Incluir el middleware
require_once __DIR__ . '/../../app/SecurityMiddleware.php';

// 2. Activar protección completa
SecurityMiddleware::protect([
    'csrf' => true,           // Protección CSRF
    'rateLimit' => true,      // Límite de peticiones
    'origin' => true,         // Validar origen
    'userAgent' => true,      // Verificar User Agent
    'securityHeaders' => true // Headers de seguridad
]);

// 3. Opcional: Validar JWT (si requiere autenticación)
require_once __DIR__ . '/../../src/validar_jwt.php';
validarTokenJWT(['admin', 'conductor']); // Roles permitidos

// 4. Tu lógica del endpoint
require_once __DIR__ . '/../../src/base_de_datos.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Listar buses
        $stmt = $base_de_datos->query("
            SELECT id_bus, placa, modelo, ind_estado_buses 
            FROM tab_buses 
            WHERE fec_delete IS NULL
            ORDER BY id_bus DESC
        ");
        $buses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $buses
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Crear bus
        $data = SecurityMiddleware::validateJSON() ?? $_POST;
        
        // Validar datos requeridos
        if (empty($data['placa']) || empty($data['modelo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            exit;
        }
        
        $stmt = $base_de_datos->prepare("
            INSERT INTO tab_buses (placa, modelo, ind_estado_buses, fec_insert)
            VALUES (?, ?, 'A', NOW())
        ");
        
        $stmt->execute([$data['placa'], $data['modelo']]);
        
        echo json_encode([
            'success' => true,
            'id' => $base_de_datos->lastInsertId(),
            'message' => 'Bus creado exitosamente'
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Actualizar bus
        $data = SecurityMiddleware::validateJSON() ?? $_POST;
        
        if (empty($data['id_bus'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido']);
            exit;
        }
        
        $stmt = $base_de_datos->prepare("
            UPDATE tab_buses 
            SET placa = ?, modelo = ?, ind_estado_buses = ?, fec_update = NOW()
            WHERE id_bus = ? AND fec_delete IS NULL
        ");
        
        $stmt->execute([
            $data['placa'] ?? null,
            $data['modelo'] ?? null,
            $data['estado'] ?? 'A',
            $data['id_bus']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Bus actualizado exitosamente'
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Eliminar bus (soft delete)
        $id = $_GET['id'] ?? $_POST['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido']);
            exit;
        }
        
        $stmt = $base_de_datos->prepare("
            UPDATE tab_buses 
            SET fec_delete = NOW()
            WHERE id_bus = ?
        ");
        
        $stmt->execute([$id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Bus eliminado exitosamente'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error del servidor',
        'message' => $e->getMessage()
    ]);
}


