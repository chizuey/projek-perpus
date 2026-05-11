<?php
// 1. Persiapan Data
require_once __DIR__ . '/../../controllers/PeminjamanController.php';
$peminjamanController = new PeminjamanController();

// Ambil data laporan dan ringkasan dari controller
$result = $peminjamanController->report();
$dataTampil = $result['dataTampil'] ?? [];
$summary = $result['summary'] ?? ['total'=>0, 'selesai'=>0, 'terlambat'=>0, 'dipinjam'=>0];

// Ambil nilai filter dari URL untuk form
$filterStatus = $_GET['status'] ?? 'Semua';
$tglMulai = $_GET['from'] ?? '';
$tglSelesai = $_GET['to'] ?? '';
$cari = $_GET['q'] ?? '';
?>

<div class="page-wrapper">
    
    <!-- Bagian 1: Header Halaman -->
    <div class="page-header">
        <h1>Laporan Transaksi</h1>
    </div>

    <!-- Bagian 2: Ringkasan Statistik (Dashboard Cards) -->
    <section class="dashboard-cards">
        <article class="card card-blue">
            <div class="card-top">
                <span class="card-icon"><i class="bi bi-journal-text"></i></span>
                <span class="card-label">Total Pinjam</span>
            </div>
            <div class="card-value"><?= $summary['total']; ?></div>
        </article>

        <article class="card card-green">
            <div class="card-top">
                <span class="card-icon"><i class="bi bi-check-circle"></i></span>
                <span class="card-label">Selesai</span>
            </div>
            <div class="card-value"><?= $summary['selesai']; ?></div>
        </article>

        <article class="card card-yellow">
            <div class="card-top">
                <span class="card-icon"><i class="bi bi-clock-history"></i></span>
                <span class="card-label">Terlambat</span>
            </div>
            <div class="card-value"><?= $summary['terlambat']; ?></div>
        </article>

        <article class="card card-red">
            <div class="card-top">
                <span class="card-icon"><i class="bi bi-exclamation-circle"></i></span>
                <span class="card-label">Belum Kembali</span>
            </div>
            <div class="card-value"><?= $summary['dipinjam']; ?></div>
        </article>
    </section>

    <!-- Bagian 3: Toolbar Filter & Pencarian -->
    <div class="toolbar">
        <form method="get" class="filter-form">
            <input type="hidden" name="menu" value="laporan">
            
            <!-- Filter Status -->
            <div class="field-group status-filter">
                <span class="field-label">Status:</span>
                <select name="status" onchange="this.form.submit()">
                    <option value="Semua" <?= $filterStatus == 'Semua' ? 'selected' : '' ?>>Semua</option>
                    <option value="Selesai" <?= $filterStatus == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="Terlambat" <?= $filterStatus == 'Terlambat' ? 'selected' : '' ?>>Terlambat</option>
                    <option value="Dipinjam" <?= $filterStatus == 'Dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                </select>
            </div>

            <!-- Filter Tanggal -->
            <div class="field-group date-range">
                <input type="date" name="from" value="<?= $tglMulai; ?>" onchange="this.form.submit()">
                <span class="separator"> s/d </span>
                <input type="date" name="to" value="<?= $tglSelesai; ?>" onchange="this.form.submit()">
            </div>

            <!-- Pencarian Nama -->
            <div class="search-box">
                <input type="text" name="q" placeholder="Cari nama..." value="<?= htmlspecialchars($cari); ?>">
                <button type="submit" class="search-submit"><i class="bi bi-search"></i></button>
            </div>

            <!-- Tombol Cetak CSV -->
            <a href="actions/peminjaman/export_csv.php?status=<?= $filterStatus ?>&from=<?= $tglMulai ?>&to=<?= $tglSelesai ?>&q=<?= urlencode($cari) ?>" class="btn-export btn-csv">
                <i class="bi bi-file-earmark-spreadsheet"></i> Cetak Laporan (CSV)
            </a>
        </form>
    </div>

    <!-- Bagian 4: Tabel Laporan -->
    <div class="table-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Peminjam</th>
                        <th>ID Eksemplar</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th>Denda</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dataTampil)): ?>
                        <tr>
                            <td colspan="7" class="empty-state">Data tidak ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1; 
                        foreach ($dataTampil as $row): 
                            // Tentukan kelas warna badge
                            $kelasStatus = 'status-default';
                            if ($row['status'] == 'Selesai') $kelasStatus = 'status-green';
                            if ($row['status'] == 'Terlambat') $kelasStatus = 'status-red';
                            if ($row['status'] == 'Dipinjam') $kelasStatus = 'status-blue';
                        ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['peminjam'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['id_eksemplar'] ?? ''); ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_peminjaman'])); ?></td>
                                <td><?= !empty($row['tanggal_kembali']) ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-'; ?></td>
                                <td><?= $row['denda'] ?: 'Rp 0'; ?></td>
                                <td><span class="status-badge <?= $kelasStatus; ?>"><?= $row['status']; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
