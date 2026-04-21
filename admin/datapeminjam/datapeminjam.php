<?php
/*
|--------------------------------------------------------------------------
| KONFIGURASI AWAL
|--------------------------------------------------------------------------
*/
$dataFile = __DIR__ . '/data_peminjaman.json';
$laporanFile = __DIR__ . '/../laporantransaksi/data_laporan_transaksi.json';
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

function loadLaporanTransaksi($file)
{
    if (!file_exists($file)) {
        return [];
    }

    $json = file_get_contents($file);
    $data = json_decode($json, true);

    return is_array($data) ? $data : [];
}

function saveLaporanTransaksi($file, $data)
{
    file_put_contents(
        $file,
        json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function nextLaporanId(array $data)
{
    $max = 0;

    foreach ($data as $item) {
        $max = max($max, (int) ($item['id'] ?? 0));
    }

    return $max + 1;
}

function buildStatusLaporan($jatuhTempo, $tglKembali = '')
{
    if (!empty($tglKembali)) {
        return 'Dikembalikan';
    }

    return strtotime(date('Y-m-d')) > strtotime($jatuhTempo)
        ? 'Terlambat'
        : 'Belum Kembali';
}

function cariIndexLaporanBySourceId(array $laporanData, $sourceId)
{
    foreach ($laporanData as $index => $item) {
        if (($item['source_id'] ?? '') === $sourceId) {
            return $index;
        }
    }

    return null;
}

function buatItemLaporanDariPeminjaman(array $item, $laporanId = null)
{
    $tglKembaliReal = !empty($item['returned_at']) ? $item['returned_at'] : '';

    return [
        'id' => $laporanId,
        'source_id' => $item['id'],
        'tanggal' => $item['tanggal_pinjam'],
        'peminjam' => $item['nama'],
        'judul_buku' => $item['buku'],
        'tgl_pinjam' => $item['tanggal_pinjam'],
        'tgl_jatuh_tempo' => $item['tanggal_kembali'],
        'tgl_kembali' => $tglKembaliReal,
        'status' => buildStatusLaporan($item['tanggal_kembali'], $tglKembaliReal),
    ];
}

function sinkronkanPeminjamanKeLaporan(array $dataPeminjaman, array $laporanData)
{
    $changed = false;

    foreach ($dataPeminjaman as $item) {
        $index = cariIndexLaporanBySourceId($laporanData, $item['id']);
        $laporanItem = buatItemLaporanDariPeminjaman($item);

        if ($index === null) {
            $laporanItem['id'] = nextLaporanId($laporanData);
            array_unshift($laporanData, $laporanItem);
            $changed = true;
            continue;
        }

        $existingId = $laporanData[$index]['id'] ?? nextLaporanId($laporanData);
        $laporanItem['id'] = $existingId;

        if ($laporanData[$index] != $laporanItem) {
            $laporanData[$index] = $laporanItem;
            $changed = true;
        }
    }

    return [$laporanData, $changed];
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
$dataPeminjamanSemua = loadPeminjaman($dataFile);
$laporanTransaksi = loadLaporanTransaksi($laporanFile);

[$laporanTransaksi, $laporanChanged] = sinkronkanPeminjamanKeLaporan($dataPeminjamanSemua, $laporanTransaksi);

$dataPeminjaman = array_values(array_filter($dataPeminjamanSemua, 'isPinjamanAktif'));
$peminjamanChanged = count($dataPeminjaman) !== count($dataPeminjamanSemua);

if ($laporanChanged) {
    saveLaporanTransaksi($laporanFile, $laporanTransaksi);
}

if ($peminjamanChanged) {
    savePeminjaman($dataFile, $dataPeminjaman);
}

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

    // otomatis tanggal
    $tglPinjam = date('Y-m-d');
    $tglKembali = date('Y-m-d', strtotime('+7 days'));

    $oldInput = [
    'nim' => $nim,
    'nama' => $nama,
    'buku' => $buku,
    'tgl_pinjam' => $tglPinjam,
    'tgl_kembali' => $tglKembali
    ];

    if ($nim === '' || $nama === '' || $buku === '') {
    $errors[] = 'Semua field wajib diisi.';
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

    $laporanTransaksi = loadLaporanTransaksi($laporanFile);
    $laporanBaru = buatItemLaporanDariPeminjaman($newData, nextLaporanId($laporanTransaksi));
    array_unshift($laporanTransaksi, $laporanBaru);
    saveLaporanTransaksi($laporanFile, $laporanTransaksi);

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

    $laporanTransaksi = loadLaporanTransaksi($laporanFile);

    foreach ($dataPeminjaman as $index => $item) {
        if (($item['id'] ?? '') !== $id) {
            continue;
        }

        $item['returned_at'] = date('Y-m-d');

        $laporanIndex = cariIndexLaporanBySourceId($laporanTransaksi, $item['id']);
        $laporanItem = buatItemLaporanDariPeminjaman(
            $item,
            $laporanIndex !== null
                ? ($laporanTransaksi[$laporanIndex]['id'] ?? nextLaporanId($laporanTransaksi))
                : nextLaporanId($laporanTransaksi)
        );

        if ($laporanIndex === null) {
            array_unshift($laporanTransaksi, $laporanItem);
        } else {
            $laporanTransaksi[$laporanIndex] = $laporanItem;
        }

        unset($dataPeminjaman[$index]);
        break;
    }

    $dataPeminjaman = array_values($dataPeminjaman);

    savePeminjaman($dataFile, $dataPeminjaman);
    saveLaporanTransaksi($laporanFile, $laporanTransaksi);

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
    <link rel="stylesheet" href="datapeminjam/datapeminjam.css">
    <link rel="stylesheet" href="datapeminjam/popuppeminjaman.css">
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
                                <button
                                    type="button"
                                    class="btn-kembalikan js-open-return-modal"
                                    data-id="<?= htmlspecialchars($item['id']) ?>"
                                    data-nama="<?= htmlspecialchars($item['nama']) ?>"
                                    data-buku="<?= htmlspecialchars($item['buku']) ?>"
                                >
                                    Kembalikan
                                </button>
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
        <!-- Pop Up Konfirmasi Pengembalian -->
         <div class="return-confirm-overlay" id="returnConfirmOverlay">
    <div class="return-confirm-box">
        <div class="return-confirm-header">
            <h3>Konfirmasi Pengembalian</h3>
            <button type="button" class="return-confirm-close" id="returnConfirmClose" aria-label="Tutup">
                &times;
            </button>
        </div>

        <form method="post" class="return-confirm-form">
            <div class="return-confirm-body">
                <p class="return-confirm-text">Yakin ingin mengembalikan data ini?</p>

                <div class="return-confirm-detail">
                    <div><strong>Nama:</strong> <span id="returnConfirmNama">-</span></div>
                    <div><strong>Buku:</strong> <span id="returnConfirmBuku">-</span></div>
                </div>

                <input type="hidden" name="action" value="kembalikan_peminjaman">
                <input type="hidden" name="id" id="returnConfirmId">
                <input type="hidden" name="q" value="<?= htmlspecialchars($search ?? '') ?>">
                <input type="hidden" name="page" value="<?= (int) ($currentPage ?? 1) ?>">
            </div>

            <div class="return-confirm-actions">
                <button type="button" class="btn-return-batal" id="returnConfirmCancel">Batal</button>
                <button type="submit" class="btn-return-submit">Kembalikan</button>
            </div>
        </form>
    </div>
</div>

                    <script>
document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('returnConfirmOverlay');
    const closeBtn = document.getElementById('returnConfirmClose');
    const cancelBtn = document.getElementById('returnConfirmCancel');
    const idInput = document.getElementById('returnConfirmId');
    const namaText = document.getElementById('returnConfirmNama');
    const bukuText = document.getElementById('returnConfirmBuku');
    const openButtons = document.querySelectorAll('.js-open-return-modal');

    function openModal(id, nama, buku) {
        idInput.value = id || '';
        namaText.textContent = nama || '-';
        bukuText.textContent = buku || '-';
        overlay.classList.add('show');
        document.body.classList.add('modal-open');
    }

    function closeModal() {
        overlay.classList.remove('show');
        document.body.classList.remove('modal-open');
    }

    openButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openModal(
                this.dataset.id,
                this.dataset.nama,
                this.dataset.buku
            );
        });
    });

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('show')) {
            closeModal();
        }
    });
});
</script>

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

function bukaPopupPeminjaman() {
  if (popupPeminjaman) {
    popupPeminjaman.classList.add('active');
  }
}

function tutupPopupPeminjaman() {
  if (popupPeminjaman) {
    popupPeminjaman.classList.remove('active');
  }
}

if (openPopupPeminjaman) {
  openPopupPeminjaman.addEventListener('click', function () {
    bukaPopupPeminjaman();
  });
}

if (closePopupPeminjaman) {
  closePopupPeminjaman.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    tutupPopupPeminjaman();
  });
}

if (batalPopupPeminjaman) {
  batalPopupPeminjaman.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    tutupPopupPeminjaman();
  });
}

if (popupPeminjaman) {
  popupPeminjaman.addEventListener('click', function (e) {
    if (e.target === popupPeminjaman) {
      tutupPopupPeminjaman();
    }
  });
}
</script>

</body>
</html>