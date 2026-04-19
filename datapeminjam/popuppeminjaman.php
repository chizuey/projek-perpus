<div id="popupPeminjaman" class="popup-overlay">
    <div class="popup-box">
        <div class="popup-header">
            <span>Tambah Peminjam</span>
            <button type="button" id="closePopupPeminjaman" class="popup-close">&times;</button>
        </div>

        <form class="popup-body" method="post" action="">
            <div class="form-group">
                <label for="popup_nim">NIM</label>
                <input type="text" id="popup_nim" name="nim">
            </div>

            <div class="form-group">
                <label for="popup_nama">Nama</label>
                <input type="text" id="popup_nama" name="nama">
            </div>

            <div class="form-group">
                <label for="popup_buku">Buku</label>
                <select id="popup_buku" name="buku">
                    <option value=""></option>
                    <option value="Algoritma">Algoritma</option>
                    <option value="Basis Data">Basis Data</option>
                    <option value="Jaringan">Jaringan</option>
                    <option value="AI">AI</option>
                    <option value="Web Dev">Web Dev</option>
                    <option value="Python">Python</option>
                    <option value="UI/UX">UI/UX</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-date">
                    <label for="popup_tgl_pinjam">Tanggal Pinjam</label>
                    <input type="date" id="popup_tgl_pinjam" name="tgl_pinjam">
                </div>

                <div class="form-date">
                    <label for="popup_tgl_kembali">Tanggal Kembali</label>
                    <input type="date" id="popup_tgl_kembali" name="tgl_kembali">
                </div>
            </div>

            <div class="popup-footer">
                <button type="button" id="batalPopupPeminjaman" class="btn-batal">Batal</button>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>