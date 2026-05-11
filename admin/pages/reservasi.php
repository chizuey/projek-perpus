<?php
require_once __DIR__ . '/../../controllers/ReservasiController.php';
extract(ReservasiController::index(), EXTR_SKIP);

function eR($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmtTgl($d): string {
    if (empty($d)) return '-';
    $ts = strtotime((string)$d);
    return $ts ? date('d M Y', $ts) : '-';
}

$statusLabel = [
    'menunggu'   => ['label' => 'Menunggu',   'class' => 'badge-pending'],
    'disetujui'  => ['label' => 'Disetujui',  'class' => 'badge-confirmed'],
    'dibatalkan' => ['label' => 'Dibatalkan', 'class' => 'badge-cancelled'],
    'selesai'    => ['label' => 'Selesai',    'class' => 'badge-confirmed'],
];
?>

<div class="reservasi-wrapper">
    <div class="reservasi-header">
        <div class="title-group">
            <h1>Reservasi Buku</h1>
            <span class="total-badge"><?= (int)$totalData; ?> data</span>
        </div>
    </div>

    <div class="toolbar">
        <div class="toolbar-left">
            <form method="get" class="search-form">
                <input type="hidden" name="menu" value="reservasi">
                <input type="hidden" name="per_page" value="<?= (int)$perPage; ?>">
                <?php if ($filterStatus !== ''): ?>
                    <input type="hidden" name="status" value="<?= eR($filterStatus); ?>">
                <?php endif; ?>
                <input
                    type="search"
                    name="q"
                    value="<?= eR($search); ?>"
                    placeholder="Cari kode, nama, atau judul buku..."
                    class="search-input"
                    autocomplete="off"
                >
                <button type="submit" class="btn-search">Cari</button>
            </form>

            <div class="filter-group">
                <?php
                $statusOptions = [
                    ''           => 'Semua',
                    'menunggu'   => 'Menunggu',
                    'disetujui'  => 'Disetujui',
                    'dibatalkan' => 'Dibatalkan',
                    'selesai'    => 'Selesai',
                ];
                foreach ($statusOptions as $val => $lbl):
                    $active = ($filterStatus === $val) ? 'active' : '';
                ?>
                    <a href="?menu=reservasi&status=<?= eR($val); ?>&q=<?= eR($search); ?>&per_page=<?= (int)$perPage; ?>"
                       class="filter-chip <?= $active; ?>">
                        <?= eR($lbl); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="toolbar-right">
            <form method="get" class="per-page-form">
                <input type="hidden" name="menu" value="reservasi">
                <input type="hidden" name="q" value="<?= eR($search); ?>">
                <input type="hidden" name="status" value="<?= eR($filterStatus); ?>">
                <label for="per_page_reservasi">Tampilkan</label>
                <select name="per_page" id="per_page_reservasi" onchange="this.form.submit()">
                    <?php foreach (Reservasi::perPageOptions() as $opt): ?>
                        <option value="<?= (int)$opt; ?>" <?= $perPage === $opt ? 'selected' : ''; ?>>
                            <?= (int)$opt; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span>data</span>
            </form>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="reservasi-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Anggota</th>
                    <th>Judul Buku</th>
                    <th>Tgl Reservasi</th>
                    <th>Kadaluarsa</th>
                    <th>Status</th>
                    <th>Admin</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($pageData)): ?>
                <?php foreach ($pageData as $i => $r): ?>
                    <?php
                    $st = $r['status'] ?? 'menunggu';
                    $badge = $statusLabel[$st] ?? ['label' => $st, 'class' => 'badge-pending'];
                    ?>
                    <tr>
                        <td><?= (int)($startDisplay + $i); ?></td>
                        <td><?= eR($r['kode_anggota'] ?? ''); ?></td>
                        <td><?= eR($r['nama_anggota'] ?? ''); ?></td>
                        <td><?= eR($r['judul_buku'] ?? ''); ?></td>
                        <td><?= fmtTgl($r['tanggal_reservasi'] ?? ''); ?></td>
                        <td><?= fmtTgl($r['tanggal_kadaluarsa'] ?? ''); ?></td>
                        <td>
                            <span class="status-badge <?= eR($badge['class']); ?>">
                                <?= eR($badge['label']); ?>
                            </span>
                        </td>
                        <td><?= eR($r['nama_admin'] ?? '-'); ?></td>
                        <td>
                            <div class="action-group">
                                <?php if ($st === 'menunggu'): ?>
                                    <button
                                        type="button"
                                        class="btn-konfirmasi js-konfirmasi-reservasi"
                                        data-id="<?= (int)$r['id_reservasi']; ?>"
                                        data-nama="<?= eR($r['nama_anggota'] ?? ''); ?>"
                                        data-buku="<?= eR($r['judul_buku'] ?? ''); ?>"
                                        data-page="<?= (int)$currentPage; ?>"
                                        data-per-page="<?= (int)$perPage; ?>"
                                        data-q="<?= eR($search); ?>"
                                        data-status="<?= eR($filterStatus); ?>"
                                    >
                                        Konfirmasi
                                    </button>
                                    <button
                                        type="button"
                                        class="btn-batal js-batalkan-reservasi"
                                        data-id="<?= (int)$r['id_reservasi']; ?>"
                                        data-nama="<?= eR($r['nama_anggota'] ?? ''); ?>"
                                        data-buku="<?= eR($r['judul_buku'] ?? ''); ?>"
                                        data-page="<?= (int)$currentPage; ?>"
                                        data-per-page="<?= (int)$perPage; ?>"
                                        data-q="<?= eR($search); ?>"
                                        data-status="<?= eR($filterStatus); ?>"
                                    >
                                        Batalkan
                                    </button>
                                <?php elseif ($st === 'disetujui'): ?>
                                    <button
                                        type="button"
                                        class="btn-batal js-batalkan-reservasi"
                                        data-id="<?= (int)$r['id_reservasi']; ?>"
                                        data-nama="<?= eR($r['nama_anggota'] ?? ''); ?>"
                                        data-buku="<?= eR($r['judul_buku'] ?? ''); ?>"
                                        data-page="<?= (int)$currentPage; ?>"
                                        data-per-page="<?= (int)$perPage; ?>"
                                        data-q="<?= eR($search); ?>"
                                        data-status="<?= eR($filterStatus); ?>"
                                    >
                                        Batalkan
                                    </button>
                                <?php else: ?>
                                    <span class="empty-action">-</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="empty-row">
                        <?= $search !== '' || $filterStatus !== ''
                            ? 'Tidak ada data yang cocok dengan filter.'
                            : 'Belum ada data reservasi.'; ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span class="table-info">
            <?php if ($totalData > 0): ?>
                Menampilkan <?= (int)$startDisplay; ?>-<?= (int)$endDisplay; ?> dari <?= (int)$totalData; ?> data
            <?php else: ?>
                Tidak ada data
            <?php endif; ?>
        </span>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?menu=reservasi&page=<?= (int)($currentPage - 1); ?>&per_page=<?= (int)$perPage; ?>&q=<?= eR($search); ?>&status=<?= eR($filterStatus); ?>"
                       class="page-btn">&laquo;</a>
                <?php endif; ?>

                <?php foreach ($paginationItems as $pg): ?>
                    <?php if ($pg === '...'): ?>
                        <span class="page-dots">...</span>
                    <?php else: ?>
                        <a href="?menu=reservasi&page=<?= (int)$pg; ?>&per_page=<?= (int)$perPage; ?>&q=<?= eR($search); ?>&status=<?= eR($filterStatus); ?>"
                           class="page-btn <?= $pg === $currentPage ? 'active' : ''; ?>">
                            <?= (int)$pg; ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?menu=reservasi&page=<?= (int)($currentPage + 1); ?>&per_page=<?= (int)$perPage; ?>&q=<?= eR($search); ?>&status=<?= eR($filterStatus); ?>"
                       class="page-btn">&raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../popup/reservasi.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-konfirmasi-reservasi').forEach(function (button) {
        button.onclick = function () {
            bukaPopupKonfirmasiReservasi(
                this.dataset.id,
                this.dataset.nama,
                this.dataset.buku,
                this.dataset.page,
                this.dataset.perPage,
                this.dataset.q,
                this.dataset.status
            );
        };
    });

    document.querySelectorAll('.js-batalkan-reservasi').forEach(function (button) {
        button.onclick = function () {
            bukaPopupBatalkanReservasi(
                this.dataset.id,
                this.dataset.nama,
                this.dataset.buku,
                this.dataset.page,
                this.dataset.perPage,
                this.dataset.q,
                this.dataset.status
            );
        };
    });
});
</script>
