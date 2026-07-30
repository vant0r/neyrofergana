<?php
/**
 * PDO Database Wrapper
 * Ma'lumotlar bazasi bilan ishlash uchun qulay interfeys
 */

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("DB Connection Error: " . $e->getMessage());
            die("Ma'lumotlar bazasiga ulanishda xatolik yuz berdi.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    // SELECT query
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            return false;
        }
    }

    // Bitta qatorni olish
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetch();
        }
        return false;
    }

    // Barcha qatorlarni olish
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetchAll();
        }
        return [];
    }

    // INSERT, UPDATE, DELETE
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Execute Error: " . $e->getMessage());
            return false;
        }
    }

    // Oxirgi qo'shilgan ID
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    // Transaction boshlash
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    // Transaction commit
    public function commit() {
        return $this->pdo->commit();
    }

    // Transaction rollback
    public function rollback() {
        return $this->pdo->rollBack();
    }

    // Qatorlar sonini olish
    public function rowCount($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->rowCount();
        }
        return 0;
    }
}

// Global funksiya - DB instance olish
function getDB() {
    return Database::getInstance();
}

// Qisqa funksiya - query bajarish
function dbQuery($sql, $params = []) {
    return getDB()->query($sql, $params);
}

// Qisqa funksiya - bitta qator olish
function dbFetchOne($sql, $params = []) {
    return getDB()->fetchOne($sql, $params);
}

// Qisqa funksiya - barcha qatorlar
function dbFetchAll($sql, $params = []) {
    return getDB()->fetchAll($sql, $params);
}

// Qisqa funksiya - INSERT/UPDATE/DELETE
function dbExecute($sql, $params = []) {
    return getDB()->execute($sql, $params);
}
