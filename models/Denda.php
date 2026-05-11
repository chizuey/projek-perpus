<?php

require_once __DIR__ . '/../config/database.php';

class Denda
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    /**
     * Ambil semua data denda dengan JOIN ke detail_peminjaman, buku, dan anggota.
     */
    public function all()
    {
        $sql = "SELECT d.*, dp.id_peminjaman, b.judul, a.nama as nama_anggota
                FROM denda d
                JOIN detail_peminjaman dp ON d.id_detail = dp.id_detail
                JOIN eksemplar e ON dp.id_eksemplar = e.id_eksemplar
                JOIN buku b ON e.id_buku = b.id_buku
                JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman
                JOIN anggota a ON p.id_anggota = a.id_anggota
                ORDER BY d.id_denda DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Hitung denda berdasarkan hari terlambat (misal Rp 500 per hari).
     */
    public function hitungDenda($hariTerlambat)
    {
        $tarif = 500;
        return $hariTerlambat * $tarif;
    }

    /**
     * Simpan atau update denda.
     */
    public function save($idDetail, $hariTerlambat)
    {
        $sql = "INSERT INTO denda (id_detail, jumlah_hari_terlambat) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE jumlah_hari_terlambat = VALUES(jumlah_hari_terlambat)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $idDetail, $hariTerlambat);
        return $stmt->execute();
    }

    /**
     * Tandai denda sebagai lunas.
     */
    public function bayar($idDenda)
    {
        $today = date('Y-m-d');
        $sql = "UPDATE denda SET tanggal_bayar = ? WHERE id_denda = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $today, $idDenda);
        return $stmt->execute();
    }
}
