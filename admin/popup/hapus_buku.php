<?php include_once __DIR__ . '/popup_helper.php'; ?>

<div class="return-confirm-overlay admin-popup" id="popupHapusBuku">
    <div class="return-confirm-box">
        <div class="return-confirm-header">
            <h3>Hapus Buku</h3>
            <button type="button" class="return-confirm-close" data-popup-close="popupHapusBuku">&times;</button>
        </div>

        <form method="post" action="actions/buku/delete.php">
            <input type="hidden" name="action" value="delete_buku">
            <input type="hidden" name="id" id="hapus_buku_id">
            <input type="hidden" name="page" id="hapus_buku_page">
            <input type="hidden" name="q" id="hapus_buku_q">
            <input type="hidden" name="kategori_filter" id="hapus_buku_kategori">
            <input type="hidden" name="per_page" id="hapus_buku_per_page">

            <div class="return-confirm-body">
                <p class="return-confirm-text">Yakin ingin menghapus buku ini?</p>
                <div class="return-confirm-detail">
                    <span><strong>Judul:</strong> <span id="hapus_buku_judul">-</span></span>
                    <span>Data buku dan eksemplar terkait akan dihapus.</span>
                </div>
            </div>

            <div class="return-confirm-actions">
                <button type="button" class="btn-return-batal" data-popup-close="popupHapusBuku">Batal</button>
                <button type="submit" class="btn-return-submit btn-danger-submit">Hapus Buku</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaPopupHapusBuku(id, judul, page, q, kategori, perPage) {
    document.getElementById('hapus_buku_id').value = id;
    document.getElementById('hapus_buku_judul').innerText = judul;
    document.getElementById('hapus_buku_page').value = page;
    document.getElementById('hapus_buku_q').value = q;
    document.getElementById('hapus_buku_kategori').value = kategori;
    document.getElementById('hapus_buku_per_page').value = perPage;
    bukaPopup('popupHapusBuku');
}
</script>
