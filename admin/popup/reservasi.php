<?php include_once __DIR__ . '/popup_helper.php'; ?>

<div class="return-confirm-overlay admin-popup" id="popupKonfirmasiReservasi">
    <div class="return-confirm-box">
        <div class="return-confirm-header">
            <h3>Konfirmasi Reservasi</h3>
            <button type="button" class="return-confirm-close" data-popup-close="popupKonfirmasiReservasi">&times;</button>
        </div>

        <form method="post" action="actions/reservasi/konfirmasi.php">
            <input type="hidden" name="id" id="konfirmasi_reservasi_id">
            <input type="hidden" name="page" id="konfirmasi_reservasi_page">
            <input type="hidden" name="per_page" id="konfirmasi_reservasi_per_page">
            <input type="hidden" name="q" id="konfirmasi_reservasi_q">
            <input type="hidden" name="status" id="konfirmasi_reservasi_status">

            <div class="return-confirm-body">
                <p class="return-confirm-text">Yakin ingin mengkonfirmasi reservasi ini?</p>
                <div class="return-confirm-detail">
                    <span><strong>Nama Anggota:</strong> <span id="konfirmasi_reservasi_nama">-</span></span>
                    <span><strong>Judul Buku:</strong> <span id="konfirmasi_reservasi_buku">-</span></span>
                </div>
            </div>

            <div class="return-confirm-actions">
                <button type="button" class="btn-return-batal" data-popup-close="popupKonfirmasiReservasi">Batal</button>
                <button type="submit" class="btn-return-submit btn-green-submit">Konfirmasi</button>
            </div>
        </form>
    </div>
</div>

<div class="return-confirm-overlay admin-popup" id="popupBatalkanReservasi">
    <div class="return-confirm-box">
        <div class="return-confirm-header">
            <h3>Batalkan Reservasi</h3>
            <button type="button" class="return-confirm-close" data-popup-close="popupBatalkanReservasi">&times;</button>
        </div>

        <form method="post" action="actions/reservasi/batalkan.php">
            <input type="hidden" name="id" id="batalkan_reservasi_id">
            <input type="hidden" name="page" id="batalkan_reservasi_page">
            <input type="hidden" name="per_page" id="batalkan_reservasi_per_page">
            <input type="hidden" name="q" id="batalkan_reservasi_q">
            <input type="hidden" name="status" id="batalkan_reservasi_status">

            <div class="return-confirm-body">
                <p class="return-confirm-text">Yakin ingin membatalkan reservasi ini?</p>
                <div class="return-confirm-detail">
                    <span><strong>Nama Anggota:</strong> <span id="batalkan_reservasi_nama">-</span></span>
                    <span><strong>Judul Buku:</strong> <span id="batalkan_reservasi_buku">-</span></span>
                </div>
            </div>

            <div class="return-confirm-actions">
                <button type="button" class="btn-return-batal" data-popup-close="popupBatalkanReservasi">Batal</button>
                <button type="submit" class="btn-return-submit btn-danger-submit">Batalkan</button>
            </div>
        </form>
    </div>
</div>

<script>
function isiDataPopupReservasi(prefix, id, nama, buku, page, perPage, q, status) {
    document.getElementById(prefix + '_id').value = id;
    document.getElementById(prefix + '_nama').innerText = nama;
    document.getElementById(prefix + '_buku').innerText = buku;
    document.getElementById(prefix + '_page').value = page;
    document.getElementById(prefix + '_per_page').value = perPage;
    document.getElementById(prefix + '_q').value = q;
    document.getElementById(prefix + '_status').value = status;
}

function bukaPopupKonfirmasiReservasi(id, nama, buku, page, perPage, q, status) {
    isiDataPopupReservasi('konfirmasi_reservasi', id, nama, buku, page, perPage, q, status);
    bukaPopup('popupKonfirmasiReservasi');
}

function bukaPopupBatalkanReservasi(id, nama, buku, page, perPage, q, status) {
    isiDataPopupReservasi('batalkan_reservasi', id, nama, buku, page, perPage, q, status);
    bukaPopup('popupBatalkanReservasi');
}
</script>
