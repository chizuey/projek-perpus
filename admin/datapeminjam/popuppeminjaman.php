<div id="popupPeminjaman" class="popup-overlay <?php echo $openPopup ? 'active' : ''; ?>">
    <div class="popup-box">
        <div class="popup-header">
            <span>Tambah Peminjam</span>
            <button type="button" id="closePopupPeminjaman" class="popup-close">&times;</button>
        </div>

        <!--
        |--------------------------------------------------------------------------
        | FORM TAMBAH PEMINJAMAN
        |--------------------------------------------------------------------------
        | action = add_peminjaman
        -->
        <form class="popup-body" method="post" action="">
            <input type="hidden" name="action" value="add_peminjaman">

            <!--
            |--------------------------------------------------------------------------
            | TAMPILKAN ERROR VALIDASI
            |--------------------------------------------------------------------------
            -->
            <?php if (!empty($errors)): ?>
                <div class="popup-alert">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="popup_nim">NIM</label>
                <input type="text" id="popup_nim" name="nim" value="<?php echo htmlspecialchars($oldInput['nim']); ?>">
            </div>

            <div class="form-group">
                <label for="popup_nama">Nama</label>
                <input type="text" id="popup_nama" name="nama" value="<?php echo htmlspecialchars($oldInput['nama']); ?>">
            </div>

            <!--
            |--------------------------------------------------------------------------
            | PILIH BUKU DARI DAFTAR BUKU
            |--------------------------------------------------------------------------
            | sumber data: getDaftarBuku()
            -->
            <div class="form-group">
                <label for="popup_buku">Buku</label>
                <select id="popup_buku" name="buku">
                    <option value=""></option>
                    <?php foreach (getDaftarBuku() as $bukuOption => $stokTotal): ?>
                        <option
                            value="<?php echo htmlspecialchars($bukuOption); ?>"
                            <?php echo $oldInput['buku'] === $bukuOption ? 'selected' : ''; ?>
                        >
                            <?php echo htmlspecialchars($bukuOption); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!--
            |--------------------------------------------------------------------------
            | INPUT TANGGAL PINJAM DAN TANGGAL KEMBALI
            |--------------------------------------------------------------------------
            -->
            <div class="form-row">
                <div class="form-date">
                    <label for="popup_tgl_pinjam">Tanggal Pinjam</label>
                    <input type="date" id="popup_tgl_pinjam" name="tgl_pinjam" value="<?php echo htmlspecialchars($oldInput['tgl_pinjam']); ?>">
                </div>

                <div class="form-date">
                    <label for="popup_tgl_kembali">Tanggal Kembali</label>
                    <input type="date" id="popup_tgl_kembali" name="tgl_kembali" value="<?php echo htmlspecialchars($oldInput['tgl_kembali']); ?>">
                </div>
            </div>

            <!--
            |--------------------------------------------------------------------------
            | TOMBOL AKSI POPUP
            |--------------------------------------------------------------------------
            -->
            <div class="popup-footer">
                <button type="button" id="batalPopupPeminjaman" class="btn-batal">Batal</button>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>