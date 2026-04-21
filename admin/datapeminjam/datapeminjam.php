<?php
/*
|--------------------------------------------------------------------------
| KONFIGURASI AWAL
|--------------------------------------------------------------------------
*/
$dataFile = __DIR__ . '/data_peminjaman.json';
$openPopup = false;
$errors = [];
$oldInput = [
    'nim' => '',
    'nama' => '',
    'buku' => '',
    'tgl_pinjam' => '',
    'tgl_kembali' => ''
];

/*
|--------------------------------------------------------------------------
| HELPER ID DAN DATA AWAL
|--------------------------------------------------------------------------
*/
function createId()
{
    return uniqid('pjm_', true);
}

function seedPeminjaman()
{
    $today = new DateTimeImmutable('today');
    $rel = function ($modifier) use ($today) {
        return $today->modify($modifier)->format('Y-m-d');
    };

    return [
        [
            'id' => createId(),
            'nim' => '123456',
            'nama' => 'Fajar',
            'buku' => 'Pemrograman Web',
            'tanggal_pinjam' => $rel('-3 days'),
            'tanggal_kembali' => $rel('+6 days'),
            'returned_at' => null
        ],
        [
            'id' => createId(),
            'nim' => '123457',
            'nama' => 'Dina',
            'buku' => 'Basis Data',
            'tanggal_pinjam' => $rel('-6 days'),
            'tanggal_kembali' => $rel('+2 days'),
            'returned_at' => null
        ],
        [
            'id' => createId(),
            'nim' => '123458',
            'nama' => 'Budi',
            'buku' => 'Jaringan Komputer',
            'tanggal_pinjam' => $rel('-12 days'),
            'tanggal_kembali' => $rel('-2 days'),
            'returned_at' => null
        ],
        [
            'id' => createId(),
            'nim' => '123459',
            'nama' => 'Andi',
            'buku' => 'Sistem Informasi',
            'tanggal_pinjam' => $rel('-18 days'),
            'tanggal_kembali' => $rel('-10 days'),
            'returned_at' => $rel('-8 days')
        ],
        [
            'id' => createId(),
            'nim' => '123460',
            'nama' => 'Rani',
            'buku' => 'Pemrograman Java',
            'tanggal_pinjam' => $rel('-24 days'),
            'tanggal_kembali' => $rel('-16 days'),
            'returned_at' => $rel('-16 days')
        ],
        [
            'id' => createId(),
            'nim' => '123461',
            'nama' => 'Eko',
            'buku' => 'Teknik Elektro',
            'tanggal_pinjam' => $rel('-14 days'),
            'tanggal_kembali' => $rel('-3 days'),
            'returned_at' => null
        ],
        [
            'id' => createId(),
            'nim' => '123462',
            'nama' => 'Widya',
            'buku' => 'Manajemen Keuangan',
            'tanggal_pinjam' => $rel('-4 days'),
            'tanggal_kembali' => $rel('+5 days'),
            'returned_at' => null
        ]
    ];
}

