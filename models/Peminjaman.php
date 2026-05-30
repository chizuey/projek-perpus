<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Denda.php';

class Peminjaman
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public static function updateOverdueStatuses()
    {
        // Status peminjaman dihitung dari detail_peminjaman, bukan disimpan di header.
        return;
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
        
        $id_eksemplar_array = array_filter($id_eksemplar_array);
        $new_count = count($id_eksemplar_array);
        
        if ($new_count === 0) {
            throw new Exception("Tidak ada buku dipilih.");
        }
        
        if ($new_count !== count(array_unique($id_eksemplar_array))) {
            throw new Exception("Buku dipilih ganda.");
        }
        
        // Cek pinjaman terlambat
        // $today = date('Y-m-d');
        // $res_terlambat = $this->conn->query("SELECT COUNT(*) as terlambat_count 
        //                                      FROM detail_peminjaman dp 
        //                                      JOIN peminjaman p ON p.id_peminjaman = dp.id_peminjaman 
        //                                      WHERE p.id_anggota = $id_anggota 
        //                                      AND dp.status_pengembalian = 'dipinjam' 
        //                                      AND p.batas_waktu < '$today'");
        // if ($res_terlambat && $res_terlambat->fetch_assoc()['terlambat_count'] > 0) {
        //     throw new Exception("Masih memiliki pinjaman terlambat.");
        // }
        
        // Cek jumlah buku yang sedang dipinjam
        $res_count = $this->conn->query("SELECT COUNT(*) as active_count 
                                        FROM detail_peminjaman dp 
                                        JOIN peminjaman p ON p.id_peminjaman = dp.id_peminjaman 
                                        WHERE p.id_anggota = $id_anggota AND dp.status_pengembalian = 'dipinjam'");
        $active_count = $res_count->fetch_assoc()['active_count'];
        
        // Cek jumlah buku yang sedang direservasi
        $res_reservasi = $this->conn->query("SELECT COUNT(*) as res_count FROM reservasi WHERE id_anggota = $id_anggota AND status IN ('menunggu', 'disetujui')");
        $reservasi_count = $res_reservasi->fetch_assoc()['res_count'];
        
        if (($active_count + $reservasi_count + $new_count) > 3) {
            throw new Exception("Batas maksimal peminjaman dan reservasi gabungan adalah 3 buku. Saat ini meminjam $active_count buku dan $reservasi_count reservasi aktif.");
        }

        $tgl_pinjam = date('Y-m-d');
        $tgl_jatuh_tempo = date('Y-m-d', strtotime('+7 days'));

        $this->conn->begin_transaction();

        try {
            $sql_header = "INSERT INTO peminjaman (id_anggota, id_admin, tanggal_peminjaman, batas_waktu) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql_header);
            if (!$stmt) throw new Exception("Gagal insert/update database.");
            $stmt->bind_param("iiss", $id_anggota, $adminId, $tgl_pinjam, $tgl_jatuh_tempo);
            if (!$stmt->execute()) {
                throw new Exception("Gagal insert/update database.");
            }
            $id_peminjaman = $this->conn->insert_id;

            $count = 0;
            foreach (array_unique($id_eksemplar_array) as $id_eksemplar) {
                if (empty($id_eksemplar)) continue;

                $res_eks = $this->conn->query("SELECT id_eksemplar, id_buku FROM eksemplar WHERE id_eksemplar = " . (int)$id_eksemplar . " AND status = 'tersedia'");
                if ($res_eks && $res_eks->num_rows > 0) {
                    $eksemplar = $res_eks->fetch_assoc();

                    $sql_detail = "INSERT INTO detail_peminjaman (id_peminjaman, id_eksemplar, status_pengembalian) VALUES (?, ?, 'dipinjam')";
                    $stmt_detail = $this->conn->prepare($sql_detail);
                    if (!$stmt_detail) throw new Exception("Gagal insert/update database.");
                    $stmt_detail->bind_param("ii", $id_peminjaman, $id_eksemplar);
                    if (!$stmt_detail->execute()) {
                        throw new Exception("Gagal insert/update database.");
                    }

                    if (!$this->conn->query("UPDATE eksemplar SET status = 'dipinjam' WHERE id_eksemplar = " . (int)$id_eksemplar)) {
                        throw new Exception("Gagal insert/update database.");
                    }
                    $this->syncStokBuku((int)$eksemplar['id_buku']);
                    $count++;
                } else {
                    throw new Exception("Buku tidak tersedia.");
                }
            }

            if ($count === 0) throw new Exception("Tidak ada buku yang berhasil dipinjam.");

            if (!$this->conn->commit()) {
                throw new Exception("Gagal insert/update database.");
            }
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            // Rethrow exception to be caught by controller
            throw $e;
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
        $res = $this->conn->query("SELECT dp.id_eksemplar, dp.id_peminjaman, p.batas_waktu, e.id_buku
                                  FROM detail_peminjaman dp
                                  JOIN peminjaman p ON p.id_peminjaman = dp.id_peminjaman
                                  JOIN eksemplar e ON e.id_eksemplar = dp.id_eksemplar
                                  WHERE dp.id_detail = $idDetail");
        if ($row = $res->fetch_assoc()) {
            $id_eksemplar = $row['id_eksemplar'];
            $id_buku = $row['id_buku'];
            $today = date('Y-m-d');
            $hariTerlambat = 0;

            if (!empty($row['batas_waktu']) && strtotime($today) > strtotime($row['batas_waktu'])) {
                $selisih = strtotime($today) - strtotime($row['batas_waktu']);
                $hariTerlambat = (int) floor($selisih / (60 * 60 * 24));
            }
            
            $sql = "UPDATE detail_peminjaman SET tanggal_kembali = ?, status_pengembalian = 'kembali' WHERE id_detail = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $today, $idDetail);
            $stmt->execute();

            if ($hariTerlambat > 0) {
                (new Denda())->save($idDetail, $hariTerlambat);
            }
            
            $this->conn->query("UPDATE eksemplar SET status = 'tersedia' WHERE id_eksemplar = $id_eksemplar");
            $this->syncStokBuku((int)$id_buku);
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
        $nama = $this->conn->real_escape_string($nama);
        $res = $this->conn->query("SELECT id_anggota FROM anggota WHERE nim = '$nim' AND nama_anggota = '$nama'");
        if ($row = $res->fetch_assoc()) {
            return $row['id_anggota'];
        }
        
        throw new Exception("Anggota tidak ditemukan.");
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
        
        if ($startDate) {
            $startDate = $this->conn->real_escape_string($startDate);
            $sql .= " AND p.tanggal_peminjaman >= '$startDate'";
        }
        if ($endDate) {
            $endDate = $this->conn->real_escape_string($endDate);
            $sql .= " AND p.tanggal_peminjaman <= '$endDate'";
        }
        if ($keyword) {
            $keyword = $this->conn->real_escape_string($keyword);
            $sql .= " AND (a.nama LIKE '%$keyword%' OR b.judul LIKE '%$keyword%')";
        }
        
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

    private function syncStokBuku($idBuku)
    {
        $idBuku = (int)$idBuku;
        $this->conn->query("UPDATE buku
                            SET copy = (SELECT COUNT(*) FROM eksemplar WHERE id_buku = $idBuku),
                                stok_tersedia = (SELECT COUNT(*) FROM eksemplar WHERE id_buku = $idBuku AND status = 'tersedia')
                            WHERE id_buku = $idBuku");
    }
}
