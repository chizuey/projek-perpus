<?php

function updateBukuDataFile(): string
{
    return __DIR__ . '/../data_buku.json';
}

function loadUpdateBukuData(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }

    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function saveUpdateBukuData(string $file, array $data): void
{
    file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function normalizeUpdateBukuPerPage($value, int $default = 7): int
{
    $value = (int) $value;
    return in_array($value, [5, 7, 10, 15, 20], true) ? $value : $default;
}

function buildUpdateBukuUrl(int $page, string $search, string $kategori, int $perPage): string
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

function redirectUpdateBuku(string $query): void
{
    header('Location: ../../index.php' . $query);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['action'] ?? '') !== 'edit_buku') {
    redirectUpdateBuku('?menu=databuku');
}

$dataFile = updateBukuDataFile();
$dataBuku = loadUpdateBukuData($dataFile);
$perPage = normalizeUpdateBukuPerPage($_POST['per_page'] ?? 7);
$redirectUrl = buildUpdateBukuUrl(
    max(1, (int) ($_POST['page'] ?? 1)),
    trim($_POST['q'] ?? ''),
    $_POST['kategori_filter'] ?? 'Semua',
    $perPage
);

$id = (int) ($_POST['id'] ?? 0);

foreach ($dataBuku as $index => $item) {
    if ((int) ($item['id'] ?? 0) !== $id) {
        continue;
    }

    $dataBuku[$index] = array_merge($item, [
        'id' => $id,
        'judul' => trim($_POST['judul'] ?? ''),
        'penulis' => trim($_POST['penulis'] ?? ''),
        'penerbit' => trim($_POST['penerbit'] ?? ''),
        'tahun' => (int) ($_POST['tahun'] ?? 0),
        'kategori' => trim($_POST['kategori'] ?? ''),
        'stok' => max(0, (int) ($_POST['stok'] ?? 0)),
    ]);
    saveUpdateBukuData($dataFile, $dataBuku);
    break;
}

redirectUpdateBuku($redirectUrl);
