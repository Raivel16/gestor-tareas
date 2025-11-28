<?php
/**
 * Database Configuration
 * Configuración de conexión a la base de datos MariaDB
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // Configuración de la base de datos (Laragon defaults)
    private $host = 'localhost';
    private $db_name = 'gestor_tareas';
    private $username = 'root';
    private $password = ''; // NuevaContrasena123 -> Rocky
    private $charset = 'utf8mb4';
    
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            die(json_encode([
                'success' => false,
                'message' => 'Error de conexión a la base de datos'
            ]));
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevenir clonación
    private function __clone() {}
    
    // Prevenir unserialize
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
