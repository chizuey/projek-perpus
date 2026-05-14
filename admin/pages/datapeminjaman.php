<?php
// 1. Persiapan Data (Memanggil Controller)
require_once __DIR__ . '/../../controllers/PeminjamanController.php';
$peminjamanController = new PeminjamanController();
$data = $peminjamanController->index();

// Mengeluarkan data agar bisa langsung dipakai (Contoh: $search, $pageData, dll)
extract($data, EXTR_SKIP);
?>

<div class="datapeminjam-wrapper">
    
    <!-- Bagian Header: Judul Halaman -->
    <div class="datapeminjam-header">
        <h1>Data Peminjam</h1>
    </div>

    <!-- Bagian Toolbar: Tombol Tambah & Kolom Pencarian -->
    <div class="toolbar">
        <div class="toolbar-left">
            <!-- Tombol untuk membuka popup tambah pinjaman -->
            <button type="button" class="btn-tambah" id="btnBukaPopup">
                <span class="plus-icon">+</span> Tambah Peminjaman
            </button>

            <!-- Form Pencarian -->
            <form method="get" class="search-form">
                <input type="hidden" name="menu" value="peminjaman">
                <div class="search-box">
                    <input type="text" name="q" placeholder="Cari NIM atau Nama..." value="<?= htmlspecialchars($search); ?>">
                    <button type="submit" class="search-submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bagian Tabel: Menampilkan Daftar Transaksi -->
    <div class="table-container">
        <table class="datapeminjam-table">
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama Mahasiswa</th>
                    <th>Tgl Pinjam</th>
                    <th>Batas Kembali</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pageData)): ?>
                    <?php foreach ($pageData as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nim']); ?></td>
                            <td><?= htmlspecialchars($row['nama']); ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal_peminjaman'])); ?></td>
                            <td><?= date('d/m/Y', strtotime($row['batas_waktu'])); ?></td>
                            <td style="text-align: center;">
                                <!-- Tombol Detail untuk melihat buku apa saja yang dipinjam -->
                                <button type="button" class="btn-detail js-tombol-detail" 
                                        data-id="<?= $row['id']; ?>" 
                                        data-nama="<?= $row['nama']; ?>">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-state">Belum ada data peminjaman.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bagian Footer: Informasi Data & Navigasi Halaman -->
    <div class="datapeminjam-footer">
        <div class="data-info">
            Menampilkan <?= $startDisplay; ?>-<?= $endDisplay; ?> dari <?= $totalData; ?> data
        </div>
        
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?menu=peminjaman&page=<?= $i; ?>&q=<?= $search; ?>" 
                   class="page-btn <?= ($i == $currentPage) ? 'active' : ''; ?>">
                    <?= $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- ======================================================
     MODAL POPUP: DETAIL PEMINJAMAN (Melihat Buku)
     ====================================================== -->
<div class="return-confirm-overlay" id="modalDetail">
    <div class="return-confirm-box">
        <div class="return-confirm-header">
            <h3>Daftar Buku: <span id="txtNamaPeminjam"></span></h3>
            <button type="button" class="return-confirm-close" id="btnTutupModalX">&times;</button>
        </div>

        <div class="return-confirm-body" style="max-height: 60vh; overflow-y: auto;">
            <table class="datapeminjam-table" style="min-width: 100%;">
                <thead>
                    <tr>
                        <th>ID Eks</th>
                        <th>Judul Buku</th>
                        <th>Denda</th>
                        <th>Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tabelDetailBuku">
                    <!-- Data buku akan muncul di sini secara otomatis -->
                </tbody>
            </table>
        </div>

        <div class="return-confirm-actions">
            <button type="button" class="btn-return-batal" id="btnTutupModal">Tutup</button>
        </div>
    </div>
</div>

<!-- Sertakan popup untuk tambah peminjaman baru -->
<?php include __DIR__ . '/../popuppeminjaman.php'; ?>
<?php include __DIR__ . '/../popup/perpanjang_peminjaman.php'; ?>
<?php include __DIR__ . '/../popup/kembalikan_buku.php'; ?>

<!-- ======================================================
     JAVASCRIPT: LOGIKA INTERAKSI HALAMAN
     ====================================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Variabel Elemen Modal Detail
    const modalDetail = document.getElementById('modalDetail');
    const bodiTabel = document.getElementById('tabelDetailBuku');
    const txtNama = document.getElementById('txtNamaPeminjam');

    const escapeAttr = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    // 2. Fungsi Membuka Modal Detail & Ambil Data via AJAX
    function bukaDetail(id, nama) {
        txtNama.textContent = nama;
        bodiTabel.innerHTML = '<tr><td colspan="5" style="text-align:center;">Mengambil data...</td></tr>';
        modalDetail.classList.add('show');
        document.body.classList.add('modal-open');

        // Mengambil data detail dari file PHP lain
        fetch('actions/peminjaman/get_details.php?id=' + id)
            .then(response => response.json())
            .then(listBuku => {
                bodiTabel.innerHTML = '';
                
                if (listBuku.length === 0) {
                    bodiTabel.innerHTML = '<tr><td colspan="5" style="text-align:center;">Buku sudah dikembalikan semua.</td></tr>';
                    return;
                }

                listBuku.forEach(buku => {
                    // Sembunyikan buku yang sudah dikembalikan di dalam popup ini
                    if (buku.status_pengembalian === 'kembali') return;

                    const row = `
                        <tr>
                            <td>${buku.id_eksemplar}</td>
                            <td>${buku.judul}</td>
                            <td>${buku.denda_teks}</td>
                            <td><span class="status-badge status-${buku.status_teks.toLowerCase()}">${buku.status_teks}</span></td>
                            <td style="text-align: center;">
                                <div class="peminjaman-action-group">
                                    ${!buku.extended_at ? 
                                        `<button
                                            type="button"
                                            class="btn-perpanjang btn-sm"
                                            data-action-peminjaman="perpanjang"
                                            data-id="${escapeAttr(buku.id_detail)}"
                                            data-judul="${escapeAttr(buku.judul)}"
                                            data-id-eksemplar="${escapeAttr(buku.id_eksemplar)}"
                                        >Perpanjang</button>` :
                                        '<span class="extend-used-label">Sudah Diperpanjang</span>'
                                    }
                                    <button
                                        type="button"
                                        class="btn-kembalikan btn-sm"
                                        data-action-peminjaman="kembalikan"
                                        data-id="${escapeAttr(buku.id_detail)}"
                                        data-judul="${escapeAttr(buku.judul)}"
                                        data-id-eksemplar="${escapeAttr(buku.id_eksemplar)}"
                                        data-status="${escapeAttr(buku.status_teks)}"
                                        data-denda="${escapeAttr(buku.denda_teks)}"
                                    >Kembalikan</button>
                                </div>
                            </td>
                        </tr>
                    `;
                    bodiTabel.innerHTML += row;
                });
            });
    }

    bodiTabel.addEventListener('click', function (event) {
        const button = event.target.closest('[data-action-peminjaman]');
        if (!button) return;

        if (button.dataset.actionPeminjaman === 'perpanjang') {
            bukaPopupPerpanjangPeminjaman(
                button.dataset.id,
                button.dataset.judul,
                button.dataset.idEksemplar
            );
            return;
        }

        bukaPopupKembalikanBuku(
            button.dataset.id,
            button.dataset.judul,
            button.dataset.idEksemplar,
            button.dataset.status,
            button.dataset.denda
        );
    });

    // 3. Menghubungkan Tombol Detail di Tabel
    document.querySelectorAll('.js-tombol-detail').forEach(btn => {
        btn.onclick = function() {
            bukaDetail(this.dataset.id, this.dataset.nama);
        };
    });

    // 4. Fungsi Tutup Modal
    const tutupModal = () => {
        modalDetail.classList.remove('show');
        document.body.classList.remove('modal-open');
    };
    document.getElementById('btnTutupModal').onclick = tutupModal;
    document.getElementById('btnTutupModalX').onclick = tutupModal;

    // 5. Popup Tambah Peminjaman (Logika Sederhana)
    document.getElementById('btnBukaPopup').onclick = () => {
        bukaPopup('popupPeminjaman');
    };
});

</script>
