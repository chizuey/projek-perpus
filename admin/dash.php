<?php
// ====== DATA DUMMY (nanti bisa dari database) ======
$total_buku = 168;
$tersedia = 158;
$dipinjam = 10;

$logs = [
    ["id" => "e00000", "nama" => "nama saya", "judul" => "Algoritma", "status" => "Pinjam"],
    ["id" => "e00011", "nama" => "John", "judul" => "Novel", "status" => "Pinjam"],
    ["id" => "e00022", "nama" => "Wick", "judul" => "Database", "status" => "Kembali"],
];
?>
<!-- 
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> -->

    <!-- Bootstrap -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->

    <!-- Custom CSS -->
    <!-- <link rel="stylesheet" href="style.css"> -->
<!-- </head> -->
<!-- <body> -->

<div class="main-content">
    <div class="container py-4">

        <!-- STAT -->
        <div class="row g-4 mb-4">

            <div class="col-md-4">
                <div class="card stat-card">
                    <div>
                        <h3><?= $total_buku ?></h3>
                        <small>Total buku</small>
                    </div>
                    <div class="icon bg-red">
                        <i class="bi bi-book"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card">
                    <div>
                        <h3><?= $tersedia ?></h3>
                        <small>Tersedia</small>
                    </div>
                    <div class="icon bg-gray">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card">
                    <div>
                        <h3><?= $dipinjam ?></h3>
                        <small>Dipinjam</small>
                    </div>
                    <div class="icon bg-orange">
                        <i class="bi bi-bookmark"></i>
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-4">

            <!-- TABLE -->
            <div class="col-md-8">
                <div class="card custom-card p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <h6>Log Hari Ini</h6>
                        <small>02/03/2027</small>
                    </div>

                    <table class="table table-borderless align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>ID TRANSAKSI</th>
                                <th>NAMA PEMINJAM</th>
                                <th>JUDUL BUKU</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= $log['id'] ?></td>
                                <td><?= $log['nama'] ?></td>
                                <td><?= $log['judul'] ?></td>
                                <td>
                                    <span class="badge 
                                    <?= $log['status']=='Pinjam' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>">
                                        <?= $log['status'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>
            </div>

            <!-- RIGHT -->
            <div class="col-md-4">

                <!-- CHART -->
                <div class="card custom-card p-3 mb-3">
                    <h6 class="mb-2">Grafik peminjaman buku</h6>

                    <div class="chart">
                        <div class="y-line"></div>

                        <div class="bars">
                            <div class="bar purple" style="height: 80px;"></div>
                            <div class="bar dark" style="height: 55px;"></div>
                            <div class="bar light" style="height: 25px;"></div>
                        </div>
                    </div>
                </div>

                <!-- ADMIN -->
                <div class="card admin-card p-3">
                    <h6>Nama admin</h6>
                    <small>Jabatan</small>
                </div>

            </div>

        </div>

    </div>
</div>


<!-- </body>
</html> -->