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
        // Menampilkan 1 baris per transaksi peminjaman (Header)
        $sql = "SELECT p.*, a.nim, a.nama
                FROM peminjaman p
                JOIN anggota a ON a.id_anggota = p.id_anggota
                WHERE EXISTS (SELECT 1 FROM detail_peminjaman dp WHERE dp.id_peminjaman = p.id_peminjaman AND dp.status_pengembalian = 'dipinjam')";
        
        if ($search) {
            $sql .= " AND (a.nim LIKE '%$search%' OR a.nama LIKE '%$search%')";
        }
        
        $sql .= " ORDER BY p.id_peminjaman DESC";
        $result = $this->conn->query($sql);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $row['id'] = $row['id_peminjaman'];
            $data[] = $row;
        }
        return $data;
    }

    public function getDetails($idPeminjaman)
    {
        $sql = "SELECT dp.*, b.judul, e.id_eksemplar, p.batas_waktu
                FROM detail_peminjaman dp
                JOIN eksemplar e ON e.id_eksemplar = dp.id_eksemplar
                JOIN buku b ON b.id_buku = e.id_buku
                JOIN peminjaman p ON p.id_peminjaman = dp.id_peminjaman
                WHERE dp.id_peminjaman = $idPeminjaman";
        $result = $this->conn->query($sql);
        
        $details = [];
        while ($row = $result->fetch_assoc()) {
            $meta = $this->getMeta($row);
            $row['status_teks'] = $meta['status'];
            $row['denda_teks'] = $meta['denda'];
            $details[] = $row;
        }
        return $details;
    }

    public function create($nim, $nama, $id_eksemplar_array, $adminId)
    {
        $id_anggota = $this->getAnggotaId($nim, $nama);
        $tgl_pinjam = date('Y-m-d');
        $tgl_jatuh_tempo = date('Y-m-d', strtotime('+7 days'));

        $this->conn->begin_transaction();

        try {
            $sql_header = "INSERT INTO peminjaman (id_anggota, id_admin, tanggal_peminjaman, batas_waktu) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql_header);
            $stmt->bind_param("iiss", $id_anggota, $adminId, $tgl_pinjam, $tgl_jatuh_tempo);
            $stmt->execute();
            $id_peminjaman = $this->conn->insert_id;

            $count = 0;
            foreach ($id_eksemplar_array as $id_eksemplar) {
                if (empty($id_eksemplar)) continue;

                $res_eks = $this->conn->query("SELECT id_eksemplar FROM eksemplar WHERE id_eksemplar = " . (int)$id_eksemplar . " AND status = 'tersedia'");
                if ($res_eks->num_rows > 0) {
                    $sql_detail = "INSERT INTO detail_peminjaman (id_peminjaman, id_eksemplar, status_pengembalian) VALUES (?, ?, 'dipinjam')";
                    $stmt_detail = $this->conn->prepare($sql_detail);
                    $stmt_detail->bind_param("ii", $id_peminjaman, $id_eksemplar);
                    $stmt_detail->execute();

                    $this->conn->query("UPDATE eksemplar SET status = 'dipinjam' WHERE id_eksemplar = " . (int)$id_eksemplar);
                    $count++;
                }
            }

            if ($count === 0) throw new Exception("Tidak ada buku yang berhasil dipinjam.");

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function extend($idDetail)
    {
        // Perpanjang dilakukan PER BUKU (detail), tapi karena batas_waktu di header, 
        // kita perbarui header dan tandai detailnya sudah pernah diperpanjang.
        $res = $this->conn->query("SELECT p.id_peminjaman, p.batas_waktu 
                                  FROM detail_peminjaman dp 
                                  JOIN peminjaman p ON p.id_peminjaman = dp.id_peminjaman 
                                  WHERE dp.id_detail = $idDetail");
        if ($row = $res->fetch_assoc()) {
            $id_peminjaman = $row['id_peminjaman'];
            $new_date = date('Y-m-d', strtotime($row['batas_waktu'] . ' +7 days'));
            
            $sql = "UPDATE peminjaman SET batas_waktu = ? WHERE id_peminjaman = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $new_date, $id_peminjaman);
            $stmt->execute();
            
            $this->conn->query("UPDATE detail_peminjaman SET extended_at = NOW() WHERE id_detail = $idDetail");
            return true;
        }
        return false;
    }

    public function returnBook($idDetail)
    {
        $res = $this->conn->query("SELECT id_eksemplar FROM detail_peminjaman WHERE id_detail = $idDetail");
        if ($row = $res->fetch_assoc()) {
            $id_eksemplar = $row['id_eksemplar'];
            $today = date('Y-m-d');
            
            $sql = "UPDATE detail_peminjaman SET tanggal_kembali = ?, status_pengembalian = 'kembali' WHERE id_detail = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $today, $idDetail);
            $stmt->execute();
            
            $this->conn->query("UPDATE eksemplar SET status = 'tersedia' WHERE id_eksemplar = $id_eksemplar");
            return true;
        }
        return false;
    }

    public function getOpsiBuku()
    {
        $res = $this->conn->query("SELECT e.id_eksemplar, b.judul 
                                  FROM eksemplar e 
                                  JOIN buku b ON e.id_buku = b.id_buku 
                                  WHERE e.status = 'tersedia' 
                                  ORDER BY b.judul ASC");
        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    private function getAnggotaId($nim, $nama)
    {
        $nim = $this->conn->real_escape_string($nim);
        $res = $this->conn->query("SELECT id_anggota FROM anggota WHERE nim = '$nim'");
        if ($row = $res->fetch_assoc()) {
            return $row['id_anggota'];
        }
        
        $dummy_email = $nim . '@student.com';
        $dummy_pass = password_hash('123456', PASSWORD_DEFAULT);
        $sql = "INSERT INTO anggota (nim, nama, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $nim, $nama, $dummy_email, $dummy_pass);
        $stmt->execute();
        return $this->conn->insert_id;
    }

    public function getMeta($item)
    {
        $jatuh_tempo = strtotime($item['batas_waktu'] ?? $item['tanggal_kembali'] ?? '');
        $tgl_kembali = !empty($item['tanggal_kembali']) ? strtotime($item['tanggal_kembali']) : time();
        
        $terlambat = 0;
        if ($jatuh_tempo && $tgl_kembali > $jatuh_tempo) {
            $diff = $tgl_kembali - $jatuh_tempo;
            $terlambat = floor($diff / (60 * 60 * 24));
        }

        $status = 'Dipinjam';
        if (!empty($item['tanggal_kembali'])) {
            $status = ($terlambat > 0) ? 'Terlambat' : 'Selesai';
        } elseif ($terlambat > 0) {
            $status = 'Terlambat';
        }

        return [
            'status' => $status,
            'terlambat' => $terlambat > 0 ? $terlambat . ' hari' : '-',
            'denda' => 'Rp ' . number_format($terlambat * 500, 0, ',', '.')
        ];
    }

    public function reportRows($statusFilter, $startDate, $endDate, $keyword)
    {
        // Format Laporan: Nama Peminjam, ID Eksemplar, Tgl Pinjam, Denda, Status
        $sql = "SELECT dp.*, p.tanggal_peminjaman, p.batas_waktu, a.nama as peminjam, e.id_eksemplar, b.judul
                FROM detail_peminjaman dp
                JOIN peminjaman p ON p.id_peminjaman = dp.id_peminjaman
                JOIN anggota a ON a.id_anggota = p.id_anggota
                JOIN eksemplar e ON e.id_eksemplar = dp.id_eksemplar
                JOIN buku b ON b.id_buku = e.id_buku
                WHERE p.laporan_hidden_at IS NULL";
        
        if ($startDate) $sql .= " AND p.tanggal_peminjaman >= '$startDate'";
        if ($endDate) $sql .= " AND p.tanggal_peminjaman <= '$endDate'";
        if ($keyword) $sql .= " AND (a.nama LIKE '%$keyword%' OR b.judul LIKE '%$keyword%')";
        
        $sql .= " ORDER BY dp.id_detail DESC";
        $result = $this->conn->query($sql);
        
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $meta = $this->getMeta($row);
            $row['status'] = $meta['status'];
            $row['denda'] = $meta['denda'];
            
            if ($statusFilter !== 'Semua' && $row['status'] !== $statusFilter) continue;
            
            $rows[] = $row;
        }
        return $rows;
    }

    public function hideReports($ids)
    {
        if (empty($ids)) return;
        $idList = implode(',', $ids);
        $this->conn->query("UPDATE peminjaman SET laporan_hidden_at = NOW() WHERE id_peminjaman IN ($idList)");
    }
}
