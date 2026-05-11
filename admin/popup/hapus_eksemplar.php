<?php include_once __DIR__ . '/popup_helper.php'; ?>

<div class="return-confirm-overlay admin-popup" id="popupHapusEksemplar">
    <div class="return-confirm-box">
        <div class="return-confirm-header">
            <h3>Hapus Eksemplar</h3>
            <button type="button" class="return-confirm-close" data-popup-close="popupHapusEksemplar">&times;</button>
        </div>

        <form method="post" action="actions/buku/delete_eksemplar.php">
            <input type="hidden" name="action" value="delete_eksemplar">
            <input type="hidden" name="id" id="hapus_eksemplar_id_buku">
            <div id="hapus_eksemplar_inputs"></div>

            <div class="return-confirm-body">
                <p class="return-confirm-text">Yakin ingin menghapus eksemplar yang dipilih?</p>
                <div class="return-confirm-detail">
                    <span><strong>Jumlah:</strong> <span id="hapus_eksemplar_jumlah">0</span> eksemplar</span>
                    <span><strong>ID Eksemplar:</strong> <span id="hapus_eksemplar_daftar">-</span></span>
                </div>
            </div>

            <div class="return-confirm-actions">
                <button type="button" class="btn-return-batal" data-popup-close="popupHapusEksemplar">Batal</button>
                <button type="submit" class="btn-return-submit btn-danger-submit">Hapus Eksemplar</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaPopupHapusEksemplar(idBuku, daftarId) {
    var tempatInput = document.getElementById('hapus_eksemplar_inputs');
    tempatInput.innerHTML = '';

    for (var i = 0; i < daftarId.length; i++) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'eksemplar_ids[]';
        input.value = daftarId[i];
        tempatInput.appendChild(input);
    }

    document.getElementById('hapus_eksemplar_id_buku').value = idBuku;
    document.getElementById('hapus_eksemplar_jumlah').innerText = daftarId.length;
    document.getElementById('hapus_eksemplar_daftar').innerText = daftarId.join(', ');
    bukaPopup('popupHapusEksemplar');
}
</script>
