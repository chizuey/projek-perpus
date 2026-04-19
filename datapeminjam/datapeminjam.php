<?php
$dataPeminjaman = [
    [
        'nim' => '123456',
        'nama' => 'Fajar',
        'buku' => 'Pemrograman Web',
        'tanggal_pinjam' => '01 Apr 2024',
        'tanggal_kembali' => '10 Apr 2024',
        'terlambat' => '-',
        'denda' => 'Rp 0',
        'status' => 'Dipinjam',
        'aksi' => 'Kembalikan'
    ],
    [
        'nim' => '123457',
        'nama' => 'Dina',
        'buku' => 'Basis Data',
        'tanggal_pinjam' => '28 Mar 2024',
        'tanggal_kembali' => '08 Apr 2024',
        'terlambat' => '-',
        'denda' => 'Rp 0',
        'status' => 'Dipinjam',
        'aksi' => 'Kembalikan'
    ],
    [
        'nim' => '123458',
        'nama' => 'Budi',
        'buku' => 'Jaringan Komputer',
        'tanggal_pinjam' => '20 Mar 2024',
        'tanggal_kembali' => '30 Mar 2024',
        'terlambat' => '2 hari',
        'denda' => 'Rp 4.000',
        'status' => 'Terlambat',
        'aksi' => 'bayar denda'
    ],
    [
        'nim' => '123459',
        'nama' => 'Andi',
        'buku' => 'Sistem Informasi',
        'tanggal_pinjam' => '17 Mar 2024',
        'tanggal_kembali' => '25 Mar 2024',
        'terlambat' => '-',
        'denda' => 'Rp 0',
        'status' => 'Dikembalikan',
        'aksi' => '-'
    ],
    [
        'nim' => '123460',
        'nama' => 'Rani',
        'buku' => 'Pemrograman Java',
        'tanggal_pinjam' => '12 Mar 2024',
        'tanggal_kembali' => '22 Mar 2024',
        'terlambat' => '-',
        'denda' => 'Rp 0',
        'status' => 'Dikembalikan',
        'aksi' => '-'
    ],
    [
        'nim' => '123461',
        'nama' => 'Eko',
        'buku' => 'Teknik Elektro',
        'tanggal_pinjam' => '10 Mar 2024',
        'tanggal_kembali' => '20 Mar 2024',
        'terlambat' => '3 hari',
        'denda' => 'Rp 6.000',
        'status' => 'Terlambat',
        'aksi' => 'bayar denda'
    ],
    [
        'nim' => '123462',
        'nama' => 'Widya',
        'buku' => 'Manajemen Keuangan',
        'tanggal_pinjam' => '05 Mar 2024',
        'tanggal_kembali' => '15 Mar 2024',
        'terlambat' => '-',
        'denda' => 'Rp 0',
        'status' => 'Dipinjam',
        'aksi' => 'Kembalikan'
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peminjam</title>
    <link rel="stylesheet" href="datapeminjam.css">
    <link rel="stylesheet" href="popuppeminjaman.css">
</head>
<body>

<div class="datapeminjam-wrapper">
    <div class="datapeminjam-header">
        <div class="title-group">
            <button class="back-button" type="button" onclick="history.back()">&#8249;</button>
            <h1>Data Peminjam</h1>
        </div>
    </div>

    <div class="toolbar">
    <div class="toolbar-left">
        <button type="button" class="btn-tambah" id="openPopupPeminjaman">
            <span class="plus-icon">+</span>
            Tambah Peminjaman
        </button>

        <div class="search-box">
            <input type="text" placeholder="Cari Peminjaman...">
            <span class="search-icon">&#128269;</span>
        </div>
    </div>

    <div class="toolbar-right">
        <button type="button" class="btn-filter">
            Filter
            <span class="arrow-down">&#709;</span>
        </button>
    </div>
</div>

    <div class="table-container">
        <table class="datapeminjam-table">
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Terlambat</th>
                    <th>Denda</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataPeminjaman as $item): ?>
                    <tr>
                        <td><?php echo $item['nim']; ?></td>
                        <td><?php echo $item['nama']; ?></td>
                        <td><?php echo $item['buku']; ?></td>
                        <td><?php echo $item['tanggal_pinjam']; ?></td>
                        <td><?php echo $item['tanggal_kembali']; ?></td>
                        <td><?php echo $item['terlambat']; ?></td>
                        <td><?php echo $item['denda']; ?></td>
                        <td>
                            <?php
                                $statusClass = '';
                                if ($item['status'] === 'Dipinjam') {
                                    $statusClass = 'status-dipinjam';
                                } elseif ($item['status'] === 'Dikembalikan') {
                                    $statusClass = 'status-dikembalikan';
                                } elseif ($item['status'] === 'Terlambat') {
                                    $statusClass = 'status-terlambat';
                                }
                            ?>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo $item['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($item['aksi'] === 'Kembalikan'): ?>
                                <button type="button" class="aksi-btn aksi-kembalikan">Kembalikan</button>
                            <?php elseif ($item['aksi'] === 'bayar denda'): ?>
                                <button type="button" class="aksi-btn aksi-denda">bayar denda</button>
                            <?php else: ?>
                                <button type="button" class="aksi-btn aksi-disabled">-</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="datapeminjam-footer">
        <div class="data-count">
            Menampilkan 1–7 dari 16 data
        </div>

        <div class="pagination">
            <button type="button" class="page-btn">&laquo;</button>
            <button type="button" class="page-btn active">1</button>
            <button type="button" class="page-btn">2</button>
            <button type="button" class="page-btn">5</button>
            <button type="button" class="page-btn">...</button>
            <button type="button" class="page-btn">&raquo;</button>
        </div>
    </div>
</div>

<?php include 'popuppeminjaman.php'; ?>

<script>
const openPopupPeminjaman = document.getElementById('openPopupPeminjaman');
const popupPeminjaman = document.getElementById('popupPeminjaman');
const closePopupPeminjaman = document.getElementById('closePopupPeminjaman');
const batalPopupPeminjaman = document.getElementById('batalPopupPeminjaman');

if (openPopupPeminjaman) {
    openPopupPeminjaman.addEventListener('click', function () {
        popupPeminjaman.classList.add('active');
    });
}

if (closePopupPeminjaman) {
    closePopupPeminjaman.addEventListener('click', function () {
        popupPeminjaman.classList.remove('active');
    });
}

if (batalPopupPeminjaman) {
    batalPopupPeminjaman.addEventListener('click', function () {
        popupPeminjaman.classList.remove('active');
    });
}

if (popupPeminjaman) {
    popupPeminjaman.addEventListener('click', function (e) {
        if (e.target === popupPeminjaman) {
            popupPeminjaman.classList.remove('active');
        }
    });
}
</script>

</body>
</html>