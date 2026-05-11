<?php include_once __DIR__ . '/popup/popup_helper.php'; ?>

<!-- Popup form tambah peminjaman baru -->
<div class="popup-overlay <?= $openPopup ? 'active' : ''; ?>" id="popupPeminjaman">
    <div class="popup-box">
        <div class="popup-header">
            <span>Tambah Peminjaman</span>
            <button type="button" class="popup-close" data-popup-close="popupPeminjaman" aria-label="Tutup">&times;</button>
        </div>

        <!-- Form create peminjaman, diproses oleh halaman peminjaman -->
        <form method="post" action="actions/peminjaman/store.php">
            <div class="popup-body">
                <?php if (!empty($errors)): ?>
                    <div class="popup-alert">
                        <?= e(implode(' ', $errors)); ?>
                    </div>
                <?php endif; ?>

                <!-- Penanda action agar handler POST tahu proses yang diminta -->
                <input type="hidden" name="action" value="add_peminjaman">
                <input type="hidden" name="per_page" value="<?= (int) ($perPage ?? 7); ?>">

                <div class="form-group">
                    <label for="popup_nim">NIM</label>
                    <input
                        type="text"
                        id="popup_nim"
                        name="nim"
                        value="<?= e($oldInput['nim'] ?? ''); ?>"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="popup_nama">Nama</label>
                    <input
                        type="text"
                        id="popup_nama"
                        name="nama"
                        value="<?= e($oldInput['nama'] ?? ''); ?>"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="buku1">ID Eksemplar 1 (Wajib)</label>
                    <input 
                        type="text" 
                        name="buku1" 
                        id="buku1" 
                        placeholder="Masukkan ID Eksemplar (Baris 1)" 
                        class="form-control"
                        value="<?= e($oldInput['buku1'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="buku2">ID Eksemplar 2 (Opsional)</label>
                    <input 
                        type="text" 
                        name="buku2" 
                        id="buku2" 
                        placeholder="Masukkan ID Eksemplar (Baris 2)" 
                        class="form-control"
                        value="<?= e($oldInput['buku2'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="buku3">ID Eksemplar 3 (Opsional)</label>
                    <input 
                        type="text" 
                        name="buku3" 
                        id="buku3" 
                        placeholder="Masukkan ID Eksemplar (Baris 3)" 
                        class="form-control"
                        value="<?= e($oldInput['buku3'] ?? ''); ?>"
                    >
                </div>
                <?php
                $tglPinjamValue = !empty($oldInput['tgl_pinjam']) ? $oldInput['tgl_pinjam'] : todayDate();
                $tglKembaliValue = !empty($oldInput['tgl_kembali']) ? $oldInput['tgl_kembali'] : defaultTanggalKembali();
                ?>

                <!-- Tanggal otomatis, hanya ditampilkan sebagai readonly -->
                <div class="form-row">
                    <div class="form-date">
                        <label for="popup_tgl_pinjam_view">Tanggal Pinjam</label>
                        <input
                            type="text"
                            id="popup_tgl_pinjam_view"
                            value="<?= e(date('d-m-Y', strtotime($tglPinjamValue))); ?>"
                            readonly
                        >
                    </div>

                    <div class="form-date">
                        <label for="popup_tgl_kembali_view">Tanggal Kembali</label>
                        <input
                            type="text"
                            id="popup_tgl_kembali_view"
                            value="<?= e(date('d-m-Y', strtotime($tglKembaliValue))); ?>"
                            readonly
                        >
                    </div>
                </div>

                <div class="popup-footer">
                    <button type="button" class="btn-batal" data-popup-close="popupPeminjaman">Batal</button>
                    <button type="submit" class="btn-simpan">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Logika untuk menutup popup peminjaman
document.addEventListener('DOMContentLoaded', function() {
    const pesanError = <?= json_encode(!empty($errors) ? implode(' ', $errors) : ''); ?>;

    if (pesanError) alert(pesanError);
});
</script>
