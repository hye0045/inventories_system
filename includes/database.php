<?php
require_once(__DIR__ . '/config.php');
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
     * Thực thi các câu lệnh thay đổi dữ liệu (INSERT, UPDATE, DELETE).
     * Trả về TRUE nếu có ít nhất 1 dòng bị ảnh hưởng, ngược lại trả về FALSE.
     */
    public function query(string $sql, array $params = []): bool {
        $this->connect();
        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("SQL prepare statement failed: " . $this->connection->error);
        }
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); 
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        
        // Lấy số dòng bị ảnh hưởng từ statement và trả về true/false
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        
        return $affected_rows > 0;
    }

    /**
     * Tiện ích: Lấy tất cả các dòng kết quả từ câu lệnh SELECT.
     */
    public function fetchAll(string $sql, array $params = []): array {
        $this->connect();
        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("SQL prepare statement failed: " . $this->connection->error);
        }
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    /**
     * Tiện ích: Lấy một dòng kết quả từ câu lệnh SELECT.
     */
    public function fetchOne(string $sql, array $params = []): ?array {
        $this->connect();
        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("SQL prepare statement failed: " . $this->connection->error);
        }
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
    public function escape(string $str): string {
        $this->connect(); // Đảm bảo đã kết nối CSDL
        return $this->connection->real_escape_string($str);
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
        if ($this->connection) $this->connection->commit();
    }

    public function rollback(): void {
        if ($this->connection) $this->connection->rollback();
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