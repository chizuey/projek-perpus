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

    public function getActiveLoans($anggota)
    {
        $idList = $this->getRelatedAnggotaIds($anggota);
        $sql = "SELECT dp.*, p.id_peminjaman, p.id_anggota, p.tanggal_peminjaman, p.batas_waktu,
                       b.judul, b.penulis, b.cover, e.id_eksemplar, e.status AS status_eksemplar
                FROM detail_peminjaman dp
                JOIN peminjaman p ON p.id_peminjaman = dp.id_peminjaman
                JOIN eksemplar e ON e.id_eksemplar = dp.id_eksemplar
                JOIN buku b ON b.id_buku = e.id_buku
                WHERE p.id_anggota IN ($idList)
                  AND dp.status_pengembalian = 'dipinjam'
                ORDER BY p.tanggal_peminjaman DESC, dp.id_detail DESC";
        $result = $this->conn->query($sql);
        
        $loans = [];
        while ($row = $result->fetch_assoc()) {
            $loans[] = $row;
        }
        return $loans;
    }

    public function getLoanHistory($anggota)
    {
        $idList = $this->getRelatedAnggotaIds($anggota);
        $sql = "SELECT dp.*, p.id_peminjaman, p.id_anggota, p.tanggal_peminjaman, p.batas_waktu,
                       b.judul, b.penulis, b.cover, e.id_eksemplar, e.status AS status_eksemplar
                FROM detail_peminjaman dp
                JOIN peminjaman p ON p.id_peminjaman = dp.id_peminjaman
                JOIN eksemplar e ON e.id_eksemplar = dp.id_eksemplar
                JOIN buku b ON b.id_buku = e.id_buku
                WHERE p.id_anggota IN ($idList)
                ORDER BY p.tanggal_peminjaman DESC, dp.id_detail DESC";
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

    private function getRelatedAnggotaIds($anggota)
    {
        if (!is_array($anggota)) {
            return (string)(int)$anggota;
        }

        $id = (int)($anggota['id_anggota'] ?? 0);
        $nim = $this->conn->real_escape_string($anggota['nim'] ?? '');
        $email = $anggota['email'] ?? '';
        $emailNim = $this->conn->real_escape_string(strtok($email, '@') ?: '');

        $where = ["id_anggota = $id"];
        if ($nim !== '') $where[] = "LOWER(nim) = LOWER('$nim')";
        if ($emailNim !== '') $where[] = "LOWER(nim) = LOWER('$emailNim')";

        $sql = "SELECT id_anggota FROM anggota WHERE " . implode(' OR ', $where);
        $result = $this->conn->query($sql);

        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = (int)$row['id_anggota'];
        }

        return !empty($ids) ? implode(',', array_unique($ids)) : (string)$id;
    }
}
