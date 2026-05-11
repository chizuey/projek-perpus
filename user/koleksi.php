<?php
require_once __DIR__ . '/../models/Buku.php';

$bukuModel = new Buku();

// Ambil parameter filter dari URL
$search   = isset($_GET['q']) ? trim($_GET['q']) : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$tahun    = isset($_GET['tahun']) ? trim($_GET['tahun']) : '';
$page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage  = 15;

// Ambil data buku dengan filter & pagination
$result = $bukuModel->searchKoleksi($search, $kategori, $tahun, $page, $perPage);
$books       = $result['data'];
$total       = $result['total'];
$totalPages  = $result['totalPages'];
$currentPage = $result['currentPage'];

// Ambil opsi filter
$kategoriOptions = $bukuModel->kategoriOptions();
$tahunOptions    = $bukuModel->getTahunOptions();

// Helper untuk build URL pagination & filter
function koleksiBuildUrl($page, $search, $kategori, $tahun) {
    $params = ['page' => $page];
    if ($search !== '')   $params['q'] = $search;
    if ($kategori !== '') $params['kategori'] = $kategori;
    if ($tahun !== '')    $params['tahun'] = $tahun;
    return 'koleksi.php?' . http_build_query($params);
}

$hasActiveFilter = ($search !== '' || $kategori !== '' || $tahun !== '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koleksi - Perpustakaan Polije</title>
    <link rel="stylesheet" href="../public/css/style.css?v=6">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/stylekoleksi.css?v=<?= time(); ?>">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="koleksi-page">
    <div class="hero-section-koleksi">
        <div class="hero-content-koleksi">
            <div class="hero-icon-koleksi">
                <img src="../public/img/Books.png" alt="Icon Koleksi">
            </div>
            <h1 class="hero-title-koleksi">Koleksi Buku</h1>
            <p class="hero-subtitle-koleksi">Perpustakaan POLIJE</p>
        </div>
    </div>

    <div class="koleksi-header">
        <form method="get" action="koleksi.php" class="koleksi-search-wrapper">
            <?php if ($kategori !== ''): ?>
                <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori); ?>">
            <?php endif; ?>
            <?php if ($tahun !== ''): ?>
                <input type="hidden" name="tahun" value="<?= htmlspecialchars($tahun); ?>">
            <?php endif; ?>

            <div class="koleksi-search-box">
                <img src="gambar/Container.png" alt="search">
                <input type="text" name="q" placeholder="Cari judul buku atau penulis..." value="<?= htmlspecialchars($search); ?>" autocomplete="off">
            </div>
        </form>

        <div class="koleksi-filter-row">
            <span class="filter-label">Filter:</span>
            <select id="filterKategori" onchange="applyFilters()">
                <option value="">Kategori</option>
                <?php foreach ($kategoriOptions as $kat): ?>
                    <option value="<?= htmlspecialchars($kat); ?>" <?= ($kategori === $kat) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($kat); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="filterTahun" onchange="applyFilters()">
                <option value="">Tahun Terbit</option>
                <?php foreach ($tahunOptions as $thn): ?>
                    <option value="<?= htmlspecialchars($thn); ?>" <?= ($tahun === $thn) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($thn); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <a href="koleksi.php" class="btn-reset-filter <?= $hasActiveFilter ? 'has-filter' : ''; ?>">Reset</a>
        </div>
    </div>

    <div class="koleksi-grid-wrapper">
        <div class="koleksi-info">
            Menampilkan <strong><?= count($books); ?></strong> dari <strong><?= $total; ?></strong> buku
        </div>

        <div class="koleksi-grid">
            <?php if (empty($books)): ?>
                <div class="koleksi-empty">
                    <i class="fas fa-book-open"></i>
                    <p>Tidak ada buku ditemukan.</p>
                </div>
            <?php else: ?>
                <?php foreach ($books as $b): 
                    // VARIABEL POPUP (ADD INI)
                    $id_pop    = (int)($b['id'] ?? $b['id_buku'] ?? 0);
                    $titel_pop = addslashes(htmlspecialchars($b['judul']));
                    $kat_pop   = htmlspecialchars($b['kategori']);
                    $desk_pop  = addslashes(htmlspecialchars($b['deskripsi'] ?? 'Tidak ada deskripsi.'));
                    $img_pop   = !empty($b['cover']) ? '../' . htmlspecialchars($b['cover']) : 'gambar/buku.png';
                ?>
                    <div class="koleksi-card" style="cursor: pointer;" onclick="bukaPopup(<?= $id_pop ?>, '<?= $titel_pop ?>', '<?= $kat_pop ?>', '<?= $img_pop ?>', '<?= $desk_pop ?>')">>
                        <div class="koleksi-card-cover">
                            <img src="../<?= !empty($b['cover']) ? htmlspecialchars($b['cover']) : 'user/gambar/buku.png'; ?>" alt="<?= htmlspecialchars($b['judul']); ?>">
                        </div>
                        <div class="koleksi-card-info">
                            <h4><?= htmlspecialchars($b['judul']); ?></h4>
                            <div class="author"><?= htmlspecialchars($b['penulis']); ?></div>
                            <div class="meta-row">
                                <?php if (!empty($b['kategori'])): ?>
                                    <span class="kategori-badge"><?= htmlspecialchars($b['kategori']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($b['tahun'])): ?>
                                    <span class="tahun"><?= htmlspecialchars($b['tahun']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="koleksi-pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="<?= koleksiBuildUrl($currentPage - 1, $search, $kategori, $tahun); ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-chevron-left"></i></span>
                <?php endif; ?>

                <?php
                $start = max(1, $currentPage - 2);
                $end   = min($totalPages, $currentPage + 2);

                if ($start > 1): ?>
                    <a href="<?= koleksiBuildUrl(1, $search, $kategori, $tahun); ?>">1</a>
                    <?php if ($start > 2): ?><span class="dots">...</span><?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $currentPage): ?>
                        <span class="active"><?= $i; ?></span>
                    <?php else: ?>
                        <a href="<?= koleksiBuildUrl($i, $search, $kategori, $tahun); ?>"><?= $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end < $totalPages): ?>
                    <?php if ($end < $totalPages - 1): ?><span class="dots">...</span><?php endif; ?>
                    <a href="<?= koleksiBuildUrl($totalPages, $search, $kategori, $tahun); ?>"><?= $totalPages; ?></a>
                <?php endif; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= koleksiBuildUrl($currentPage + 1, $search, $kategori, $tahun); ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-chevron-right"></i></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'modal_detail.php'; ?>
<?php include 'foot.php'; ?>

<script>
function applyFilters() {
    const kategori = document.getElementById('filterKategori').value;
    const tahun = document.getElementById('filterTahun').value;
    const params = new URLSearchParams(window.location.search);
    const q = params.get('q') || '';
    
    const newParams = new URLSearchParams();
    if (q) newParams.set('q', q);
    if (kategori) newParams.set('kategori', kategori);
    if (tahun) newParams.set('tahun', tahun);
    
    window.location.href = 'koleksi.php?' + newParams.toString();
}
</script>
</body>
</html>