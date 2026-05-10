<?php

require_once __DIR__ . '/../models/Kategori.php';

class KategoriController
{
    private $model;

    public function __construct()
    {
        $this->model = new Kategori();
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editKategori = $editId > 0 ? $this->model->find($editId) : null;

        $data = [
            'kategoriList' => $this->model->all(),
            'editKategori' => $editKategori,
            'errorsKategori' => $_SESSION['errors_kategori'] ?? [],
            'successKategori' => $_SESSION['success_kategori'] ?? '',
            'oldKategori' => $_SESSION['old_kategori'] ?? ['nama_kategori' => '']
        ];

        unset($_SESSION['errors_kategori'], $_SESSION['success_kategori'], $_SESSION['old_kategori']);
        return $data;
    }

    public function store($post)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $nama = trim($post['nama_kategori'] ?? '');
        if ($nama === '') {
            $_SESSION['errors_kategori'] = ['Nama kategori wajib diisi.'];
            $_SESSION['old_kategori'] = $post;
            header('Location: ../../index.php?menu=kategori');
            exit;
        }

        try {
            $this->model->create($nama);
            $_SESSION['success_kategori'] = 'Kategori berhasil ditambahkan.';
        } catch (Exception $e) {
            $_SESSION['errors_kategori'] = ['Kategori sudah ada atau gagal ditambahkan.'];
            $_SESSION['old_kategori'] = $post;
        }

        header('Location: ../../index.php?menu=kategori');
        exit;
    }

    public function update($post)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $id = (int) ($post['id'] ?? 0);
        $nama = trim($post['nama_kategori'] ?? '');
        if ($id < 1 || $nama === '') {
            $_SESSION['errors_kategori'] = ['Data kategori tidak lengkap.'];
            header('Location: ../../index.php?menu=kategori');
            exit;
        }

        try {
            $this->model->update($id, $nama);
            $_SESSION['success_kategori'] = 'Kategori berhasil diperbarui.';
        } catch (Exception $e) {
            $_SESSION['errors_kategori'] = ['Kategori gagal diperbarui. Nama mungkin sudah dipakai.'];
            header('Location: ../../index.php?menu=kategori&edit=' . $id);
            exit;
        }

        header('Location: ../../index.php?menu=kategori');
        exit;
    }

    public function delete($post)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $id = (int) ($post['id'] ?? 0);
        if ($id < 1) {
            $_SESSION['errors_kategori'] = ['Kategori tidak ditemukan.'];
            header('Location: ../../index.php?menu=kategori');
            exit;
        }

        try {
            $this->model->delete($id);
            $_SESSION['success_kategori'] = 'Kategori berhasil dihapus.';
        } catch (Exception $e) {
            $_SESSION['errors_kategori'] = ['Kategori tidak bisa dihapus karena masih dipakai oleh buku.'];
        }

        header('Location: ../../index.php?menu=kategori');
        exit;
    }
}

if (!function_exists('eKategori')) {
    function eKategori($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
