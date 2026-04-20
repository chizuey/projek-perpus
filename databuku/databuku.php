<?php
$buku = [
    ["judul"=>"Laskar Pelangi","penulis"=>"Andrea Hirata","penerbit"=>"Bentang Pustaka","tahun"=>"2005","kategori"=>"Fiksi","stok"=>24],
    ["judul"=>"Bumi Manusia","penulis"=>"Pramoedya Ananta Toer","penerbit"=>"Hasta Mitra","tahun"=>"1980","kategori"=>"Fiksi Sejarah","stok"=>12],
    ["judul"=>"Filosofi Teras","penulis"=>"Henry Manampiring","penerbit"=>"Kompas","tahun"=>"2018","kategori"=>"Non-Fiksi","stok"=>45],
    ["judul"=>"Atomic Habits","penulis"=>"James Clear","penerbit"=>"Gramedia","tahun"=>"2019","kategori"=>"Edukasi","stok"=>89],
    ["judul"=>"Laut Bercerita","penulis"=>"Leila S. Chudori","penerbit"=>"KPG","tahun"=>"2017","kategori"=>"Fiksi Sejarah","stok"=>18],
    ["judul"=>"Cantik Itu Luka","penulis"=>"Eka Kurniawan","penerbit"=>"Gramedia","tahun"=>"2002","kategori"=>"Fiksi","stok"=>7],
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Buku</title>
    <link rel="stylesheet" href="styledatabuku.css">
</head>
<body>

<div class="container">

    <div class="sidebar">
        <h2>ADMIN</h2>
        <ul>
            <li>Dashboard</li>
            <li>Peminjaman</li>
            <li class="active">Data Buku</li>
            <li>Laporan</li>
        </ul>
    </div>

    <div class="main">
        <div class="header">
            <h2>PERPUSTAKAAN POLIJE</h2>
        </div>

        <div class="content">
            <h3>Data Buku</h3>

            <div class="top-bar">
                <button class="btn blue">+ Tambah Buku</button>
                <input type="text" placeholder="Cari Buku..." class="search">
                <button class="btn">Filter</button>
            </div>

            <table>
                <tr>
                    <th>No</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Penerbit</th>
                    <th>Tahun</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>

                <?php $no=1; foreach($buku as $b): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $b['judul'] ?></td>
                    <td><?= $b['penulis'] ?></td>
                    <td><?= $b['penerbit'] ?></td>
                    <td><?= $b['tahun'] ?></td>
                    <td><?= $b['kategori'] ?></td>
                    <td><?= $b['stok'] ?></td>
                    <td>

                        <button class="btn small"
                        onclick="openEdit(
                        '<?= htmlspecialchars($b['judul']) ?>',
                        '<?= htmlspecialchars($b['penulis']) ?>',
                        '<?= htmlspecialchars($b['penerbit']) ?>',
                        '<?= htmlspecialchars($b['tahun']) ?>',
                        '<?= htmlspecialchars($b['kategori']) ?>'
                        )">
                        Edit</button>

                        <button class="btn small"
                        onclick="openDetail(
                        '<?= htmlspecialchars($b['judul']) ?>',
                        '<?= htmlspecialchars($b['penulis']) ?>',
                        '<?= htmlspecialchars($b['penerbit']) ?>',
                        '<?= htmlspecialchars($b['tahun']) ?>',
                        '<?= htmlspecialchars($b['kategori']) ?>',
                        '<?= htmlspecialchars($b['stok']) ?>'
                        )">
                        Detail</button>

                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>

<!-- MODAL EDIT -->
<div id="modalEdit" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEdit()">&times;</span>
        <h3>Edit Data Buku</h3>

        <input type="text" id="e_judul">
        <input type="text" id="e_penulis">
        <input type="text" id="e_penerbit">

        <div class="row">
            <input type="text" id="e_tahun">
            <input type="text" id="e_kategori">
        </div>

        <br>
        <button class="btn" onclick="closeEdit()">Batal</button>
        <button class="btn blue">Simpan</button>
    </div>
</div>

<!-- MODAL DETAIL -->
<div id="modalDetail" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDetail()">&times;</span>
        <h3>Detail Buku</h3>

        <p><b>Judul:</b> <span id="d_judul"></span></p>
        <p><b>Penulis:</b> <span id="d_penulis"></span></p>
        <p><b>Penerbit:</b> <span id="d_penerbit"></span></p>
        <p><b>Tahun:</b> <span id="d_tahun"></span></p>
        <p><b>Kategori:</b> <span id="d_kategori"></span></p>
        <p><b>Stok:</b> <span id="d_stok"></span></p>
    </div>
</div>

<script>
// EDIT
function openEdit(judul, penulis, penerbit, tahun, kategori) {
    document.getElementById("modalEdit").style.display = "block";

    document.getElementById("e_judul").value = judul;
    document.getElementById("e_penulis").value = penulis;
    document.getElementById("e_penerbit").value = penerbit;
    document.getElementById("e_tahun").value = tahun;
    document.getElementById("e_kategori").value = kategori;
}

function closeEdit() {
    document.getElementById("modalEdit").style.display = "none";
}

// DETAIL
function openDetail(judul, penulis, penerbit, tahun, kategori, stok) {
    document.getElementById("modalDetail").style.display = "block";

    document.getElementById("d_judul").innerText = judul;
    document.getElementById("d_penulis").innerText = penulis;
    document.getElementById("d_penerbit").innerText = penerbit;
    document.getElementById("d_tahun").innerText = tahun;
    document.getElementById("d_kategori").innerText = kategori;
    document.getElementById("d_stok").innerText = stok;
}

function closeDetail() {
    document.getElementById("modalDetail").style.display = "none";
}
</script>

</body>
</html>