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
        // Set data dasar
        document.getElementById('popIdBuku').value = idBuku || '';
        document.getElementById('popTitle').innerText = judul || "Judul Tidak Tersedia";
        document.getElementById('popKategori').innerText = kategori || "Umum";
        document.getElementById('popImg').src = img || "../public/img/buku.png";
    
        const descText = (deskripsi && deskripsi !== 'null' && deskripsi !== '') 
                         ? deskripsi 
                         : "Tidak ada deskripsi untuk buku ini.";
        document.getElementById('popDesc').innerText = descText;

        // UPDATE STOK DAN STATUS (Tambahan Baru)
        const statusText = document.getElementById('popStatusText');
        const statusCount = document.getElementById('popStok');
        
        // Pastikan stok adalah angka
        const jumlahStok = parseInt(stok) || 0;
        
        if (statusCount) {
            statusCount.innerText = jumlahStok;
        }

        if (statusText) {
            if (jumlahStok <= 0) {
                statusText.innerText = "Tidak Tersedia";
            } else {
                statusText.innerText = "Tersedia";
            }
        }

        // Tampilkan Modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function tutupPopup() {
    const modal = document.getElementById('modalDetail');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Kembalikan scroll body
    }
}


function submitReservasi(event) {
    event.preventDefault();
    
    const idBuku = document.getElementById('popIdBuku').value;
    
    if (!idBuku) {
        alert('ID Buku tidak ditemukan');
        return;
    }

    // Show loading state
    const btn = event.target;
    const originalText = btn.innerText;
    btn.innerText = 'Memproses...';
    btn.disabled = true;

    // Submit via AJAX
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