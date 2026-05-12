<div class="bd-modal-overlay" id="modalDetail" style="display: none;">
    <div class="bd-modal-content">
        <header class="bd-header">
            <div class="bd-back-btn" onclick="tutupPopup()">
                <i class="fas fa-chevron-left"></i>
            </div>
            <span style="margin-left: 15px; font-weight: 600; color: #1e293b;">Informasi Buku</span>
        </header>

        <div class="bd-main-flex-auto">
            <div class="bd-cover-wrapper">
                <img id="popImg" src="" class="bd-img-auto" alt="Cover Buku">
            </div>

            <div class="bd-detail-wrapper">
                <h2 id="popTitle" class="bd-title"></h2>
                <div class="bd-meta-row">
                    <span id="popKategori" class="bd-tag"></span>
                </div>
                
                <div class="bd-desc-container">
                    <p id="popDesc" class="bd-description"></p>
                </div>
<div class="bd-action-area">
    <div class="bd-status-box">
        <span class="bd-status-text" id="popStatusText">Tersedia</span>
        <span class="bd-status-count" id="popStok">0</span> 
    </div>

    <form id="formReservasi" class="reservasi-form">
        <input type="hidden" name="id_buku" id="popIdBuku" value="">
        <button type="button" class="bd-btn-reservasi" onclick="submitReservasi(event)">RESERVASI</button>
    </form>
</div>
            </div>
        </div>
    </div>
</div>

<script>

function bukaPopup(idBuku, judul, kategori, img, deskripsi, stok) {
    const modal = document.getElementById('modalDetail');

    if (modal) {
        // 1. Isi data ke dalam elemen popup (dengan pengecekan elemen agar tidak crash)
        const elId = document.getElementById('popIdBuku');
        const elTitle = document.getElementById('popTitle');
        const elKategori = document.getElementById('popKategori');
        const elImg = document.getElementById('popImg');
        const elDesc = document.getElementById('popDesc');
        const elStok = document.getElementById('popStok');
        const elStatus = document.getElementById('popStatusText');

        if (elId) elId.value = idBuku || '';
        if (elTitle) elTitle.innerText = judul || "Judul Tidak Tersedia";
        if (elKategori) elKategori.innerText = kategori || "Umum";
        if (elImg) elImg.src = img || "../public/img/buku.png";
        if (elStok) elStok.innerText = stok || '0';

        if (elDesc) {
            elDesc.innerText = (deskripsi && deskripsi !== 'null' && deskripsi !== '') 
                               ? deskripsi 
                               : "Tidak ada deskripsi untuk buku ini.";
        }

        const jumlahStok = parseInt(stok) || 0;

        // 2. Update Status Teks
        if (elStatus) {
            elStatus.innerText = (jumlahStok <= 0) ? "Tidak Tersedia" : "Tersedia";
            elStatus.style.color = (jumlahStok <= 0) ? "#e11d48" : "#22c55e";
        }

        // 3. Logika Tombol Reservasi
        const btnReservasi = document.querySelector('.bd-btn-reservasi');
        if (btnReservasi) {
            if (jumlahStok <= 0) {
                btnReservasi.innerText = "STOK HABIS";
                btnReservasi.disabled = true;
                btnReservasi.style.opacity = "0.5";
                btnReservasi.style.cursor = "not-allowed";
                btnReservasi.style.backgroundColor = "#94a3b8";
            } else {
                btnReservasi.innerText = "RESERVASI";
                btnReservasi.disabled = false;
                btnReservasi.style.opacity = "1";
                btnReservasi.style.cursor = "pointer";
                btnReservasi.style.backgroundColor = ""; 
            }
        }

        // 4. Tampilkan Popup (Pastikan display flex/block sesuai CSS-mu)
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function tutupPopup() {
    const modal = document.getElementById('modalDetail');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function submitReservasi(event) {
    event.preventDefault();
    
    const idBuku = document.getElementById('popIdBuku').value;
    const elStok = document.getElementById('popStok');
    const stokSekarang = elStok ? parseInt(elStok.innerText) : 0;

    // Proteksi jika stok habis tapi fungsi dipicu
    if (stokSekarang <= 0) {
        alert('Maaf, stok buku tidak tersedia.');
        return;
    }

    if (!idBuku) {
        alert('ID Buku tidak ditemukan');
        return;
    }

    // Ambil tombol dengan benar (event.currentTarget lebih aman daripada event.target)
    const btn = event.currentTarget;
    const originalText = btn.innerText;
    
    btn.innerText = 'Memproses...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('id_buku', idBuku);

    fetch('actions/reservasi/create.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.innerText = originalText;
        btn.disabled = false;

        if (data.success) {
            alert(data.message);
            tutupPopup();
            location.reload(); 
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        btn.innerText = originalText;
        btn.disabled = false;
        alert('Error: ' + error.message);
    });
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('modalDetail');
    if (e.target === modal) {
        tutupPopup();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === "Escape") {
        tutupPopup();
    }
});
</script>