<?php

require_once __DIR__ . '/../config/database.php';

class Anggota
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public function findByEmail($email)
    {
        $email = $this->conn->real_escape_string($email);
        $sql = "SELECT * FROM anggota WHERE email = '$email' LIMIT 1";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }

    public function findById($id)
    {
        $id = (int)$id;
        $sql = "SELECT * FROM anggota WHERE id_anggota = $id LIMIT 1";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }

    public function getActiveLoans($idAnggota)
    {
        $idAnggota = (int)$idAnggota;
        $sql = "SELECT dp.*, p.tanggal_peminjaman, p.batas_waktu, b.judul, b.penulis, b.cover
                FROM detail_peminjaman dp
                JOIN peminjaman p ON p.id_peminjaman = dp.id_peminjaman
                JOIN eksemplar e ON e.id_eksemplar = dp.id_eksemplar
                JOIN buku b ON b.id_buku = e.id_buku
                WHERE p.id_anggota = $idAnggota AND dp.status_pengembalian = 'dipinjam'
                ORDER BY p.tanggal_peminjaman DESC";
        $result = $this->conn->query($sql);
        
        $loans = [];
        while ($row = $result->fetch_assoc()) {
            $loans[] = $row;
        }
        return $loans;
    }

    public function getLoanHistory($idAnggota)
    {
        $idAnggota = (int)$idAnggota;
        $sql = "SELECT dp.*, p.tanggal_peminjaman, p.batas_waktu, b.judul, b.penulis, b.cover
                FROM detail_peminjaman dp
                JOIN peminjaman p ON p.id_peminjaman = dp.id_peminjaman
                JOIN eksemplar e ON e.id_eksemplar = dp.id_eksemplar
                JOIN buku b ON b.id_buku = e.id_buku
                WHERE p.id_anggota = $idAnggota
                ORDER BY p.tanggal_peminjaman DESC";
        $result = $this->conn->query($sql);
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        return $history;
    }

    public function updateDenda($idAnggota, $totalDenda)
    {
        $idAnggota = (int)$idAnggota;
        $totalDenda = (int)$totalDenda;
        $sql = "UPDATE anggota SET total_denda = $totalDenda WHERE id_anggota = $idAnggota";
        return $this->conn->query($sql);
    }
}
