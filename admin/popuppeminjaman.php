<!-- Popup form tambah peminjaman baru -->
<div class="popup-overlay <?= $openPopup ? 'active' : ''; ?>" id="popupPeminjaman">
    <div class="popup-box">
        <div class="popup-header">
            <span>Tambah Peminjaman</span>
            <button type="button" class="popup-close" id="closePopupPeminjaman" aria-label="Tutup">&times;</button>
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

        <div class="mb-3">
    <label for="buku" class="form-label">Buku</label>
    <select name="buku" id="buku" class="form-select" required>
        <option value="" selected disabled>Pilih Buku</option>
        
        <?php if (!empty($opsiBuku)): ?>
            <?php foreach ($opsiBuku as $b): ?>
                <option value="<?= e($b['judul']); ?>" <?= $b['stok_tersedia'] <= 0 ? 'disabled' : ''; ?>>
                    <?= e($b['judul']); ?> (Stok: <?= $b['stok_tersedia']; ?>)
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
        
    </select>
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
                    <button type="button" class="btn-batal" id="batalPopupPeminjaman">Batal</button>
                    <button type="submit" class="btn-simpan">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
