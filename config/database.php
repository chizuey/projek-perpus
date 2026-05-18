<?php

class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $password = '1deA050806';
    private $db = 'db_perpustakaan';

    public $conn;

    public function __construct() {
        $hostingConfig = __DIR__ . '/hosting.php';
        if (file_exists($hostingConfig)) {
            $config = require $hostingConfig;
            $this->host = $config['host'] ?? $this->host;
            $this->user = $config['user'] ?? $this->user;
            $this->password = $config['password'] ?? $this->password;
            $this->db = $config['database'] ?? $this->db;
        }

        $this->conn = null;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conn = new mysqli(
                $this->host,
                $this->user,
                $this->password,
                $this->db
            );

            if ($this->conn->connect_error) {
                die('gagal koneksi'.$this->conn->connect_error);
            }
            $this->conn->set_charset('utf8mb4');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}

// $db = new Database();
// $conn = $db->getConnection();

?>