/*
|--------------------------------------------------------------------------
| SIMPAN DAN LOAD DATA JSON
|--------------------------------------------------------------------------
*/
function savePeminjaman($file, $data)
{
    file_put_contents(
        $file,
        json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function loadPeminjaman($file)
{
    if (!file_exists($file)) {
        $defaultData = seedPeminjaman();
        savePeminjaman($file, $defaultData);
        return $defaultData;
    }

    $json = file_get_contents($file);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        $defaultData = seedPeminjaman();
        savePeminjaman($file, $defaultData);
        return $defaultData;
    }

    return $data;
}

/*
|--------------------------------------------------------------------------
| HELPER UMUM
|--------------------------------------------------------------------------
*/
function getCurrentMenuPeminjaman() {
    return $_GET['menu'] ?? 'peminjaman';
}

function redirectTo($params = []) {
    $url = basename($_SERVER['PHP_SELF']);

    $baseParams = [
        'menu' => getCurrentMenuPeminjaman(),
    ];

    $params = array_merge($baseParams, $params);

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    header('Location: ' . $url);
    exit;
}

function formatTanggal($date)
{
    if (empty($date)) {
        return '-';
    }
    return date('d M Y', strtotime($date));
}

/*
|--------------------------------------------------------------------------
| HITUNG STATUS, KETERLAMBATAN, DAN DENDA
|--------------------------------------------------------------------------
*/
function hitungMetaPeminjaman($item)
{
    $today = new DateTimeImmutable('today');
    $tanggalKembali = new DateTimeImmutable($item['tanggal_kembali']);
    $returnedAt = !empty($item['returned_at']) ? new DateTimeImmutable($item['returned_at']) : null;

    $pembanding = $returnedAt ?: $today;
    $lateDays = 0;

    if ($pembanding > $tanggalKembali) {
        $lateDays = (int) $tanggalKembali->diff($pembanding)->format('%a');
    }

    $denda = $lateDays * 500;

    if ($returnedAt) {
        $status = 'Dikembalikan';
    } elseif ($today > $tanggalKembali) {
        $status = 'Terlambat';
    } else {
        $status = 'Dipinjam';
    }

    return [
        'status' => $status,
        'terlambat' => $lateDays > 0 ? $lateDays . ' hari' : '-',
        'denda' => 'Rp ' . number_format($denda, 0, ',', '.'),
        'late_days' => $lateDays
    ];
}

/*
|--------------------------------------------------------------------------
| HELPER PAGINATION
|--------------------------------------------------------------------------
*/
function buildPageUrl($page, $search) {
    $params = [
        'menu' => getCurrentMenuPeminjaman(),
        'page' => $page,
    ];

    if ($search !== '') {
        $params['q'] = $search;
    }

    return '?' . http_build_query($params);
}

function getPaginationItems($currentPage, $totalPages)
{
    $items = [];
    $lastWasDots = false;

    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i === 1 || $i === $totalPages || abs($i - $currentPage) <= 1) {
            $items[] = $i;
            $lastWasDots = false;
        } else {
            if (!$lastWasDots) {
                $items[] = '...';
                $lastWasDots = true;
            }
        }
    }

    return $items;
}

/*
|--------------------------------------------------------------------------
| DAFTAR BUKU DAN STOK
|--------------------------------------------------------------------------
*/
function getDaftarBuku()
{
    return [
        'Algoritma' => 5,
        'Basis Data' => 5,
        'Jaringan' => 5,
        'AI' => 5,
        'Web Dev' => 5,
        'Python' => 5,
        'UI/UX' => 5,
        'Pemrograman Web' => 5,
        'Jaringan Komputer' => 5,
        'Sistem Informasi' => 5,
        'Pemrograman Java' => 5,
        'Teknik Elektro' => 5,
        'Manajemen Keuangan' => 5
    ];
}

function getStokBuku($buku)
{
    $daftarBuku = getDaftarBuku();
    return $daftarBuku[$buku] ?? 5;
}

function isPinjamanAktif($item)
{
    return empty($item['returned_at']);
}

/*
|--------------------------------------------------------------------------
| VALIDASI BATAS PINJAMAN BERDASARKAN NIM
|--------------------------------------------------------------------------
*/
function countPinjamanAktifByNim($data, $nim)
{
    $total = 0;

    foreach ($data as $item) {
        if (
            isPinjamanAktif($item) &&
            trim((string) $item['nim']) === trim((string) $nim)
        ) {
            $total++;
        }
    }

    return $total;
}

/*
|--------------------------------------------------------------------------
| VALIDASI STOK BERDASARKAN JUDUL BUKU
|--------------------------------------------------------------------------
*/
function countPinjamanAktifByBuku($data, $buku)
{
    $total = 0;

    foreach ($data as $item) {
        if (
            isPinjamanAktif($item) &&
            trim((string) $item['buku']) === trim((string) $buku)
        ) {
            $total++;
        }
    }

    return $total;
}

function getSisaStokBuku($data, $buku)
{
    $stokTotal = getStokBuku($buku);
    $dipinjamAktif = countPinjamanAktifByBuku($data, $buku);

    return max(0, $stokTotal - $dipinjamAktif);
}

/*
|--------------------------------------------------------------------------
| LOAD DATA PEMINJAMAN
|--------------------------------------------------------------------------
*/
$dataPeminjaman = loadPeminjaman($dataFile);

