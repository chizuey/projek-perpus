<?php

class Reservasi
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    private function fetchAssocFromStmt(mysqli_stmt $stmt): ?array
    {
        $meta = $stmt->result_metadata();
        if (!$meta) return null;

        $row = [];
        $bind = [];
        while ($field = $meta->fetch_field()) {
            $row[$field->name] = null;
            $bind[] = &$row[$field->name];
        }

        call_user_func_array([$stmt, 'bind_result'], $bind);
        if (!$stmt->fetch()) return null;

        return array_map(fn($value) => $value, $row);
    }

    private function fetchAllFromStmt(mysqli_stmt $stmt): array
    {
        $rows = [];
        $meta = $stmt->result_metadata();
        if (!$meta) return $rows;

        $row = [];
        $bind = [];
        while ($field = $meta->fetch_field()) {
            $row[$field->name] = null;
            $bind[] = &$row[$field->name];
        }

        call_user_func_array([$stmt, 'bind_result'], $bind);
        while ($stmt->fetch()) {
            $rows[] = array_map(fn($value) => $value, $row);
        }
        return $rows;
    }

    // =========================================================================
    // READ
    // =========================================================================

    /**
     * Ambil semua reservasi dengan JOIN ke anggota, buku, admin.
     */
    public function getAll(string $search = '', string $filterStatus = ''): array
    {
        $sql = 'SELECT
                    r.id_reservasi,
                    r.tanggal_reservasi,
                    r.status,
                   e.id_eksemplar,
                    a.id_anggota,
                    a.nim,
                    a.nama as nama_anggota,
                    b.id_buku,
                    b.judul AS judul_buku,
                    b.stok_tersedia,
                    e.status AS status_eksemplar,
                    adm.nama as nama_admin
                FROM reservasi r
                JOIN anggota a ON r.id_anggota = a.id_anggota
                JOIN buku    b ON r.id_buku    = b.id_buku
              LEFT JOIN eksemplar e ON b.id_buku = e.id_buku -- Hubungkan eksemplar lewat tabel buku (b), bukan r
              LEFT JOIN admin adm ON r.id_admin = adm.id_admin
              WHERE 1=1';
        $params = [];
        $types  = '';

        if ($filterStatus !== '') {
            $sql    .= ' AND r.status = ?';
            $params[] = $filterStatus;
            $types  .= 's';
        }

        if ($search !== '') {
            $like     = '%' . $search . '%';
            $sql     .= ' AND (a.nim LIKE ? OR a.nama LIKE ? OR b.judul LIKE ?)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $types   .= 'sss';
        }

        $sql .= ' ORDER BY r.created_at DESC';

        $stmt = $this->conn->prepare($sql);

        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $data = [];
        foreach ($this->fetchAllFromStmt($stmt) as $row) {
            $row['kode_anggota'] = $row['nim']; // Untuk kompatibilitas view lama
            $data[] = $row;
        }
        return $data;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT r.*, a.nim, a.nama as nama_anggota, b.judul AS judul_buku, b.stok_tersedia
             FROM reservasi r
             JOIN anggota a ON r.id_anggota = a.id_anggota
             JOIN buku    b ON r.id_buku    = b.id_buku
             WHERE r.id_reservasi = ? LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $this->fetchAssocFromStmt($stmt);
        if ($row) {
            $row['kode_anggota'] = $row['nim'];
        }
        return $row ?: null;
    }

    // =========================================================================
    // UPDATE STATUS
    // =========================================================================

    /**
     * Konfirmasi reservasi — status: menunggu → disetujui, isi id_admin.
     */
    public function konfirmasi(int $id, int $idAdmin): bool
    {
        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare(
                'SELECT id_reservasi, id_buku
                 FROM reservasi
                 WHERE id_reservasi = ? AND status = "menunggu"
                 LIMIT 1 FOR UPDATE'
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $reservasi = $this->fetchAssocFromStmt($stmt);

            if (!$reservasi) {
                throw new Exception('Reservasi tidak ditemukan atau sudah diproses.');
            }

            $idBuku = (int) $reservasi['id_buku'];
            $stmt = $this->conn->prepare(
                'SELECT id_eksemplar
                 FROM eksemplar
                 WHERE id_buku = ? AND status = "tersedia"
                 ORDER BY id_eksemplar ASC
                 LIMIT 1 FOR UPDATE'
            );
            $stmt->bind_param('i', $idBuku);
            $stmt->execute();
            $eksemplar = $this->fetchAssocFromStmt($stmt);

            if (!$eksemplar) {
                throw new Exception('Stok tersedia habis. Reservasi belum bisa dikonfirmasi.');
            }

            $idEksemplar = (int) $eksemplar['id_eksemplar'];

            $stmt = $this->conn->prepare('UPDATE eksemplar SET status = "direservasi" WHERE id_eksemplar = ?');
            $stmt->bind_param('i', $idEksemplar);
            $stmt->execute();

            $stmt = $this->conn->prepare(
                'UPDATE reservasi
                 SET status = "disetujui", id_admin = ?, id_eksemplar = ?
                 WHERE id_reservasi = ? AND status = "menunggu"'
            );
            $stmt->bind_param('iii', $idAdmin, $idEksemplar, $id);
            $stmt->execute();

            $this->syncStokBuku($idBuku);
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Batalkan reservasi — status: menunggu/disetujui → dibatalkan.
     */
    public function batalkan(int $id): bool
    {
        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare(
                'SELECT id_reservasi, id_buku, id_eksemplar, status
                 FROM reservasi
                 WHERE id_reservasi = ? AND status IN ("menunggu", "disetujui")
                 LIMIT 1 FOR UPDATE'
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $reservasi = $this->fetchAssocFromStmt($stmt);

            if (!$reservasi) {
                throw new Exception('Reservasi tidak ditemukan atau sudah selesai.');
            }

            $idBuku = (int) $reservasi['id_buku'];
            $idEksemplar = (int) ($reservasi['id_eksemplar'] ?? 0);

            if (($reservasi['status'] ?? '') === 'disetujui' && $idEksemplar > 0) {
                $stmt = $this->conn->prepare(
                    'UPDATE eksemplar
                     SET status = "tersedia"
                     WHERE id_eksemplar = ? AND status = "direservasi"'
                );
                $stmt->bind_param('i', $idEksemplar);
                $stmt->execute();
            }

            $stmt = $this->conn->prepare(
                'UPDATE reservasi
                 SET status = "dibatalkan"
                 WHERE id_reservasi = ? AND status IN ("menunggu", "disetujui")'
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();

            $this->syncStokBuku($idBuku);
            $this->conn->commit();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Selesaikan reservasi — status: disetujui → selesai (saat buku diambil).
     */
    public function selesaikan(int $id): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE reservasi
             SET status = "selesai"
             WHERE id_reservasi = ? AND status = "disetujui"'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function prosesPeminjaman(int $id, int $idAdmin): bool
    {
        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare(
                'SELECT r.id_reservasi, r.id_anggota, r.id_buku, r.id_eksemplar, e.status AS status_eksemplar
                 FROM reservasi r
                 JOIN eksemplar e ON r.id_eksemplar = e.id_eksemplar
                 WHERE r.id_reservasi = ? AND r.status = "disetujui"
                 LIMIT 1 FOR UPDATE'
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $reservasi = $this->fetchAssocFromStmt($stmt);

            if (!$reservasi) {
                throw new Exception('Reservasi belum disetujui atau data eksemplar tidak ditemukan.');
            }

            if (($reservasi['status_eksemplar'] ?? '') !== 'direservasi') {
                throw new Exception('Eksemplar reservasi tidak dalam status direservasi.');
            }

            $idAnggota = (int) $reservasi['id_anggota'];
            $idBuku = (int) $reservasi['id_buku'];
            $idEksemplar = (int) $reservasi['id_eksemplar'];
            $tanggalPinjam = date('Y-m-d');
            $batasWaktu = date('Y-m-d', strtotime('+7 days'));

            $stmt = $this->conn->prepare(
                'SELECT COUNT(*) AS active_count
                 FROM detail_peminjaman dp
                 JOIN peminjaman p ON p.id_peminjaman = dp.id_peminjaman
                 WHERE p.id_anggota = ? AND dp.status_pengembalian = "dipinjam"'
            );
            $stmt->bind_param('i', $idAnggota);
            $stmt->execute();
            $activeRow = $this->fetchAssocFromStmt($stmt);
            $activeCount = (int) ($activeRow['active_count'] ?? 0);

            if ($activeCount >= 3) {
                throw new Exception('Batas maksimal peminjaman adalah 3 buku.');
            }

            $stmt = $this->conn->prepare(
                'INSERT INTO peminjaman (id_anggota, id_admin, tanggal_peminjaman, batas_waktu)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->bind_param('iiss', $idAnggota, $idAdmin, $tanggalPinjam, $batasWaktu);
            $stmt->execute();
            $idPeminjaman = $this->conn->insert_id;

            $stmt = $this->conn->prepare(
                'INSERT INTO detail_peminjaman (id_peminjaman, id_eksemplar, status_pengembalian)
                 VALUES (?, ?, "dipinjam")'
            );
            $stmt->bind_param('ii', $idPeminjaman, $idEksemplar);
            $stmt->execute();

            $stmt = $this->conn->prepare('UPDATE eksemplar SET status = "dipinjam" WHERE id_eksemplar = ? AND status = "direservasi"');
            $stmt->bind_param('i', $idEksemplar);
            $stmt->execute();

            $stmt = $this->conn->prepare(
                'UPDATE reservasi
                 SET status = "selesai", id_admin = ?
                 WHERE id_reservasi = ? AND status = "disetujui"'
            );
            $stmt->bind_param('ii', $idAdmin, $id);
            $stmt->execute();

            $this->syncStokBuku($idBuku);
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // =========================================================================
    // CREATE RESERVASI (USER)
    // =========================================================================

    /**
     * User membuat reservasi buku baru — status otomatis "menunggu"
     */
    public function create(int $idAnggota, int $idBuku): bool
    {
        $idAnggota = (int) $idAnggota;
        $idBuku = (int) $idBuku;

        $result = $this->conn->query(
            "SELECT COUNT(*) AS stok_tersedia
             FROM eksemplar
             WHERE id_buku = $idBuku AND status = 'tersedia'"
        );
        $buku = $result->fetch_assoc();

        if (!$buku || (int) $buku['stok_tersedia'] <= 0) {
            throw new Exception('Stok buku tidak tersedia.');
        }

        // Cek jumlah buku yang sedang dipinjam
        $result = $this->conn->query("SELECT COUNT(*) as active_count FROM detail_peminjaman dp JOIN peminjaman p ON p.id_peminjaman = dp.id_peminjaman WHERE p.id_anggota = $idAnggota AND dp.status_pengembalian = 'dipinjam'");
        $active_count = (int)($result->fetch_assoc()['active_count'] ?? 0);

        // Cek jumlah buku yang sedang direservasi
        $result = $this->conn->query("SELECT COUNT(*) as res_count FROM reservasi WHERE id_anggota = $idAnggota AND status IN ('menunggu', 'disetujui')");
        $reservasi_count = (int)($result->fetch_assoc()['res_count'] ?? 0);

        $total_buku = $active_count + $reservasi_count + 1;
        if ($total_buku > 3) {
            throw new Exception("Batas maksimal peminjaman dan reservasi adalah 3 buku. Saat ini Anda memiliki $active_count pinjaman aktif dan $reservasi_count reservasi aktif.");
        }

        // Cek apakah sudah ada reservasi aktif untuk buku yang sama
        $result = $this->conn->query(
            "SELECT id_reservasi FROM reservasi
             WHERE id_anggota = $idAnggota AND id_buku = $idBuku AND status IN ('menunggu', 'disetujui')"
        );
        if ($result->fetch_assoc()) {
            return false; // Sudah ada reservasi aktif
        }

        $today = date('Y-m-d');
        $today = $this->conn->real_escape_string($today);
        return $this->conn->query(
            "INSERT INTO reservasi (id_anggota, id_buku, tanggal_reservasi, status)
             VALUES ($idAnggota, $idBuku, '$today', 'menunggu')"
        );
    }

    private function syncStokBuku(int $idBuku): void
    {
        $stmt = $this->conn->prepare(
            'UPDATE buku
             SET copy = (SELECT COUNT(*) FROM eksemplar WHERE id_buku = ?),
                 stok_tersedia = (SELECT COUNT(*) FROM eksemplar WHERE id_buku = ? AND status = "tersedia")
             WHERE id_buku = ?'
        );
        $stmt->bind_param('iii', $idBuku, $idBuku, $idBuku);
        $stmt->execute();
    }

    // =========================================================================
    // PAGINATION
    // =========================================================================

    public static function perPageOptions(): array { return [5, 10, 15, 20]; }

    public static function normalizePerPage($value, int $default = 10): int
    {
        $value = (int) $value;
        return in_array($value, self::perPageOptions(), true) ? $value : $default;
    }

    public static function paginationItems(int $currentPage, int $totalPages): array
    {
        $items = [];
        $last  = false;
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i === 1 || $i === $totalPages || abs($i - $currentPage) <= 1) {
                $items[] = $i;
                $last    = false;
            } elseif (!$last) {
                $items[] = '...';
                $last    = true;
            }
        }
        return $items;
    }
}
