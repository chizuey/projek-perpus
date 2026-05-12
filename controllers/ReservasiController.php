<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Reservasi.php';

class ReservasiController
{
    public const MENU = 'reservasi';

    private static function getConn(): mysqli
    {
        static $conn = null;
        if ($conn === null) {
            $db   = new Database();
            $conn = $db->getConnection();
        }
        return $conn;
    }

    private static function model(): Reservasi
    {
        static $model = null;
        if ($model === null) {
            $model = new Reservasi(self::getConn());
        }
        return $model;
    }

    private static function getIdAdmin(): int
    {
        self::startSession();
        return (int) ($_SESSION['id_admin'] ?? $_SESSION['id_user'] ?? 0);
    }

    // =========================================================================
    // INDEX — data untuk view reservasi
    // =========================================================================

    public static function index(): array
    {
        self::startSession();

        // Tandai reservasi kadaluarsa otomatis sudah tidak digunakan di skema baru
        // self::model()->expireKadaluarsa();

        $search       = trim($_GET['q'] ?? '');
        $filterStatus = trim($_GET['status'] ?? '');
        $perPage      = Reservasi::normalizePerPage($_GET['per_page'] ?? 10);

        $dataReservasi = self::model()->getAll($search, $filterStatus);

        $totalData   = count($dataReservasi);
        $totalPages  = max(1, (int) ceil($totalData / $perPage));
        $currentPage = min(max(1, (int) ($_GET['page'] ?? 1)), $totalPages);
        $offset      = ($currentPage - 1) * $perPage;
        $flashMessage = $_SESSION['reservasi_flash_message'] ?? '';
        $flashType = $_SESSION['reservasi_flash_type'] ?? '';

        unset($_SESSION['reservasi_flash_message'], $_SESSION['reservasi_flash_type']);

        return [
            'dataReservasi'   => $dataReservasi,
            'pageData'        => array_slice($dataReservasi, $offset, $perPage),
            'flashMessage'    => $flashMessage,
            'flashType'       => $flashType,
            'search'          => $search,
            'filterStatus'    => $filterStatus,
            'perPage'         => $perPage,
            'totalData'       => $totalData,
            'totalPages'      => $totalPages,
            'currentPage'     => $currentPage,
            'offset'          => $offset,
            'startDisplay'    => $totalData > 0 ? $offset + 1 : 0,
            'endDisplay'      => $totalData > 0 ? min($offset + $perPage, $totalData) : 0,
            'paginationItems' => Reservasi::paginationItems($currentPage, $totalPages),
        ];
    }

    // =========================================================================
    // KONFIRMASI
    // =========================================================================

    public static function konfirmasi(array $post): void
    {
        self::startSession();
        $id = (int) ($post['id'] ?? 0);
        try {
            self::model()->konfirmasi($id, self::getIdAdmin());
            self::setFlash('Reservasi berhasil dikonfirmasi dan eksemplar dikunci.', 'success');
        } catch (Exception $e) {
            self::setFlash($e->getMessage(), 'error');
        }
        self::redirectBackToList($post);
    }

    // =========================================================================
    // BATALKAN
    // =========================================================================

    public static function batalkan(array $post): void
    {
        self::startSession();
        $id = (int) ($post['id'] ?? 0);
        try {
            self::model()->batalkan($id);
            self::setFlash('Reservasi berhasil dibatalkan.', 'success');
        } catch (Exception $e) {
            self::setFlash($e->getMessage(), 'error');
        }
        self::redirectBackToList($post);
    }

    // =========================================================================
    // PROSES PEMINJAMAN
    // =========================================================================

    public static function prosesPeminjaman(array $post): void
    {
        self::startSession();
        $id = (int) ($post['id'] ?? 0);
        try {
            self::model()->prosesPeminjaman($id, self::getIdAdmin());
            self::setFlash('Reservasi berhasil diproses menjadi peminjaman.', 'success');
        } catch (Exception $e) {
            self::setFlash($e->getMessage(), 'error');
        }
        self::redirectBackToList($post);
    }

    // =========================================================================
    // CREATE (USER)
    // =========================================================================

    public static function create(array $post): array
    {
        self::startSession();
        
        if (empty($_SESSION['id_anggota']) && !empty($_SESSION['id_user'])) {
            $_SESSION['id_anggota'] = $_SESSION['id_user'];
        }

        if (empty($_SESSION['id_anggota'])) {
            return ['success' => false, 'message' => 'Anda harus login terlebih dahulu'];
        }

        $idAnggota = (int) $_SESSION['id_anggota'];
        $idBuku = (int) ($post['id_buku'] ?? 0);

        if ($idBuku <= 0) {
            return ['success' => false, 'message' => 'ID buku tidak valid'];
        }

        try {
            $success = self::model()->create($idAnggota, $idBuku);
            if ($success) {
                return ['success' => true, 'message' => 'Reservasi berhasil dibuat'];
            } else {
                return ['success' => false, 'message' => 'Anda sudah memiliki reservasi aktif untuk buku ini'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================================
    // Redirect helpers
    // =========================================================================

    private static function redirectBackToList(array $post): void
    {
        $params = [
            'page'     => max(1, (int) ($post['page'] ?? 1)),
            'per_page' => Reservasi::normalizePerPage($post['per_page'] ?? 10),
        ];
        if (($s = trim($post['q'] ?? '')) !== '')       $params['q']      = $s;
        if (($f = trim($post['status'] ?? '')) !== '')  $params['status'] = $f;
        self::redirectTo($params);
    }

    private static function setFlash(string $message, string $type): void
    {
        $_SESSION['reservasi_flash_message'] = $message;
        $_SESSION['reservasi_flash_type'] = $type;
    }

    public static function redirectTo(array $params = []): void
    {
        $params      = array_merge(['menu' => self::MENU], $params);
        $projectRoot = str_replace('\\', '/', dirname(__DIR__));
        $docRoot     = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        $basePath    = rtrim(str_replace($docRoot, '', $projectRoot), '/');
        header('Location: ' . $basePath . '/admin/?' . http_build_query($params));
        exit;
    }

    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();
    }
}
