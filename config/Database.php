<?php
/**
 * Clase Database - Manejo centralizado de conexiones PDO
 * Implementa patrón Singleton para una sola instancia de conexión
 */
class Database {
    private static $instance = null;
    private $pdo;
    
    // Configuración de base de datos
    private const DB_CONFIG = [
        'host' => 'localhost',
        'dbname' => 'db_restaurante',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ];
    
    // Opciones PDO por defecto
    private const PDO_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Obtiene la instancia única de Database
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establece la conexión PDO
     */
    private function connect(): void {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                self::DB_CONFIG['host'],
                self::DB_CONFIG['dbname'],
                self::DB_CONFIG['charset']
            );
            
            $this->pdo = new PDO(
                $dsn,
                self::DB_CONFIG['username'],
                self::DB_CONFIG['password'],
                self::PDO_OPTIONS
            );
            
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
    
    /**
     * Obtiene la instancia PDO
     */
    public function getConnection(): PDO {
        return $this->pdo;
    }
    
    /**
     * Ejecuta una consulta preparada
     */
    public function query(string $sql, array $params = []): PDOStatement {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Error en la consulta a la base de datos");
        }
    }
    
    /**
     * Obtiene un solo registro
     */
    public function fetchOne(string $sql, array $params = []): ?array {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Obtiene múltiples registros
     */
    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Ejecuta una consulta y retorna el número de filas afectadas
     */
    public function execute(string $sql, array $params = []): int {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Obtiene el último ID insertado
     */
    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Inicia una transacción
     */
    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Confirma una transacción
     */
    public function commit(): bool {
        return $this->pdo->commit();
    }
    
    /**
     * Revierte una transacción
     */
    public function rollback(): bool {
        return $this->pdo->rollback();
    }
    
    /**
     * Verifica si hay una transacción activa
     */
    public function inTransaction(): bool {
        return $this->pdo->inTransaction();
    }
    
    // Prevenir clonación y deserialización
    private function __clone() {}
    public function __wakeup() {}
}