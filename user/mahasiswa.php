<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../controllers/UserController.php';

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$userController = new UserController();
$dashboardData = $userController->dashboard($_SESSION['id_user']);

$profile = $dashboardData['profile'];
$activeLoans = $dashboardData['activeLoans'];
$history = $dashboardData['history'];
$stats = $dashboardData['stats'];

$nama_tampil = $profile['nama'];
$nim_tampil  = $profile['nim'];
$jurusan_tampil = $profile['jurusan'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Mahasiswa - Perpustakaan Polije</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/mahasiswa.css">
</head>
<body>

      <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
    <a href="beranda.php" style="display: flex; align-items: center; justify-content: center; width: 35px; height: 35px; border: 1px solid #ddd; border-radius: 8px; text-decoration: none; color: #333;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6"></path>
        </svg>
    </a>
    <h2 style="margin: 0; font-size: 24px; font-weight: bold;">Profil Mahasiswa</h2>
</div>

    <div class="profile-card">
        <div class="avatar-placeholder">
            <svg width="45" height="45" viewBox="0 0 24 24" fill="#94a3b8">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
        </div>

        <div class="profile-info">
            <h1><?php echo strtoupper($nama_tampil); ?></h1>
            <div class="badge-group">
                <span class="badge badge-nim">NIM <?php echo $nim_tampil; ?></span>
                <span class="badge" style="background: #f1f5f9; color: #475569;"><?php echo $jurusan_tampil; ?></span>
            </div>
        </div>

        <a href="../auth/logout.php" class="btn-logout-card" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Logout
        </a>
    </div>

    <div class="grid-sections" style="margin-top: 25px;">
        <div class="section-card">
            <h3>Buku yang Dipinjam</h3>
            <?php if (empty($activeLoans)): ?>
                <p style="font-size: 13px; color: var(--text-muted); text-align: center; margin-top: 20px;">Anda tidak sedang meminjam buku.</p>
            <?php else: ?>
                <?php foreach (array_slice($activeLoans, 0, 3) as $loan): ?>
                    <div class="book-item">
                        <div class="book-cover">
                            <?php if ($loan['cover']): ?>
                                <img src="../<?php echo $loan['cover']; ?>" style="width:100%; height:100%; object-fit:cover; border-radius:4px;">
                            <?php endif; ?>
                        </div>
                        <div class="book-detail">
                            <div class="book-title"><?php echo htmlspecialchars($loan['judul']); ?></div>
                            <div class="book-author"><?php echo htmlspecialchars($loan['penulis']); ?></div>
                            <div class="book-meta">🗓️ Dipinjam: <?php echo date('d M Y', strtotime($loan['tanggal_peminjaman'])); ?></div>
                        </div>
                        <div class="status-labels">
                            <?php if ($loan['status_teks'] === 'Terlambat'): ?>
                                <span class="status-tag tag-late"><?php echo $loan['status_teks']; ?> <?php echo $loan['terlambat']; ?></span>
                                <span class="status-tag tag-denda">Denda <?php echo $loan['denda_teks']; ?></span>
                            <?php else: ?>
                                <span class="status-tag tag-safe">Kembali: <?php echo date('d M Y', strtotime($loan['batas_waktu'])); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (count($activeLoans) > 3): ?>
                    <a href="#" class="see-all">Lihat semua (<?php echo count($activeLoans); ?>)</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="section-card">
            <h3>Reservasi Buku</h3>
            <div class="book-item">
                <div class="book-cover"></div>
                <div class="book-detail">
                    <div class="book-title">Sistem Basis Data Lanjut</div>
                    <div class="book-author">Polije Press</div>
                    <div class="book-meta">🗓️ Reservasi pada 8 Nov 2023</div>
                </div>
                <div class="status-labels">
                    <span class="status-tag tag-wait">Menunggu Konfirmasi</span>
                </div>
            </div>
            <a href="#" class="see-all" style="margin-top: 70px;">Lihat semua</a>
        </div>
    </div>

    <h3>Riwayat Peminjaman</h3>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;">📘</div>
            <div class="stat-info">
                <span class="label">Total Buku Dipinjam</span>
                <span class="value"><?php echo $stats['totalBuku']; ?></span>
                <span class="desc">Riwayat semua peminjaman</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff7ed; color: #f59e0b;">⏰</div>
            <div class="stat-info">
                <span class="label">Riwayat Keterlambatan</span>
                <span class="value"><?php echo $stats['totalKeterlambatan']; ?>x</span>
                <span class="desc">Buku yang dikembalikan terlambat</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;">💰</div>
            <div class="stat-info">
                <span class="label">Total Denda</span>
                <span class="value">Rp<?php echo number_format($stats['totalDenda'], 0, ',', '.'); ?></span>
                <span class="desc">Total denda akumulatif</span>
            </div>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Batas Waktu / Kembali</th>
                    <th>Status</th>
                    <th>Denda</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">Belum ada riwayat peminjaman.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach (array_slice($history, 0, 5) as $row): ?>
                        <tr>
                            <td>
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <div class="book-cover" style="width:30px; height:40px;">
                                        <?php if ($row['cover']): ?>
                                            <img src="../<?php echo $row['cover']; ?>" style="width:100%; height:100%; object-fit:cover; border-radius:4px;">
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($row['judul']); ?></strong><br>
                                        <small style="color:var(--text-muted)"><?php echo htmlspecialchars($row['penulis']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_peminjaman'])); ?></td>
                            <td><?php echo !empty($row['tanggal_kembali']) ? date('d M Y', strtotime($row['tanggal_kembali'])) : date('d M Y', strtotime($row['batas_waktu'])); ?></td>
                            <td>
                                <?php 
                                    $tagClass = 'tag-safe';
                                    if ($row['status_teks'] === 'Terlambat') $tagClass = 'tag-late';
                                    if ($row['status_teks'] === 'Dipinjam') $tagClass = 'tag-wait';
                                    if ($row['status_teks'] === 'Selesai') $tagClass = 'tag-safe';
                                ?>
                                <span class="status-tag <?php echo $tagClass; ?>"><?php echo $row['status_teks']; ?></span>
                            </td>
                            <td style="color:var(--primary-blue); font-weight:600;"><?php echo $row['denda_teks']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if (count($history) > 5): ?>
            <a href="#" class="see-all">Lihat semua (<?php echo count($history); ?>)</a>
        <?php endif; ?>
    </div>
</div>

<div style="margin-bottom: 50px;"></div>
</body>
</html>