<?php

require_once __DIR__ . '/../models/Peminjaman.php';

class PeminjamanController
{
    private $model;

    public function __construct()
    {
        $this->model = new Peminjaman();
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 7;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $dataPeminjaman = $this->model->all($search);
        
        $totalData = count($dataPeminjaman);
        $totalPages = ceil($totalData / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        
        $offset = ($currentPage - 1) * $perPage;
        $pageData = array_slice($dataPeminjaman, $offset, $perPage);

        return [
            'openPopup' => isset($_SESSION['open_popup']),
            'errors' => isset($_SESSION['errors']) ? $_SESSION['errors'] : [],
            'oldInput' => isset($_SESSION['old']) ? $_SESSION['old'] : ['nim' => '', 'nama' => '', 'buku' => ''],
            'search' => $search,
            'perPage' => $perPage,
            'totalData' => $totalData,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'offset' => $offset,
            'pageData' => $pageData,
            'startDisplay' => ($totalData > 0) ? $offset + 1 : 0,
            'endDisplay' => min($offset + $perPage, $totalData),
            'paginationItems' => $this->getPaginationItems($currentPage, $totalPages),
            'opsiBuku' => $this->model->getOpsiBuku()
        ];
    }

    public function store($post)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $nim = trim($post['nim']);
        $nama = trim($post['nama']);
        $buku = trim($post['buku']);
        $adminId = 1; // Default admin

        if (!$this->model->create($nim, $nama, $buku, $adminId)) {
            $_SESSION['errors'] = ['Gagal simpan data. Pastikan buku dipilih dan stok ada.'];
            $_SESSION['old'] = $post;
            $_SESSION['open_popup'] = true;
        }

        header('Location: ../../index.php?menu=peminjaman');
        exit;
    }

    public function extend($post)
    {
        $this->model->extend($post['id']);
        header('Location: ../../index.php?menu=peminjaman');
        exit;
    }

    public function returnBook($post)
    {
        $this->model->returnBook($post['id']);
        header('Location: ../../index.php?menu=peminjaman');
        exit;
    }

    private function getPaginationItems($current, $total)
    {
        $items = [];
        for ($i = 1; $i <= $total; $i++) {
            if ($i == 1 || $i == $total || abs($i - $current) <= 1) {
                $items[] = $i;
            } elseif (end($items) !== '...') {
                $items[] = '...';
            }
        }
        return $items;
    }
}

// Global helpers for views
if (!function_exists('e')) {
    function e($val) {
        return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('todayDate')) {
    function todayDate() {
        return date('Y-m-d');
    }
}

if (!function_exists('defaultTanggalKembali')) {
    function defaultTanggalKembali() {
        return date('Y-m-d', strtotime('+7 days'));
    }
}

if (!function_exists('formatTanggal')) {
    function formatTanggal($date) {
        return $date ? date('d M Y', strtotime($date)) : '-';
    }
}

if (!function_exists('hitungMetaPeminjaman')) {
    function hitungMetaPeminjaman($item) {
        return (new Peminjaman())->getMeta($item);
    }
}

if (!function_exists('canPerpanjangPeminjaman')) {
    function canPerpanjangPeminjaman($item) {
        return empty($item['tanggal_kembali']) && empty($item['extended_at']);
    }
}

if (!function_exists('tambahTujuhHariPeminjaman')) {
    function tambahTujuhHariPeminjaman($tanggal) {
        return date('Y-m-d', strtotime($tanggal . ' +7 days'));
    }
}

if (!function_exists('getPeminjamanPerPageOptions')) {
    function getPeminjamanPerPageOptions() {
        return [5, 7, 10, 15, 20];
    }
}

if (!function_exists('buildPageUrl')) {
    function buildPageUrl($page, $search, $perPage) {
        return "?menu=peminjaman&page=$page&q=$search&per_page=$perPage";
    }
}

if (!function_exists('getCurrentMenuPeminjaman')) {
    function getCurrentMenuPeminjaman() {
        return 'peminjaman';
    }
}
