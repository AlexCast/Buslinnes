<?php
/**
 * Middleware de Seguridad para proteger endpoints
 * Incluye protección contra: CSRF, Rate Limiting, Scraping, XSS
 */

class SecurityMiddleware {
    
    private static $rateLimit = 100; // Peticiones por minuto
    private static $rateLimitWindow = 60; // ventana en segundos
    private static $cachedRawInput = null;
    
    /**
     * Validar token CSRF
     */
    public static function validateCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || 
            $_SERVER['REQUEST_METHOD'] === 'PUT' || 
            $_SERVER['REQUEST_METHOD'] === 'DELETE') {
            
            // Asegurar que la sesión está iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Obtener token de varios lugares posibles
            $headers = function_exists('getallheaders') ? getallheaders() : [];
            $token = $_POST['csrf_token'] ?? 
                     $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 
                     ($headers['X-CSRF-Token'] ?? null) ??
                     ($headers['x-csrf-token'] ?? null) ??
                     null;

            // Si no vino por POST/header, intentar extraerlo del body JSON
            if (!$token) {
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                if (stripos($contentType, 'application/json') !== false) {
                    // Cache php://input — it can only be read once per request
                    if (self::$cachedRawInput === null) {
                        self::$cachedRawInput = file_get_contents('php://input') ?: '';
                    }
                    if (self::$cachedRawInput) {
                        $jsonData = json_decode(self::$cachedRawInput, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                            $token = $jsonData['csrf_token'] ?? null;
                        }
                    }
                }
            }
            
            // Debug: log si no hay token (puedes comentar esto en producción)
            if (!$token) {
                error_log("CSRF: No se recibió token. POST: " . print_r($_POST, true) . " Headers: " . print_r($headers, true));
            }
            
            if (!$token || !self::verifyCSRFToken($token)) {
                http_response_code(403);
                echo json_encode([
                    'error' => 'Token CSRF inválido o ausente'
                ]);
                exit;
            }
        }
    }
    
    /**
     * Generar token CSRF
     */
    public static function generateCSRFToken() {
        // Configurar sesión si aún no está configurada
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.use_strict_mode', 1);
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verificar token CSRF
     */
    private static function verifyCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            error_log("CSRF: No hay token en sesión. Session ID: " . session_id());
            return false;
        }
        
        // Verificar que el token no haya expirado (30 minutos)
        $tokenTime = $_SESSION['csrf_token_time'] ?? 0;
        if (time() - $tokenTime > 1800) {
            error_log("CSRF: Token expirado. Edad: " . (time() - $tokenTime) . " segundos");
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
        
        // Usar hash_equals para prevenir timing attacks
        $valid = hash_equals($_SESSION['csrf_token'], $token);
        
        if (!$valid) {
            error_log("CSRF: Token no coincide. Esperado: " . substr($_SESSION['csrf_token'], 0, 10) . "... Recibido: " . substr($token, 0, 10) . "...");
        }
        
        return $valid;
    }
    
    /**
     * Rate Limiting basado en IP
     */
    public static function checkRateLimit() {
        $ip = self::getClientIP();
        $key = 'rate_limit_' . md5($ip);
        
        // Usar APCu si está disponible, sino usar archivos temporales
        if (function_exists('apcu_fetch')) {
            $requests = apcu_fetch($key);
            if ($requests === false) {
                apcu_store($key, 1, self::$rateLimitWindow);
            } else {
                $requests++;
                apcu_store($key, $requests, self::$rateLimitWindow);
                
                if ($requests > self::$rateLimit) {
                    http_response_code(429);
                    echo json_encode(['error' => 'Demasiadas peticiones. Inténtalo más tarde.']);
                    exit;
                }
            }
        } else {
            // Fallback a archivos
            $file = sys_get_temp_dir() . '/' . $key . '.txt';
            
            if (file_exists($file)) {
                $data = json_decode(file_get_contents($file), true);
                $timestamp = $data['timestamp'] ?? 0;
                $count = $data['count'] ?? 0;
                
                if (time() - $timestamp < self::$rateLimitWindow) {
                    $count++;
                    if ($count > self::$rateLimit) {
                        http_response_code(429);
                        echo json_encode(['error' => 'Demasiadas peticiones. Inténtalo más tarde.']);
                        exit;
                    }
                } else {
                    $count = 1;
                    $timestamp = time();
                }
                
                file_put_contents($file, json_encode(['timestamp' => $timestamp, 'count' => $count]));
            } else {
                file_put_contents($file, json_encode(['timestamp' => time(), 'count' => 1]));
            }
        }
    }
    
    /**
     * Validar origen de la petición (anti-scraping)
     */
    public static function validateOrigin() {
        $validOrigins = [
            'http://localhost',
            'https://localhost',
            'http://127.0.0.1',
            $_SERVER['HTTP_HOST'] ?? ''
        ];
        
        // Verificar Referer
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Para peticiones AJAX verificar headers personalizados
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        
        // Si es una petición AJAX válida
        if (strtolower($requestedWith) === 'xmlhttprequest') {
            return true;
        }
        
        // Verificar que venga de un origen válido
        if ($origin) {
            $isValid = false;
            foreach ($validOrigins as $valid) {
                if (strpos($origin, $valid) !== false) {
                    $isValid = true;
                    break;
                }
            }
            
            if (!$isValid) {
                http_response_code(403);
                echo json_encode(['error' => 'Origen no permitido']);
                exit;
            }
        }
        
        return true;
    }
    
    /**
     * Validar User Agent (detectar bots maliciosos)
     */
    public static function validateUserAgent() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Lista de bots maliciosos conocidos
        $blockedBots = [
            'scrapy', 'curl', 'wget', 'python-requests', 
            'bot', 'crawler', 'spider', 'scraper'
        ];
        
        // Permitir bots legítimos
        $allowedBots = [
            'Googlebot', 'Bingbot', 'Yahoo! Slurp'
        ];
        
        $userAgentLower = strtolower($userAgent);
        
        // Verificar si es un bot permitido
        foreach ($allowedBots as $allowed) {
            if (stripos($userAgent, $allowed) !== false) {
                return true;
            }
        }
        
        // Bloquear bots maliciosos
        foreach ($blockedBots as $blocked) {
            if (strpos($userAgentLower, $blocked) !== false) {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                exit;
            }
        }
        
        // Si no tiene User Agent, bloquear
        if (empty($userAgent)) {
            http_response_code(403);
            echo json_encode(['error' => 'User Agent requerido']);
            exit;
        }
        
        return true;
    }
    
    /**
     * Sanitizar entrada para prevenir XSS
     */
    public static function sanitizeInput(&$data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeInput($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }
    
    /**
     * Validar JSON en peticiones POST/PUT
     */
    public static function validateJSON() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'JSON inválido']);
                exit;
            }
            
            return $data;
        }
        
        return null;
    }
    
    /**
     * Obtener IP real del cliente
     */
    private static function getClientIP() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
    
    /**
     * Headers de seguridad
     */
    public static function setSecurityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://unpkg.com https://router.project-osrm.org https://cdn.onesignal.com https://api.onesignal.com; style-src \'self\' \'unsafe-inline\' https://unpkg.com; img-src \'self\' data: https://tile.openstreetmap.org https://a.tile.openstreetmap.org https://b.tile.openstreetmap.org https://c.tile.openstreetmap.org; connect-src \'self\' https://router.project-osrm.org https://tile.openstreetmap.org https://a.tile.openstreetmap.org https://b.tile.openstreetmap.org https://c.tile.openstreetmap.org https://unpkg.com https://cdn.onesignal.com https://api.onesignal.com');
        
        // CORS headers si es necesario
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            }
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
            }
            exit(0);
        }
    }
    
    /**
     * Middleware completo - llamar al inicio de cada endpoint
     */
    /**
     * Returns the cached raw request body (safe to call after protect())
     */
    public static function getRawInput(): string {
        if (self::$cachedRawInput === null) {
            self::$cachedRawInput = file_get_contents('php://input') ?: '';
        }
        return self::$cachedRawInput;
    }

    public static function protect($options = []) {
        $defaults = [
            'csrf' => true,
            'rateLimit' => true,
            'origin' => true,
            'userAgent' => true,
            'securityHeaders' => true,
            'jwt' => true,
            'roles' => []
        ];

        // Backward compatibility:
        // - protect(true): default protections
        // - protect(false): disable active checks but keep security headers
        if (is_bool($options)) {
            $options = $options ? [] : [
                'csrf' => false,
                'rateLimit' => false,
                'origin' => false,
                'userAgent' => false,
                'securityHeaders' => true
            ];
        } elseif (!is_array($options)) {
            $options = [];
        }
        
        $options = array_merge($defaults, $options);
        
        if ($options['securityHeaders']) {
            self::setSecurityHeaders();
        }
        
        if ($options['rateLimit']) {
            self::checkRateLimit();
        }
        
        if ($options['origin']) {
            self::validateOrigin();
        }
        
        if ($options['userAgent']) {
            self::validateUserAgent();
        }
        
        if ($options['csrf']) {
            self::validateCSRF();
        }
        
        // Sanitizar inputs
        $_POST = self::sanitizeInput($_POST);
        $_GET = self::sanitizeInput($_GET);

        // Validar token JWT si está habilitado para esta ruta
        if (!empty($options['jwt'])) {
            $jwtRoles = $options['roles'] ?? [];

            $validarPath = __DIR__ . '/../src/validar_jwt.php';
            if (file_exists($validarPath)) {
                if (!defined('VALIDAR_JWT_SKIP_AUTORUN')) {
                    define('VALIDAR_JWT_SKIP_AUTORUN', true);
                }
                require_once $validarPath;
                validarTokenJWT($jwtRoles);
            } else {
                error_log('SecurityMiddleware: no se encontró validar_jwt.php en ' . $validarPath);
            }
        }

        return true;
    }
}
