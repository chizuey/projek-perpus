<?php include_once __DIR__ . '/popup_helper.php'; ?>

<div class="return-confirm-overlay admin-popup" id="popupPerpanjangPeminjaman">
    <div class="return-confirm-box">
        <div class="return-confirm-header">
            <h3>Perpanjang Peminjaman</h3>
            <button type="button" class="return-confirm-close" data-popup-close="popupPerpanjangPeminjaman">&times;</button>
        </div>

        <form method="post" action="actions/peminjaman/update.php">
            <input type="hidden" name="action" value="perpanjang_peminjaman">
            <input type="hidden" name="id" id="perpanjang_id">

            <div class="return-confirm-body">
                <p class="return-confirm-text">Yakin ingin memperpanjang peminjaman buku ini?</p>
                <div class="return-confirm-detail">
                    <span><strong>Judul:</strong> <span id="perpanjang_judul">-</span></span>
                    <span><strong>ID Eksemplar:</strong> <span id="perpanjang_eksemplar">-</span></span>
                </div>
            </div>

            <div class="return-confirm-actions">
                <button type="button" class="btn-return-batal" data-popup-close="popupPerpanjangPeminjaman">Batal</button>
                <button type="submit" class="btn-return-submit">Perpanjang</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaPopupPerpanjangPeminjaman(id, judul, idEksemplar) {
    document.getElementById('perpanjang_id').value = id;
    document.getElementById('perpanjang_judul').innerText = judul;
    document.getElementById('perpanjang_eksemplar').innerText = idEksemplar;
    bukaPopup('popupPerpanjangPeminjaman');
}
</script>
