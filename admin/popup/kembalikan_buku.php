<?php include_once __DIR__ . '/popup_helper.php'; ?>

<div class="return-confirm-overlay admin-popup" id="popupKembalikanBuku">
    <div class="return-confirm-box">
        <div class="return-confirm-header">
            <h3>Kembalikan Buku</h3>
            <button type="button" class="return-confirm-close" data-popup-close="popupKembalikanBuku">&times;</button>
        </div>

        <form method="post" action="actions/peminjaman/delete.php">
            <input type="hidden" name="action" value="kembalikan_peminjaman">
            <input type="hidden" name="id" id="kembalikan_id">

            <div class="return-confirm-body">
                <p class="return-confirm-text">Yakin ingin menandai buku ini sudah dikembalikan?</p>
                <div class="return-confirm-detail">
                    <span><strong>Judul:</strong> <span id="kembalikan_judul">-</span></span>
                    <span><strong>ID Eksemplar:</strong> <span id="kembalikan_eksemplar">-</span></span>
                    <span><strong>Status:</strong> <span id="kembalikan_status">-</span></span>
                    <span><strong>Denda:</strong> <span id="kembalikan_denda">-</span></span>
                </div>
            </div>

            <div class="return-confirm-actions">
                <button type="button" class="btn-return-batal" data-popup-close="popupKembalikanBuku">Batal</button>
                <button type="submit" class="btn-return-submit">Kembalikan</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaPopupKembalikanBuku(id, judul, idEksemplar, status, denda) {
    document.getElementById('kembalikan_id').value = id;
    document.getElementById('kembalikan_judul').innerText = judul;
    document.getElementById('kembalikan_eksemplar').innerText = idEksemplar;
    document.getElementById('kembalikan_status').innerText = status;
    document.getElementById('kembalikan_denda').innerText = denda;
    bukaPopup('popupKembalikanBuku');
}
</script>