/*
|--------------------------------------------------------------------------
| PROSES FORM: TAMBAH PEMINJAMAN DAN KEMBALIKAN PEMINJAMAN
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    /*
    |--------------------------------------------------------------------------
    | TAMBAH PEMINJAMAN
    |--------------------------------------------------------------------------
    | - validasi field wajib
    | - validasi tanggal
    | - maksimal 3 buku per NIM
    | - stok buku per judul maksimal 5
    */
    if ($action === 'add_peminjaman') {
    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $buku = trim($_POST['buku'] ?? '');
    $tglPinjam = trim($_POST['tgl_pinjam'] ?? '');
    $tglKembali = trim($_POST['tgl_kembali'] ?? '');

    $oldInput = [
        'nim' => $nim,
        'nama' => $nama,
        'buku' => $buku,
        'tgl_pinjam' => $tglPinjam,
        'tgl_kembali' => $tglKembali
    ];

    if ($nim === '' || $nama === '' || $buku === '' || $tglPinjam === '' || $tglKembali === '') {
        $errors[] = 'Semua field wajib diisi.';
    }

    if ($tglPinjam !== '' && $tglKembali !== '' && strtotime($tglKembali) < strtotime($tglPinjam)) {
        $errors[] = 'Tanggal kembali tidak boleh lebih kecil dari tanggal pinjam.';
    }

    if ($nim !== '') {
        $jumlahPinjamanAktif = countPinjamanAktifByNim($dataPeminjaman, $nim);

        if ($jumlahPinjamanAktif >= 3) {
            $errors[] = 'Peminjaman gagal karena peminjam tersebut sedang meminjam 3 buku.';
        }
    }

    if ($buku !== '') {
        $sisaStok = getSisaStokBuku($dataPeminjaman, $buku);

        if ($sisaStok < 1) {
            $errors[] = 'Peminjaman gagal karena stok buku "' . htmlspecialchars($buku, ENT_QUOTES, 'UTF-8') . '" sedang habis.';
        }
    }

    if (empty($errors)) {
        $newData = [
            'id' => createId(),
            'nim' => $nim,
            'nama' => $nama,
            'buku' => $buku,
            'tanggal_pinjam' => $tglPinjam,
            'tanggal_kembali' => $tglKembali,
            'returned_at' => null
        ];

        array_unshift($dataPeminjaman, $newData);
        savePeminjaman($dataFile, $dataPeminjaman);
        
        redirectTo();
    } else {
        $openPopup = true;
    }
}

    /*
    |--------------------------------------------------------------------------
    | KEMBALIKAN PEMINJAMAN
    |--------------------------------------------------------------------------
    | - tandai returned_at dengan tanggal hari ini
    | - simpan ulang ke file json
    | - kembali ke halaman sesuai page dan search
    */
    if ($action === 'kembalikan_peminjaman') {
    $id = $_POST['id'] ?? '';
    $searchFromPost = trim($_POST['q'] ?? '');
    $pageFromPost = max(1, (int) ($_POST['page'] ?? 1));

    foreach ($dataPeminjaman as &$item) {
        if (($item['id'] ?? '') === $id && empty($item['returned_at'])) {
            $item['returned_at'] = date('Y-m-d');
            break;
        }
    }
    unset($item);

    savePeminjaman($dataFile, $dataPeminjaman);

    $redirectParams = [
        'menu' => 'peminjaman',
        'page' => $pageFromPost
    ];

    if ($searchFromPost !== '') {
        $redirectParams['q'] = $searchFromPost;
    }

    redirectTo($redirectParams);
    }
}

/*
|--------------------------------------------------------------------------
| SEARCH DATA TABEL
|--------------------------------------------------------------------------
*/
$search = trim($_GET['q'] ?? '');
$filteredData = array_values(array_filter($dataPeminjaman, function ($item) use ($search) {
    if ($search === '') {
        return true;
    }

    $keyword = strtolower($search);

    return strpos(strtolower($item['nim']), $keyword) !== false
        || strpos(strtolower($item['nama']), $keyword) !== false
        || strpos(strtolower($item['buku']), $keyword) !== false;
}));

/*
|--------------------------------------------------------------------------
| PAGINATION DATA TABEL
|--------------------------------------------------------------------------
*/
$perPage = 7;
$totalData = count($filteredData);
$totalPages = max(1, (int) ceil($totalData / $perPage));
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $perPage;
$pageData = array_slice($filteredData, $offset, $perPage);

$startDisplay = $totalData > 0 ? $offset + 1 : 0;
$endDisplay = $totalData > 0 ? min($offset + $perPage, $totalData) : 0;
$paginationItems = getPaginationItems($currentPage, $totalPages);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peminjam</title>
    <link rel="stylesheet" href="datapeminjam.css">
    <link rel="stylesheet" href="popuppeminjaman.css">
</head>
<body>

