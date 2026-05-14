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
                    <span id="hapus_buku_warning_text">Data buku dan eksemplar terkait akan dihapus.</span>
                </div>
                
                <!-- Alert/Peringatan jika ada yang dipinjam/reservasi -->
                <div id="hapus_buku_alert" class="form-alert" style="display: none; margin-top: 15px; background-color: #fff1f1; border: 1px solid #f0b8b8; color: #991b1b; padding: 10px; border-radius: 8px; font-size: 0.85rem;">
                    <i class="bi bi-exclamation-triangle-fill" style="margin-right: 5px;"></i>
                    <span id="hapus_buku_alert_message"></span>
                </div>
            </div>

            <div class="return-confirm-actions">
                <button type="button" class="btn-return-batal" data-popup-close="popupHapusBuku">Batal</button>
                <button type="submit" class="btn-return-submit btn-danger-submit" id="btn_hapus_buku_submit">Hapus Buku</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaPopupHapusBuku(id, judul, page, q, kategori, perPage, dipinjam = 0, direservasi = 0) {
    document.getElementById('hapus_buku_id').value = id;
    document.getElementById('hapus_buku_judul').innerText = judul;
    document.getElementById('hapus_buku_page').value = page;
    document.getElementById('hapus_buku_q').value = q;
    document.getElementById('hapus_buku_kategori').value = kategori;
    document.getElementById('hapus_buku_per_page').value = perPage;
    
    const alertBox = document.getElementById('hapus_buku_alert');
    const alertMsg = document.getElementById('hapus_buku_alert_message');
    const submitBtn = document.getElementById('btn_hapus_buku_submit');
    const warningText = document.getElementById('hapus_buku_warning_text');
    
    dipinjam = parseInt(dipinjam) || 0;
    direservasi = parseInt(direservasi) || 0;
    
    if (dipinjam > 0 || direservasi > 0) {
        alertBox.style.display = 'block';
        let msg = "Buku tidak bisa dihapus karena masih ada ";
        if (dipinjam > 0 && direservasi > 0) {
            msg += `<strong>${dipinjam} eksemplar dipinjam</strong> dan <strong>${direservasi} eksemplar direservasi</strong>.`;
        } else if (dipinjam > 0) {
            msg += `<strong>${dipinjam} eksemplar dipinjam</strong>.`;
        } else {
            msg += `<strong>${direservasi} eksemplar direservasi</strong>.`;
        }
        alertMsg.innerHTML = msg;
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
        warningText.style.display = 'none';
    } else {
        alertBox.style.display = 'none';
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
        warningText.style.display = 'block';
    }
    
    bukaPopup('popupHapusBuku');
}
</script>
