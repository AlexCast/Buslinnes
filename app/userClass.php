<?php
// class/userClass.php

class userClass {
    public $db;
    public $lastErrorCode = null;
    public $lastErrorMessage = null;
    
    public function __construct() {
    global $db;
    $this->db = $db;
    // Añade verificación
    if(!$this->db) {
        error_log("Error: No hay conexión a la base de datos");
        throw new Exception("Database connection failed");
    }
}
    
    /**
     * Registro de usuario
     * @param string $password
     * @param string $email
     * @param string $name
     * @return boolean
     */
    public function fun_insert_usuario($password, $correo, $nombre) {
        try {
            $this->lastErrorCode = null;
            $this->lastErrorMessage = null;
            error_log("Intentando registrar usuario: $nombre, $correo"); // Log de depuración

            if (empty($password) || empty($correo) || empty($nombre)) {
                $this->lastErrorCode = 'INVALID_INPUT';
                $this->lastErrorMessage = 'Datos incompletos';
                error_log("Error: Datos incompletos");
                return false;
            }

            // Verificar correo existente (el nombre puede repetirse)
            $stmt = $this->db->prepare("SELECT id_usuario FROM tab_usuarios WHERE correo = ? LIMIT 1");
            if(!$stmt->execute([$correo])) {
                $this->lastErrorCode = 'SYSTEM_ERROR';
                $this->lastErrorMessage = 'Error verificando correo existente';
                error_log("Error en verificación de usuario existente: " . print_r($stmt->errorInfo(), true));
                return false;
            }

            if ($stmt->fetchColumn()) {
                $this->lastErrorCode = 'EMAIL_EXISTS';
                $this->lastErrorMessage = 'El correo electrónico ya está registrado en el sistema.';
                error_log("Correo ya existe");
                return false;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("INSERT INTO tab_usuarios (contrasena, correo, nombre) VALUES (?, ?, ?) RETURNING id_usuario");
            if(!$stmt->execute([$hashedPassword, $correo, $nombre])) {
                $errorInfo = $stmt->errorInfo();
                $sqlState = $errorInfo[0] ?? null;
                $this->lastErrorCode = $sqlState === '23505' ? 'EMAIL_EXISTS' : 'SYSTEM_ERROR';
                $this->lastErrorMessage = $sqlState === '23505'
                    ? 'El correo electrónico ya está registrado en el sistema.'
                    : 'No fue posible registrar el usuario en este momento.';
                error_log("Error en INSERT: " . print_r($errorInfo, true));
                return false;
            }

            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Usuario registrado con ID: " . $result['id_usuario']);
                $_SESSION['id_usuario'] = $result['id_usuario'];
                $_SESSION['nombre'] = $nombre;
                return true;
            }
            $this->lastErrorCode = 'SYSTEM_ERROR';
            $this->lastErrorMessage = 'No fue posible registrar el usuario en este momento.';
            error_log("No se pudo registrar el usuario (rowCount = 0)");
            return false;
        } catch (PDOException $e) {
            $this->lastErrorCode = $e->getCode() === '23505' ? 'EMAIL_EXISTS' : 'SYSTEM_ERROR';
            $this->lastErrorMessage = $this->lastErrorCode === 'EMAIL_EXISTS'
                ? 'El correo electrónico ya está registrado en el sistema.'
                : 'No fue posible registrar el usuario en este momento.';
            error_log("Excepción en fun_insert_usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Inicio de sesión del usuario validando el hash de la contraseña en PHP
     * @param string $usernameEmail Nombre de usuario o email
     * @param string $password Contraseña sin encriptar
     * @return mixed ID del usuario si es exitoso, false si falla
     */
    public function iniciar_sesion_usuario($email, $password) {
        try {
            // Buscar usuario por correo
            $stmt = $this->db->prepare("SELECT id_usuario, correo, contrasena FROM tab_usuarios WHERE correo = :correo LIMIT 1");
            $stmt->bindParam(':correo', $email);
            $stmt->execute();
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userData && password_verify($password, $userData['contrasena'])) {
                $_SESSION['id_usuario'] = $userData['id_usuario'];
                $_SESSION['correo'] = $userData['correo'];
                return $userData['id_usuario'];
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en userLogin: " . $e->getMessage());
            return false;
        }
    }
}