<div class="main-content">
<div class="datapeminjam-wrapper">
    <div class="datapeminjam-header">
        <div class="title-group">
            <h1>Data Peminjam</h1>
        </div>
    </div>

    <div class="toolbar">
        <div class="toolbar-left">
            <button type="button" class="btn-tambah" id="openPopupPeminjaman">
                <span class="plus-icon">+</span>
                Tambah Peminjaman
            </button>

            <form method="get" class="search-form">
                <input type="hidden" name="menu" value="<?= htmlspecialchars($_GET['menu'] ?? 'peminjaman'); ?>">
                <div class="search-box">
                    <input
                        type="text"
                        name="q"
                        placeholder="Cari Peminjaman..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <button type="submit" class="search-submit" aria-label="Cari">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="2"></circle>
                            <path d="M16 16L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-container">
        <table class="datapeminjam-table">
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Terlambat</th>
                    <th>Denda</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pageData)): ?>
                    <?php foreach ($pageData as $item): ?>
                        <?php
                            $meta = hitungMetaPeminjaman($item);

                            $statusClass = 'status-dipinjam';
                            if ($meta['status'] === 'Dikembalikan') {
                                $statusClass = 'status-dikembalikan';
                            } elseif ($meta['status'] === 'Terlambat') {
                                $statusClass = 'status-terlambat';
                            }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nim']); ?></td>
                            <td><?php echo htmlspecialchars($item['nama']); ?></td>
                            <td><?php echo htmlspecialchars($item['buku']); ?></td>
                            <td><?php echo formatTanggal($item['tanggal_pinjam']); ?></td>
                            <td><?php echo formatTanggal($item['tanggal_kembali']); ?></td>
                            <td><?php echo $meta['terlambat']; ?></td>
                            <td><?php echo $meta['denda']; ?></td>
                            <td>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo $meta['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (empty($item['returned_at'])): ?>
                                    <form method="post" class="aksi-form">
                                        <input type="hidden" name="action" value="kembalikan_peminjaman">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search); ?>">
                                        <input type="hidden" name="page" value="<?php echo $currentPage; ?>">

                                        <button
                                            type="submit"
                                            class="aksi-btn <?php echo $meta['status'] === 'Terlambat' ? 'aksi-terlambat' : 'aksi-kembalikan'; ?>"
                                        >
                                            Kembalikan
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="aksi-btn aksi-disabled">-</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="empty-state">Data peminjaman tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="datapeminjam-footer">
        <div class="data-count">
            Menampilkan <?php echo $startDisplay; ?>–<?php echo $endDisplay; ?> dari <?php echo $totalData; ?> data
        </div>

        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="<?php echo htmlspecialchars(buildPageUrl($currentPage - 1, $search)); ?>" class="page-btn">&laquo;</a>
            <?php else: ?>
                <span class="page-btn disabled">&laquo;</span>
            <?php endif; ?>

            <?php foreach ($paginationItems as $pageItem): ?>
                <?php if ($pageItem === '...'): ?>
                    <span class="page-btn ellipsis">...</span>
                <?php elseif ($pageItem == $currentPage): ?>
                    <span class="page-btn active"><?php echo $pageItem; ?></span>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars(buildPageUrl($pageItem, $search)); ?>" class="page-btn"><?php echo $pageItem; ?></a>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="<?php echo htmlspecialchars(buildPageUrl($currentPage + 1, $search)); ?>" class="page-btn">&raquo;</a>
            <?php else: ?>
                <span class="page-btn disabled">&raquo;</span>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<?php include 'popuppeminjaman.php'; ?>

<script>
const openPopupPeminjaman = document.getElementById('openPopupPeminjaman');
const popupPeminjaman = document.getElementById('popupPeminjaman');
const closePopupPeminjaman = document.getElementById('closePopupPeminjaman');
const batalPopupPeminjaman = document.getElementById('batalPopupPeminjaman');

if (openPopupPeminjaman) {
    openPopupPeminjaman.addEventListener('click', function () {
        popupPeminjaman.classList.add('active');
    });
}

if (closePopupPeminjaman) {
    closePopupPeminjaman.addEventListener('click', function () {
        popupPeminjaman.classList.remove('active');
    });
}

if (batalPopupPeminjaman) {
    batalPopupPeminjaman.addEventListener('click', function () {
        popupPeminjaman.classList.remove('active');
    });
}

if (popupPeminjaman) {
    popupPeminjaman.addEventListener('click', function (e) {
        if (e.target === popupPeminjaman) {
            popupPeminjaman.classList.remove('active');
        }
    });
}
</script>

</body>
</html>