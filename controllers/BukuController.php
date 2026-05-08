<?php

require_once __DIR__ . '/../models/Buku.php';

class BukuController
{
    private $model;

    public function __construct()
    {
        $this->model = new Buku();
    }

    public function index()
    {
        $dataBuku = $this->model->all();
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        $kategoriFilter = isset($_GET['kategori']) ? $_GET['kategori'] : 'Semua';
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 7;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        // Filter data secara manual (sederhana)
        $filteredData = [];
        foreach ($dataBuku as $item) {
            $matchKategori = ($kategoriFilter == 'Semua' || $item['kategori'] == $kategoriFilter);
            $matchSearch = ($search == '' || 
                stripos($item['judul'], $search) !== false || 
                stripos($item['penulis'], $search) !== false
            );

            if ($matchKategori && $matchSearch) {
                $filteredData[] = $item;
            }
        }

        $totalData = count($filteredData);
        $totalPages = ceil($totalData / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        
        $offset = ($currentPage - 1) * $perPage;
        $pageData = array_slice($filteredData, $offset, $perPage);

        return [
            'dataBuku' => $dataBuku,
            'search' => $search,
            'kategoriFilter' => $kategoriFilter,
            'perPage' => $perPage,
            'kategoriOptions' => $this->model->kategoriOptions(),
            'filteredData' => $filteredData,
            'totalData' => $totalData,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'offset' => $offset,
            'pageData' => $pageData,
            'startDisplay' => ($totalData > 0) ? $offset + 1 : 0,
            'endDisplay' => min($offset + $perPage, $totalData)
        ];
    }

    public function formState()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $state = [
            'errorsTambahBuku' => isset($_SESSION['errors']) ? $_SESSION['errors'] : [],
            'oldTambahBuku' => isset($_SESSION['old']) ? $_SESSION['old'] : [
                'judul' => '', 'penulis' => '', 'penerbit' => '', 'tahun' => '', 
                'tempat_terbit' => '', 'isbn' => '', 'kategori' => '', 'sinopsis' => '', 'stok' => 1
            ],
            'kategoriList' => $this->model->kategoriOptions()
        ];

        unset($_SESSION['errors'], $_SESSION['old']);
        return $state;
    }

    public function store($post, $files)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $data = $post;
        $data['kategori'] = is_array($post['kategori']) ? $post['kategori'][0] : $post['kategori'];
        $data['cover_buku'] = $this->uploadFile($files);

        // Validasi sederhana
        if (empty($data['judul']) || empty($data['penulis'])) {
            $_SESSION['errors'] = ['Judul dan Penulis wajib diisi.'];
            $_SESSION['old'] = $post;
            header('Location: ../../index.php?menu=tambahbuku');
            exit;
        }

        $this->model->create($data);
        header('Location: ../../index.php?menu=databuku');
        exit;
    }

    public function edit($id)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $book = $this->model->find($id);
        if (!$book) {
            header('Location: ../../index.php?menu=databuku');
            exit;
        }

        return [
            'errorsEditBuku' => isset($_SESSION['errors']) ? $_SESSION['errors'] : [],
            'oldEditBuku' => isset($_SESSION['old']) ? $_SESSION['old'] : $book,
            'kategoriList' => $this->model->kategoriOptions(),
            'bookId' => $id
        ];
    }

    public function update($post, $files)
    {
        $id = $post['id'];
        $data = $post;
        $data['kategori'] = is_array($post['kategori']) ? $post['kategori'][0] : $post['kategori'];
        
        $newCover = $this->uploadFile($files);
        if ($newCover) {
            $data['cover_buku'] = $newCover;
        }

        $this->model->update($id, $data);
        header('Location: ../../index.php?menu=databuku');
        exit;
    }

    public function delete($post)
    {
        $id = $post['id'];
        $this->model->delete($id);
        header('Location: ../../index.php?menu=databuku');
        exit;
    }

    private function uploadFile($files)
    {
        if (!empty($files['cover']['name'])) {
            $namaFile = time() . '_' . $files['cover']['name'];
            $targetDir = __DIR__ . '/../public/img/covers/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            
            move_uploaded_file($files['cover']['tmp_name'], $targetDir . $namaFile);
            return 'public/img/covers/' . $namaFile;
        }
        return '';
    }

    public function perPageOptions()
    {
        return [5, 7, 10, 15, 20];
    }
}

// Helper functions for views
if (!function_exists('eBuku')) {
    function eBuku($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('getBukuPerPageOptions')) {
    function getBukuPerPageOptions() {
        return (new BukuController())->perPageOptions();
    }
}

if (!function_exists('buildBukuUrl')) {
    function buildBukuUrl($page, $search, $kategori, $perPage) {
        return "?menu=databuku&page=$page&q=$search&kategori=$kategori&per_page=$perPage";
    }
}
