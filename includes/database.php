<?php
require_once(LIB_PATH_INC.DS."config.php");

class Database {
    private ?mysqli $connection = null;

    public function __construct() {}

    private function connect(): void {
        if ($this->connection !== null) {
            return;
        }
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $this->connection->set_charset("utf8mb4");
        } catch (mysqli_sql_exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Thực thi câu lệnh SQL an toàn bằng Prepared Statements.
     */
    public function query(string $sql, array $params = []): mysqli_stmt {
        $this->connect();
        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("SQL prepare statement failed: " . $this->connection->error);
        }
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Mặc định là string, an toàn nhất
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    /**
     * Tiện ích: Lấy tất cả các dòng kết quả.
     */
    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Tiện ích: Lấy một dòng kết quả.
     */
    public function fetchOne(string $sql, array $params = []): ?array {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Lấy ID của dòng cuối cùng được chèn.
     */
    public function lastInsertId(): int {
        return $this->connection->insert_id;
    }
    
    // --- Các hàm hỗ trợ Transaction ---
    public function beginTransaction(): void {
        $this->connect();
        $this->connection->begin_transaction();
    }
    
    public function commit(): void {
        $this->connection->commit();
    }

    public function rollback(): void {
        $this->connection->rollback();
    }

    public function disconnect(): void {
        if ($this->connection !== null) {
            $this->connection->close();
            $this->connection = null;
        }
    }
}

$db = new Database();
?>