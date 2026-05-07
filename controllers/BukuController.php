<?php

require_once __DIR__ . '/../models/Buku.php';

class BukuController
{
    private Buku $model;

    public function __construct(?Buku $model = null)
    {
        $this->model = $model ?: new Buku();
    }

    public function index(): array
    {
        $dataBuku = $this->model->all();
        $search = trim($_GET['q'] ?? '');
        $kategoriFilter = $_GET['kategori'] ?? 'Semua';
        $perPage = $this->normalizePerPage($_GET['per_page'] ?? 7);
        $kategoriOptions = $this->model->kategoriOptions();

        $filteredData = array_values(array_filter($dataBuku, function (array $item) use ($search, $kategoriFilter): bool {
            if ($kategoriFilter !== 'Semua' && ($item['kategori'] ?? '') !== $kategoriFilter) {
                return false;
            }

            if ($search === '') {
                return true;
            }

            return stripos((string) ($item['judul'] ?? ''), $search) !== false
                || stripos((string) ($item['penulis'] ?? ''), $search) !== false
                || stripos((string) ($item['penerbit'] ?? ''), $search) !== false
                || stripos((string) ($item['isbn'] ?? ''), $search) !== false;
        }));

        $totalData = count($filteredData);
        $totalPages = max(1, (int) ceil($totalData / $perPage));
        $currentPage = min(max(1, (int) ($_GET['page'] ?? 1)), $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        return [
            'dataBuku' => $dataBuku,
            'search' => $search,
            'kategoriFilter' => $kategoriFilter,
            'perPage' => $perPage,
            'kategoriOptions' => $kategoriOptions,
            'filteredData' => $filteredData,
            'totalData' => $totalData,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'offset' => $offset,
            'pageData' => array_slice($filteredData, $offset, $perPage),
            'startDisplay' => $totalData > 0 ? $offset + 1 : 0,
            'endDisplay' => $totalData > 0 ? min($offset + $perPage, $totalData) : 0,
        ];
    }

    public function formState(): array
    {
        $this->startSession();
        $default = [
            'judul' => '',
            'penulis' => '',
            'penerbit' => '',
            'tahun' => '',
            'tempat_terbit' => '',
            'isbn' => '',
            'kategori' => [],
            'sinopsis' => '',
            'stok' => 5,
        ];

        $state = [
            'errorsTambahBuku' => $_SESSION['tambah_buku_errors'] ?? [],
            'oldTambahBuku' => array_merge($default, $_SESSION['tambah_buku_old'] ?? []),
            'kategoriList' => $this->model->kategoriOptions(),
        ];

        unset($_SESSION['tambah_buku_errors'], $_SESSION['tambah_buku_old']);

        return $state;
    }

    public function edit(int $id): array
    {
        $this->startSession();
        $book = $this->model->find($id);

        if (!$book) {
            $this->redirect('?menu=databuku');
        }

        // Map model fields to form fields if necessary
        $bookData = [
            'id' => $book['id'],
            'judul' => $book['judul'],
            'penulis' => $book['penulis'],
            'penerbit' => $book['penerbit'],
            'tahun' => $book['tahun'],
            'tempat_terbit' => $book['tempat_terbit'] ?? '',
            'isbn' => $book['isbn'],
            'kategori' => [$book['kategori']],
            'sinopsis' => $book['sinopsis'] ?? '',
            'stok' => $book['stok'],
        ];

        $state = [
            'errorsEditBuku' => $_SESSION['edit_buku_errors'] ?? [],
            'oldEditBuku' => array_merge($bookData, $_SESSION['edit_buku_old'] ?? []),
            'kategoriList' => $this->model->kategoriOptions(),
            'bookId' => $id
        ];

        unset($_SESSION['edit_buku_errors'], $_SESSION['edit_buku_old']);

        return $state;
    }

    public function store(array $post, array $files = []): void
    {
        $this->startSession();
        $old = $this->normalizeInput($post);
        $errors = $this->validate($old);

        if ($old['judul'] !== '' && $this->model->countByTitle($old['judul']) > 0) {
            $errors[] = 'Judul buku sudah ada di database.';
        }

        if (!empty($errors)) {
            $_SESSION['tambah_buku_errors'] = $errors;
            $_SESSION['tambah_buku_old'] = $old;
            $this->redirect('?menu=tambahbuku');
        }

        $old['cover_buku'] = $this->handleCoverUpload($files);
        $this->model->create($old);
        unset($_SESSION['tambah_buku_errors'], $_SESSION['tambah_buku_old']);

        $this->redirect('?menu=databuku');
    }

    public function update(array $post, array $files = []): void
    {
        $this->startSession();
        $id = (int) ($post['id'] ?? 0);
        $data = $this->normalizeInput($post, false);
        $errors = $this->validate($data, false);

        if ($id <= 0) {
            $this->redirect('?menu=databuku');
        }

        if ($data['judul'] !== '') {
            $book = $this->model->find($id);
            if ($book && strtolower($book['judul']) !== strtolower($data['judul']) && $this->model->countByTitle($data['judul'], $id) > 0) {
                $errors[] = 'Judul buku sudah ada di database.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['edit_buku_errors'] = $errors;
            $_SESSION['edit_buku_old'] = $post;
            $this->redirect('?menu=editbuku&id=' . $id);
        }

        $newCover = $this->handleCoverUpload($files);
        if ($newCover !== '') {
            $data['cover_buku'] = $newCover;
        } else {
            // Jika tidak ada upload cover baru, jangan timpa data cover yang sudah ada.
            unset($data['cover_buku']);
        }

        $this->model->update($id, $data);
        unset($_SESSION['edit_buku_errors'], $_SESSION['edit_buku_old']);

        $this->redirect($this->buildUrl(
            max(1, (int) ($post['page'] ?? 1)),
            trim($post['q'] ?? ''),
            $post['kategori_filter'] ?? 'Semua',
            $this->normalizePerPage($post['per_page'] ?? 7)
        ));
    }

    public function delete(array $post): void
    {
        $id = (int) ($post['id'] ?? 0);

        if ($id > 0) {
            try {
                $this->model->delete($id);
            } catch (Throwable $e) {
                // Buku yang sudah punya riwayat peminjaman tidak dihapus oleh database.
            }
        }

        $this->redirect($this->buildUrl(
            max(1, (int) ($post['page'] ?? 1)),
            trim($post['q'] ?? ''),
            $post['kategori_filter'] ?? 'Semua',
            $this->normalizePerPage($post['per_page'] ?? 7)
        ));
    }

    public function perPageOptions(): array
    {
        return [5, 7, 10, 15, 20];
    }

    public function normalizePerPage($value, int $default = 7): int
    {
        $value = (int) $value;
        return in_array($value, $this->perPageOptions(), true) ? $value : $default;
    }

    public function buildUrl(int $page, string $search, string $kategori, int $perPage): string
    {
        $params = ['menu' => 'databuku', 'page' => $page, 'per_page' => $perPage];

        if ($search !== '') {
            $params['q'] = $search;
        }

        if ($kategori !== 'Semua') {
            $params['kategori'] = $kategori;
        }

        return '?' . http_build_query($params);
    }

    private function normalizeInput(array $post, bool $fromCreate = true): array
    {
        $kategori = $post['kategori'] ?? [];
        $kategori = is_array($kategori) ? ($kategori[0] ?? '') : $kategori;

        return [
            'judul' => trim($post['judul'] ?? ''),
            'penulis' => trim($post['penulis'] ?? ''),
            'penerbit' => trim($post['penerbit'] ?? ''),
            'tahun' => trim($post['tahun'] ?? ''),
            'tempat_terbit' => trim($post['tempat_terbit'] ?? ''),
            'isbn' => trim($post['isbn'] ?? ''),
            'kategori' => trim((string) $kategori),
            'sinopsis' => trim($post['sinopsis'] ?? ''),
            'stok' => max(0, (int) ($post['stok'] ?? 0)),
            'cover_buku' => '',
        ];
    }

    private function validate(array $data, bool $requireKategori = true): array
    {
        $errors = [];

        if ($data['judul'] === '') {
            $errors[] = 'Judul buku wajib diisi.';
        }

        if ($data['penulis'] === '') {
            $errors[] = 'Penulis wajib diisi.';
        }

        if ($data['penerbit'] === '') {
            $errors[] = 'Penerbit wajib diisi.';
        }

        if ($data['tahun'] === '' || !ctype_digit((string) $data['tahun'])) {
            $errors[] = 'Tahun terbit wajib berupa angka.';
        }

        if ($requireKategori && $data['kategori'] === '') {
            $errors[] = 'Pilih minimal satu kategori.';
        }

        if ($data['stok'] < 1) {
            $errors[] = 'Stok buku minimal 1.';
        }

        return $errors;
    }

    private function handleCoverUpload(array $files): string
    {
        if (empty($files['cover']['tmp_name']) || !is_uploaded_file($files['cover']['tmp_name'])) {
            return '';
        }

        $extension = pathinfo($files['cover']['name'] ?? '', PATHINFO_EXTENSION);
        $extension = $extension !== '' ? strtolower($extension) : 'jpg';
        $fileName = 'cover_' . uniqid('', true) . '.' . $extension;
        $relativePath = 'public/img/covers/' . $fileName;
        $targetDir = __DIR__ . '/../public/img/covers';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        move_uploaded_file($files['cover']['tmp_name'], $targetDir . '/' . $fileName);

        return $relativePath;
    }

    private function redirect(string $query): void
    {
        header('Location: ../../index.php' . $query);
        exit;
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }
}

if (!function_exists('eBuku')) {
    function eBuku($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('getBukuPerPageOptions')) {
    function getBukuPerPageOptions(): array
    {
        return (new BukuController())->perPageOptions();
    }
}

if (!function_exists('buildBukuUrl')) {
    function buildBukuUrl(int $page, string $search, string $kategori, int $perPage): string
    {
        return (new BukuController())->buildUrl($page, $search, $kategori, $perPage);
    }
}
