<?php

class Reservasi
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
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
                    a.id_anggota,
                    a.nim,
                    a.nama as nama_anggota,
                    b.id_buku,
                    b.judul AS judul_buku,
                    adm.nama as nama_admin
                FROM reservasi r
                JOIN anggota a ON r.id_anggota = a.id_anggota
                JOIN buku    b ON r.id_buku    = b.id_buku
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
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $row['kode_anggota'] = $row['nim']; // Untuk kompatibilitas view lama
            $data[] = $row;
        }
        return $data;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT r.*, a.nim, a.nama as nama_anggota, b.judul AS judul_buku
             FROM reservasi r
             JOIN anggota a ON r.id_anggota = a.id_anggota
             JOIN buku    b ON r.id_buku    = b.id_buku
             WHERE r.id_reservasi = ? LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
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
        $stmt = $this->conn->prepare(
            'UPDATE reservasi
             SET status = "disetujui", id_admin = ?
             WHERE id_reservasi = ? AND status = "menunggu"'
        );
        $stmt->bind_param('ii', $idAdmin, $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /**
     * Batalkan reservasi — status: menunggu/disetujui → dibatalkan.
     */
    public function batalkan(int $id): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE reservasi
             SET status = "dibatalkan"
             WHERE id_reservasi = ? AND status IN ("menunggu", "disetujui")'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
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

    // =========================================================================
    // CREATE RESERVASI (USER)
    // =========================================================================

    /**
     * User membuat reservasi buku baru — status otomatis "menunggu"
     */
    public function create(int $idAnggota, int $idBuku): bool
    {
        // Cek apakah sudah ada reservasi aktif untuk buku yang sama
        $stmt = $this->conn->prepare(
            'SELECT id_reservasi FROM reservasi
             WHERE id_anggota = ? AND id_buku = ? AND status IN ("menunggu", "disetujui")'
        );
        $stmt->bind_param('ii', $idAnggota, $idBuku);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            return false; // Sudah ada reservasi aktif
        }

        $today = date('Y-m-d');
        $stmt = $this->conn->prepare(
            'INSERT INTO reservasi (id_anggota, id_buku, tanggal_reservasi, status)
             VALUES (?, ?, ?, "menunggu")'
        );
        $stmt->bind_param('iis', $idAnggota, $idBuku, $today);
        $stmt->execute();
        return $stmt->affected_rows > 0;
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
