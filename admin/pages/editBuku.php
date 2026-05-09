<?php
require_once __DIR__ . '/../../controllers/BukuController.php';
$bukuController = new BukuController();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ?menu=databuku');
    exit;
}

extract($bukuController->edit($id), EXTR_SKIP);

if (!function_exists('eEditBuku')) {
    function eEditBuku($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<!-- Navigasi kembali dari form Edit Buku ke Data Buku -->
<div class="breadcrumb-bar">
    <a href="?menu=databuku" class="breadcrumb-back-btn">
        <i class="bi bi-chevron-left"></i>
    </a>
    <span class="breadcrumb-title">Edit Buku</span>
</div>

<!-- Tampilan form edit buku -->
<main class="tambah-content">
    <?php if (!empty($errorsEditBuku)): ?>
        <div class="form-alert">
            <?= eEditBuku(implode(' ', $errorsEditBuku)); ?>
        </div>
    <?php endif; ?>

    <!-- Form update buku, proses submit ditangani update.php -->
    <form method="POST" action="actions/buku/update.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_buku">
        <input type="hidden" name="id" value="<?= (int) $bookId; ?>">
        
        <!-- Parameter pagination dan filter untuk redirect setelah update -->
        <input type="hidden" name="page" value="<?= (int) ($_GET['page'] ?? 1); ?>">
        <input type="hidden" name="q" value="<?= eEditBuku($_GET['q'] ?? ''); ?>">
        <input type="hidden" name="kategori_filter" value="<?= eEditBuku($_GET['kategori_filter'] ?? 'Semua'); ?>">
        <input type="hidden" name="per_page" value="<?= (int) ($_GET['per_page'] ?? 7); ?>">

        <div class="panel-card mb-3">
            <div class="panel-title">Edit Data Buku</div>

            <div class="form-row-2">
                <div>
                    <label class="form-label-custom" for="judul">Judul Buku</label>
                    <input class="form-input-custom" id="judul" name="judul" type="text" placeholder="Masukkan judul buku..." value="<?= eEditBuku($oldEditBuku['judul']); ?>" required>
                </div>
                <div>
                    <label class="form-label-custom" for="penulis">Penulis</label>
                    <input class="form-input-custom" id="penulis" name="penulis" type="text" placeholder="Masukkan nama penulis..." value="<?= eEditBuku($oldEditBuku['penulis']); ?>" required>
                </div>
            </div>

            <div class="form-row-2">
                <div>
                    <label class="form-label-custom" for="penerbit">Penerbit</label>
                    <input class="form-input-custom" id="penerbit" name="penerbit" type="text" placeholder="Masukkan nama penerbit..." value="<?= eEditBuku($oldEditBuku['penerbit']); ?>" required>
                </div>
                <div>
                    <label class="form-label-custom" for="isbn">ISBN</label>
                    <input class="form-input-custom" id="isbn" name="isbn" type="text" placeholder="cth: 978-3-16-148410-0" value="<?= eEditBuku($oldEditBuku['isbn']); ?>">
                </div>
            </div>

            <div class="form-row-2">
                <div>
                    <label class="form-label-custom" for="tahun">Tahun Terbit</label>
                    <input class="form-input-custom" id="tahun" name="tahun" type="number" min="0" placeholder="cth: 2023" value="<?= eEditBuku($oldEditBuku['tahun']); ?>" required>
                </div>
                <div>
                    <label class="form-label-custom" for="tempat_terbit">Tempat Terbit</label>
                    <input class="form-input-custom" id="tempat_terbit" name="tempat_terbit" type="text" placeholder="cth: Jakarta" value="<?= eEditBuku($oldEditBuku['tempat_terbit']); ?>">
                </div>
            </div>

            <span class="kategori-label">Kategori :</span>
            <div class="kategori-grid">
                <?php
                $oldKategoriValue = $oldEditBuku['kategori'] ?? '';
                $oldKategoriValues = is_array($oldKategoriValue) ? $oldKategoriValue : [$oldKategoriValue];
                ?>
                <?php foreach ($kategoriList as $kategori): ?>
                    <label class="kategori-item">
                        <input
                            type="checkbox"
                            class="kategori-checkbox"
                            name="kategori[]"
                            value="<?= eEditBuku($kategori); ?>"
                            <?= in_array($kategori, $oldKategoriValues, true) ? 'checked' : ''; ?>
                        >
                        <span class="kategori-text"><?= eEditBuku($kategori); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <span class="cover-section-title">Cover Buku</span>
            <div class="cover-edit-wrapper" style="display: flex; gap: 20px; align-items: flex-start; margin-bottom: 1.1rem;">
                <?php if (!empty($oldEditBuku['cover'])): ?>
                    <div class="current-cover">
                        <span style="font-size: 0.7rem; color: #666; display: block; margin-bottom: 5px;">Cover Saat Ini:</span>
                        <img src="../<?= eEditBuku($oldEditBuku['cover']); ?>" alt="Cover" style="width: 80px; height: 110px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">
                    </div>
                <?php endif; ?>
                
                <div class="upload-new-cover">
                    <span style="font-size: 0.7rem; color: #666; display: block; margin-bottom: 5px;">Ganti Cover:</span>
                    <label class="cover-upload-area" for="cover" style="margin-bottom: 0;">
                        <span class="cover-upload-hint">Klik untuk upload cover baru</span>
                    </label>
                    <input type="file" id="cover" name="cover" accept="image/*" class="cover-file-input">
                </div>
            </div>

            <label class="synopsis-label" for="sinopsis">Deskripsi/Sinopsis :</label>
            <textarea class="synopsis-area" id="sinopsis" name="sinopsis" placeholder="Masukkan deskripsi atau sinopsis buku..."><?= eEditBuku($oldEditBuku['sinopsis']); ?></textarea>

            <div class="form-actions" style="margin-top: 1rem;">
                <button type="submit" class="btn-tambahkan">Simpan Perubahan</button>
                <a href="?menu=databuku" class="btn-batal-form" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">Batal</a>
            </div>
        </div>

        <div class="panel-card">
            <div class="copy-panel-title">Copy Buku</div>
            <label class="form-label-custom" for="stok">Jumlah Copy / Stok</label>
            <input class="form-input-custom stock-input" id="stok" name="stok" type="number" min="1" value="<?= (int) $oldEditBuku['stok']; ?>" required>
            <div class="copy-list-row" id="copyPreview" aria-live="polite"></div>
        </div>
    </form>
</main>

<!-- Script preview jumlah copy/stok buku -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const stokInput = document.getElementById('stok');
    const copyPreview = document.getElementById('copyPreview');

    // Menampilkan preview badge copy berdasarkan input stok.
    function renderCopyPreview() {
        if (!stokInput || !copyPreview) {
            return;
        }

        const total = Math.max(1, parseInt(stokInput.value || '1', 10));
        const visible = Math.min(total, 12);
        copyPreview.innerHTML = '';

        for (let index = 1; index <= visible; index++) {
            const badge = document.createElement('span');
            badge.className = 'copy-badge';
            badge.textContent = 'b' + String(index).padStart(3, '0');
            copyPreview.appendChild(badge);
        }

        if (total > visible) {
            const badge = document.createElement('span');
            badge.className = 'copy-badge';
            badge.textContent = '+' + (total - visible);
            copyPreview.appendChild(badge);
        }
    }

    if (stokInput) {
        stokInput.addEventListener('input', renderCopyPreview);
        renderCopyPreview();
    }
});
</script>
