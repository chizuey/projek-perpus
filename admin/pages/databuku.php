<?php
require_once __DIR__ . '/../../controllers/BukuController.php';
$bukuController = new BukuController();
extract($bukuController->index(), EXTR_SKIP);
?>

<!-- Tampilan utama tabel Data Buku -->
<section class="databuku-page">
    <div class="databuku-header">
        <h1>Data Buku</h1>
    </div>

    <!-- Toolbar tambah, pencarian, dan filter buku -->
    <div class="databuku-toolbar">
        <a href="?menu=tambahbuku" class="btn-add-book">
            <span class="plus-icon">+</span>
            Tambah Buku
        </a>

        <form method="get" class="databuku-search">
            <input type="hidden" name="menu" value="databuku">
            <input type="hidden" name="kategori" value="<?= eBuku($kategoriFilter); ?>">
            <input type="hidden" name="per_page" value="<?= (int) $perPage; ?>">
            <input type="text" name="q" placeholder="Cari Buku..." value="<?= eBuku($search); ?>">
            <button type="submit" aria-label="Cari buku">
                <i class="bi bi-search"></i>
            </button>
        </form>

        <form method="get" class="databuku-filter" id="filterBukuForm">
            <input type="hidden" name="menu" value="databuku">
            <input type="hidden" name="q" value="<?= eBuku($search); ?>">
            <input type="hidden" name="per_page" value="<?= (int) $perPage; ?>">
            <select name="kategori" aria-label="Filter kategori buku">
                <option value="Semua" <?= $kategoriFilter === 'Semua' ? 'selected' : ''; ?>>Filter</option>
                <?php foreach ($kategoriOptions as $kategori): ?>
                    <option value="<?= eBuku($kategori); ?>" <?= $kategoriFilter === $kategori ? 'selected' : ''; ?>>
                        <?= eBuku($kategori); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <i class="bi bi-chevron-down"></i>
        </form>
    </div>

    <!-- Tabel daftar buku dan tombol aksi -->
    <div class="databuku-table-wrap">
        <table class="databuku-table">
            <colgroup>
                <col class="col-no">
                <col class="col-judul">
                <col class="col-penulis">
                <col class="col-penerbit">
                <col class="col-tahun">
                <col class="col-kategori">
                <col class="col-stok">
                <col class="col-aksi">
            </colgroup>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Judul Buku</th>
                    <th>Penulis</th>
                    <th>Penerbit</th>
                    <th>Tahun</th>
                    <th>Kategori :</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pageData)): ?>
                    <tr>
                        <td colspan="8" class="databuku-empty">Data buku tidak ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pageData as $index => $book): ?>
                        <tr>
                            <td><?= (int) ($offset + $index + 1); ?>.</td>
                            <td><?= eBuku($book['judul']); ?></td>
                            <td><?= eBuku($book['penulis']); ?></td>
                            <td><?= eBuku($book['penerbit']); ?></td>
                            <td><?= eBuku($book['tahun']); ?></td>
                            <td><?= eBuku($book['kategori']); ?></td>
                            <?php
                            $dipinjam = (int) ($book['dipinjam'] ?? 0);
                            $stokTersedia = (int) ($book['stok_tersedia'] ?? 0);
                            $bookPayload = $book;
                            $bookPayload['stok_tersedia'] = $stokTersedia;
                            $bookPayload['dipinjam'] = $dipinjam;
                            ?>
                            <td><?= eBuku($stokTersedia); ?></td>
                            <td class="databuku-actions">
                                <a
                                    href="?menu=editbuku&id=<?= (int) $book['id']; ?>&page=<?= (int) $currentPage; ?>&q=<?= eBuku($search); ?>&kategori_filter=<?= eBuku($kategoriFilter); ?>&per_page=<?= (int) $perPage; ?>"
                                    class="btn-table-action"
                                >
                                    <i class="bi bi-pencil"></i>
                                    <span>Edit</span>
                                </a>
                                <form
                                    method="post"
                                    action="actions/buku/delete.php"
                                    class="databuku-delete-form"
                                    onsubmit="return confirm('Yakin ingin menghapus buku ini?')"
                                >
                                    <input type="hidden" name="action" value="delete_buku">
                                    <input type="hidden" name="id" value="<?= (int) $book['id']; ?>">
                                    <input type="hidden" name="page" value="<?= (int) $currentPage; ?>">
                                    <input type="hidden" name="q" value="<?= eBuku($search); ?>">
                                    <input type="hidden" name="kategori_filter" value="<?= eBuku($kategoriFilter); ?>">
                                    <input type="hidden" name="per_page" value="<?= (int) $perPage; ?>">
                                    <button type="submit" class="btn-table-action btn-delete-book">
                                        <i class="bi bi-trash"></i>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="databuku-footer">
        <div class="table-footer-info">
            <span>Menampilkan <?= (int) $startDisplay; ?>-<?= (int) $endDisplay; ?> dari <?= (int) $totalData; ?> data</span>
            <form method="get" class="per-page-form">
                <input type="hidden" name="menu" value="databuku">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="q" value="<?= eBuku($search); ?>">
                <input type="hidden" name="kategori" value="<?= eBuku($kategoriFilter); ?>">
                <label>
                    <span>Tampilkan</span>
                    <select name="per_page" onchange="this.form.submit()">
                        <?php foreach (getBukuPerPageOptions() as $option): ?>
                            <option value="<?= (int) $option; ?>" <?= $perPage === $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span>data</span>
                </label>
            </form>
        </div>
        <div class="databuku-pagination">
            <a class="page-arrow <?= $currentPage <= 1 ? 'disabled' : ''; ?>" href="<?= $currentPage <= 1 ? '#' : eBuku(buildBukuUrl($currentPage - 1, $search, $kategoriFilter, $perPage)); ?>">
                <i class="bi bi-chevron-left"></i>
            </a>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i <= 2 || $i === $totalPages || abs($i - $currentPage) <= 1): ?>
                    <a class="page-number <?= $i === $currentPage ? 'active' : ''; ?>" href="<?= eBuku(buildBukuUrl($i, $search, $kategoriFilter, $perPage)); ?>"><?= (int) $i; ?></a>
                <?php elseif ($i === 3): ?>
                    <span class="page-dots">...</span>
                <?php endif; ?>
            <?php endfor; ?>

            <a class="page-arrow <?= $currentPage >= $totalPages ? 'disabled' : ''; ?>" href="<?= $currentPage >= $totalPages ? '#' : eBuku(buildBukuUrl($currentPage + 1, $search, $kategoriFilter, $perPage)); ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Script filter Data Buku -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterBukuForm');
    const filterSelect = filterForm ? filterForm.querySelector('select') : null;

    if (filterSelect) {
        filterSelect.addEventListener('change', function () {
            filterForm.submit();
        });
    }
});
</script>
