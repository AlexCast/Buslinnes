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
     * @param string $tipo_doc
     * @param string $id_usuario
     * @param string $nom_usuario
     * @param string $email_usuario
     * @param string $contraseña
     * 
     * 
     * @return boolean
     */
    public function fun_insert_usuario($tipo_doc, $id_usuario, $nom_usuario, $email_usuario, $contraseña) {
        try {
            $this->lastErrorCode = null;
            $this->lastErrorMessage = null;
            error_log("Intentando registrar usuario: $tipo_doc, $id_usuario, $nom_usuario, $email_usuario");

            if (empty($tipo_doc) || empty($id_usuario) || empty($nom_usuario) || empty($email_usuario) || empty($contraseña)) {
                $this->lastErrorCode = 'INVALID_INPUT';
                $this->lastErrorMessage = 'Datos incompletos';
                error_log("Error: Datos incompletos");
                return false;
            }

            // Verificar correo existente (el nombre puede repetirse)
            $stmt = $this->db->prepare("SELECT id_usuario FROM tab_usuarios WHERE email_usuario = ? AND usr_delete IS NULL LIMIT 1");
            if(!$stmt->execute([$email_usuario])) {
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

            $hashedPassword = password_hash($contraseña, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("INSERT INTO tab_usuarios (tipo_doc, id_usuario, nom_usuario, email_usuario, contrasena, usr_insert, fec_insert) VALUES (?, ?, ?, ?, ?, ?, NOW()) RETURNING id_usuario");
            if(!$stmt->execute([$tipo_doc, $id_usuario, $nom_usuario, $email_usuario, $hashedPassword, 'self_register'])) {
                $errorInfo = $stmt->errorInfo();
                $sqlState = $errorInfo[0] ?? null;
                $this->lastErrorCode = $sqlState === '23505' ? 'EMAIL_EXISTS' : 'SYSTEM_ERROR';
                $this->lastErrorMessage = $sqlState === '23505'
                    ? 'El correo electrónico ya está registrado en el sistema.'
                    : 'No fue posible registrar el usuario en este momento.';
                error_log("Error en INSERT: " . print_r($errorInfo, true));
                return false;
            }

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && !empty($result['id_usuario'])) {
                error_log("Usuario registrado con ID: " . $result['id_usuario']);
                $_SESSION['id_usuario'] = $result['id_usuario'];
                $_SESSION['nombre'] = $nom_usuario;
                return true;
            }

            $this->lastErrorCode = 'SYSTEM_ERROR';
            $this->lastErrorMessage = 'No fue posible registrar el usuario en este momento.';
            error_log("No se pudo registrar el usuario: no se obtuvo ID del registro");
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
     * @param string $contraseña Contraseña sin encriptar
     * @return mixed ID del usuario si es exitoso, false si falla
     */
    public function iniciar_sesion_usuario($email_usuario, $contraseña) {
        try {
            $isPasswordValid = false;
            // Buscar usuario activo por correo
            $stmt = $this->db->prepare("SELECT id_usuario, email_usuario, contrasena, usr_update, fec_update FROM tab_usuarios WHERE email_usuario = :email_usuario AND usr_delete IS NULL LIMIT 1");
            $stmt->bindParam(':email_usuario', $email_usuario);
            $stmt->execute();
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userData) {
                $isPasswordValid = password_verify($contraseña, $userData['contrasena']);
                $inputFingerprint = substr(hash('sha256', (string)$contraseña), 0, 12);
                $hashFingerprint = substr(hash('sha256', (string)$userData['contrasena']), 0, 12);

                if ($isPasswordValid) {
                    error_log(
                        "[login_debug] Password válida para email={$email_usuario} " .
                        "id_usuario={$userData['id_usuario']} " .
                        "input_len=" . strlen((string)$contraseña) . " " .
                        "input_fp={$inputFingerprint} " .
                        "hash_fp={$hashFingerprint} " .
                        "usr_update=" . ($userData['usr_update'] ?? 'NULL') . " " .
                        "fec_update=" . ($userData['fec_update'] ?? 'NULL')
                    );
                }

                if (!$isPasswordValid) {
                    // Diagnóstico temporal: no registra contraseña, solo metadatos.
                    error_log(
                        "[login_debug] Password no válida para email={$email_usuario} " .
                        "id_usuario={$userData['id_usuario']} " .
                        "input_len=" . strlen((string)$contraseña) . " " .
                        "hash_prefix=" . substr((string)$userData['contrasena'], 0, 4) . " " .
                        "input_fp={$inputFingerprint} " .
                        "hash_fp={$hashFingerprint} " .
                        "usr_update=" . ($userData['usr_update'] ?? 'NULL') . " " .
                        "fec_update=" . ($userData['fec_update'] ?? 'NULL')
                    );
                }
            } else {
                error_log("[login_debug] Usuario no encontrado para email={$email_usuario}");
            }

            if ($userData && $isPasswordValid) {
                $_SESSION['id_usuario'] = $userData['id_usuario'];
                $_SESSION['email_usuario'] = $userData['email_usuario'];
                return $userData['id_usuario'];
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en userLogin: " . $e->getMessage());
            return false;
        }
    }
}

