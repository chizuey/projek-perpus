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
    <?php if (!empty($successEditBuku)): ?>
        <div class="form-alert form-success">
            <?= eEditBuku($successEditBuku); ?>
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

            <div class="form-actions" style="margin-top: 1rem;">
                <button type="submit" class="btn-tambahkan">Simpan Perubahan</button>
                <a href="?menu=databuku" class="btn-batal-form" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">Batal</a>
            </div>
        </div>
    </form>

    <div class="panel-card">
        <div class="copy-panel-title">Eksemplar Buku</div>
        <p class="eksemplar-help">Klik ID eksemplar untuk memilih, lalu klik Hapus Eksemplar.</p>

        <form method="post" action="actions/buku/add_eksemplar.php" class="eksemplar-inline-form">
            <input type="hidden" name="action" value="add_eksemplar">
            <input type="hidden" name="id" value="<?= (int) $bookId; ?>">
            <button type="submit" class="btn-tambah-copy">Tambah Eksemplar</button>
        </form>

        <form method="post" action="actions/buku/delete_eksemplar.php" id="hapusEksemplarPickerForm" data-id-buku="<?= (int) $bookId; ?>">
            <input type="hidden" name="action" value="delete_eksemplar">
            <input type="hidden" name="id" value="<?= (int) $bookId; ?>">

            <div class="eksemplar-grid">
                <?php if (empty($eksemplarList)): ?>
                    <span class="eksemplar-empty">Belum ada eksemplar.</span>
                <?php else: ?>
                    <?php foreach ($eksemplarList as $eksemplar): ?>
                        <?php $isTersedia = $eksemplar['status'] === 'tersedia'; ?>
                        <label class="eksemplar-item <?= $isTersedia ? '' : 'disabled'; ?>">
                            <input
                                type="checkbox"
                                name="eksemplar_ids[]"
                                value="<?= (int) $eksemplar['id_eksemplar']; ?>"
                                <?= $isTersedia ? '' : 'disabled'; ?>
                            >
                            <span>ID <?= (int) $eksemplar['id_eksemplar']; ?></span>
                            <small><?= eEditBuku($eksemplar['status']); ?></small>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-hapus-eksemplar">Hapus Eksemplar</button>
        </form>
    </div>
</main>

<?php include __DIR__ . '/../popup/hapus_eksemplar.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pickerForm = document.getElementById('hapusEksemplarPickerForm');
    if (!pickerForm) return;

    pickerForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const checked = Array.from(
            pickerForm.querySelectorAll('input[name="eksemplar_ids[]"]:checked')
        );

        if (checked.length === 0) {
            alert('Pilih minimal satu eksemplar yang tersedia.');
            return;
        }

        bukaPopupHapusEksemplar(
            pickerForm.dataset.idBuku,
            checked.map(input => input.value)
        );
    });
});
</script>
