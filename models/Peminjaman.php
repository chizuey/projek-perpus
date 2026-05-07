<?php

require_once __DIR__ . '/../config/database.php';

class Peminjaman
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public function all($search = '')
    {
        $sql = "SELECT p.*, a.kode_anggota, a.nama_anggota, b.judul
                FROM peminjaman p
                JOIN anggota a ON a.id_anggota = p.id_anggota
                JOIN buku b ON b.id_buku = p.id_buku
                WHERE p.status_pinjam IN ('borrowed', 'overdue')";
        
        if ($search) {
            $sql .= " AND (a.kode_anggota LIKE '%$search%' OR a.nama_anggota LIKE '%$search%' OR b.judul LIKE '%$search%')";
        }
        
        $sql .= " ORDER BY p.id_peminjaman DESC";
        $result = $this->conn->query($sql);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Mapping sederhana agar sesuai dengan tampilan
            $row['id'] = $row['id_peminjaman'];
            $row['nim'] = $row['kode_anggota'];
            $row['nama'] = $row['nama_anggota'];
            $row['buku'] = $row['judul'];
            $row['returned_at'] = $row['tanggal_kembali'];
            $row['tanggal_kembali'] = $row['tanggal_jatuh_tempo'];
            $data[] = $row;
        }
        return $data;
    }

    public function create($nim, $nama, $judul_buku, $adminId)
    {
        // 1. Cari atau buat anggota
        $id_anggota = $this->getAnggotaId($nim, $nama);
        
        // 2. Cari buku
        $res = $this->conn->query("SELECT id_buku, stok_tersedia FROM buku WHERE judul = '$judul_buku'");
        $book = $res->fetch_assoc();
        
        if (!$book || $book['stok_tersedia'] < 1) return false;
        
        $id_buku = $book['id_buku'];
        $tgl_pinjam = date('Y-m-d');
        $tgl_kembali = date('Y-m-d', strtotime('+7 days'));
        
        // 3. Insert peminjaman
        $sql = "INSERT INTO peminjaman (id_anggota, id_buku, id_admin, tanggal_pinjam, tanggal_jatuh_tempo, status_pinjam) 
                VALUES ($id_anggota, $id_buku, $adminId, '$tgl_pinjam', '$tgl_kembali', 'borrowed')";
        $this->conn->query($sql);
        
        // 4. Update stok buku
        $this->conn->query("UPDATE buku SET stok_tersedia = stok_tersedia - 1 WHERE id_buku = $id_buku");
        
        return true;
    }

    public function extend($id)
    {
        $res = $this->conn->query("SELECT tanggal_jatuh_tempo FROM peminjaman WHERE id_peminjaman = $id");
        if ($row = $res->fetch_assoc()) {
            $new_date = date('Y-m-d', strtotime($row['tanggal_jatuh_tempo'] . ' +7 days'));
            $sql = "UPDATE peminjaman SET tanggal_jatuh_tempo = '$new_date', extended_at = NOW() WHERE id_peminjaman = $id";
            return $this->conn->query($sql);
        }
        return false;
    }

    public function returnBook($id)
    {
        $res = $this->conn->query("SELECT id_buku FROM peminjaman WHERE id_peminjaman = $id");
        if ($row = $res->fetch_assoc()) {
            $id_buku = $row['id_buku'];
            $today = date('Y-m-d');
            
            // Update status peminjaman
            $this->conn->query("UPDATE peminjaman SET tanggal_kembali = '$today', status_pinjam = 'returned' WHERE id_peminjaman = $id");
            
            // Update stok buku
            $this->conn->query("UPDATE buku SET stok_tersedia = stok_tersedia + 1 WHERE id_buku = $id_buku");
            
            return true;
        }
        return false;
    }

    public function getOpsiBuku()
    {
        $res = $this->conn->query("SELECT judul, stok_tersedia FROM buku ORDER BY judul ASC");
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    private function getAnggotaId($nim, $nama)
    {
        $nim = $this->conn->real_escape_string($nim);
        $res = $this->conn->query("SELECT id_anggota FROM anggota WHERE kode_anggota = '$nim'");
        if ($row = $res->fetch_assoc()) {
            return $row['id_anggota'];
        }
        $this->conn->query("INSERT INTO anggota (kode_anggota, nama_anggota) VALUES ('$nim', '$nama')");
        return $this->conn->insert_id;
    }

    // Helper untuk hitung denda dan status
    public function getMeta($item)
    {
        $jatuh_tempo = strtotime($item['tanggal_jatuh_tempo']);
        $tgl_kembali = !empty($item['returned_at']) ? strtotime($item['returned_at']) : time();
        
        $terlambat = 0;
        if ($tgl_kembali > $jatuh_tempo) {
            $diff = $tgl_kembali - $jatuh_tempo;
            $terlambat = floor($diff / (60 * 60 * 24));
        }

        return [
            'status' => !empty($item['returned_at']) ? 'Dikembalikan' : ($terlambat > 0 ? 'Terlambat' : 'Dipinjam'),
            'terlambat' => $terlambat > 0 ? $terlambat . ' hari' : '-',
            'denda' => 'Rp ' . number_format($terlambat * 500, 0, ',', '.')
        ];
    }
}
