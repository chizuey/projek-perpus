<?php

require_once __DIR__ . '/../config/database.php';

class Kategori
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public function all()
    {
        $sql = "SELECT k.id_kategori, k.nama_kategori, COUNT(b.id_buku) AS jumlah_buku
                FROM kategori k
                LEFT JOIN buku b ON b.id_kategori = k.id_kategori
                GROUP BY k.id_kategori, k.nama_kategori
                ORDER BY k.id_kategori DESC";
        $result = $this->conn->query($sql);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function find($id)
    {
        $sql = "SELECT * FROM kategori WHERE id_kategori = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($nama)
    {
        $sql = "INSERT INTO kategori (nama_kategori) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nama);
        return $stmt->execute();
    }

    public function update($id, $nama)
    {
        $sql = "UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $nama, $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM kategori WHERE id_kategori = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
