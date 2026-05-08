<?php
session_start();
require_once '../config.php'; 

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Ambil data terbaru dari database
$query = mysqli_query($koneksi, "SELECT * FROM anggota WHERE id_anggota = '$id_user'");
$user  = mysqli_fetch_assoc($query);

// Gunakan data dari database jika ada, jika tidak gunakan session
$nama_tampil = isset($user['nama_anggota']) ? $user['nama_anggota'] : $_SESSION['nama'];
$nim_tampil  = isset($user['nim']) ? $user['nim'] : $_SESSION['nim'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Mahasiswa - Perpustakaan Polije</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0061f2;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --danger: #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            margin: 0;
            padding: 20px 40px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

       .btn-back {
    border: 1px solid #e0e0e0; /* Memberi garis tepi halus */
    transition: background 0.3s;
}

.btn-back:hover {
    background-color: #eeeeee;
}

        h2 { font-size: 24px; margin-bottom: 20px; font-weight: 700; color: #1e293b; }
        h3 { font-size: 18px; margin: 25px 0 15px 0; font-weight: 600; }

        /* Profile Card */
        .profile-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 25px;
            position: relative; /* Supaya tombol logout bisa ditaruh di kanan bawah */
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .avatar-placeholder {
            width: 90px;
            height: 90px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
        }

        .profile-info h1 {
            margin: 0 0 10px 0;
            font-size: 26px;
            font-weight: 700;
        }

        .badge-group { display: flex; gap: 10px; }
        .badge {
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-nim { background: #e0e7ff; color: #4338ca; }

        /* Tombol Logout*/
        .btn-logout-card {
            position: absolute;
            right: 25px;
            bottom: 25px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            color: var(--danger);
            border: 1px solid var(--danger);
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-logout-card:hover {
            background: var(--danger);
            color: white;
        }

        /* Sections Grid */
        .grid-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .section-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }

        .book-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .book-cover {
            width: 45px;
            height: 60px;
            background: #f1f5f9;
            border-radius: 4px;
            flex-shrink: 0;
        }

        .book-detail { flex: 1; }
        .book-title { font-weight: 600; font-size: 14px; margin-bottom: 2px; }
        .book-author { color: var(--text-muted); font-size: 12px; margin-bottom: 5px; }
        .book-meta { font-size: 11px; color: var(--text-muted); }

        .status-labels {
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: flex-end;
        }

        .status-tag {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            white-space: nowrap;
        }
        .tag-late { background: #fee2e2; color: #b91c1c; }
        .tag-denda { border: 1px solid var(--border-color); color: var(--primary-blue); }
        .tag-safe { background: #dcfce7; color: #15803d; }
        .tag-wait { background: #eff6ff; color: #1d4ed8; }

        .see-all {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--primary-blue);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .stat-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-info .value { font-size: 22px; font-weight: 700; display: block; margin: 2px 0; }
        .stat-info .label { font-size: 12px; color: var(--text-muted); font-weight: 500; }
        .stat-info .desc { font-size: 11px; color: var(--text-muted); }

        /* Table */
        .table-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            margin-top: 15px;
        }

        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 15px 20px; font-size: 12px; color: var(--text-muted); text-align: left; border-bottom: 1px solid var(--border-color); }
        td { padding: 15px 20px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }

        @media (max-width: 768px) {
            .grid-sections, .stats-grid { grid-template-columns: 1fr; }
            .profile-card { flex-direction: column; text-align: center; padding-bottom: 80px; }
            .btn-logout-card { left: 50%; transform: translateX(-50%); bottom: 20px; right: auto; }
        }
    </style>
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
            <div class="book-item">
                <div class="book-cover"></div>
                <div class="book-detail">
                    <div class="book-title">Negeri Para Bedebah</div>
                    <div class="book-author">Tere Liye</div>
                    <div class="book-meta">🗓️ Dipinjam sejak 12 Okt 2023</div>
                </div>
                <div class="status-labels">
                    <span class="status-tag tag-late">Terlambat 2 Hari</span>
                    <span class="status-tag tag-denda">Denda Rp1.500</span>
                </div>
            </div>
            <div class="book-item">
                <div class="book-cover"></div>
                <div class="book-detail">
                    <div class="book-title">Sistem Basis Data Lanjut</div>
                    <div class="book-author">Polije Press</div>
                    <div class="book-meta">🗓️ Dipinjam sejak 8 Nov 2023</div>
                </div>
                <div class="status-labels">
                    <span class="status-tag tag-safe">Dalam 5 Hari</span>
                    <span class="status-tag tag-denda">Denda Rp0</span>
                </div>
            </div>
            <a href="#" class="see-all">Lihat semua</a>
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
                <span class="value">12</span>
                <span class="desc">2 buku dipinjam semester ini</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff7ed; color: #f59e0b;">⏰</div>
            <div class="stat-info">
                <span class="label">Riwayat Keterlambatan</span>
                <span class="value">3x</span>
                <span class="desc">Perlu perhatian saat pengembalian</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;">💰</div>
            <div class="stat-info">
                <span class="label">Total Denda</span>
                <span class="value">Rp18.000</span>
                <span class="desc">Tagihan belum dibayar</span>
            </div>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th>Denda</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <div class="book-cover" style="width:30px; height:40px;"></div>
                            <div>
                                <strong>Negeri Para Bedebah</strong><br>
                                <small style="color:var(--text-muted)">Tere Liye</small>
                            </div>
                        </div>
                    </td>
                    <td>12 Okt 2023</td>
                    <td>22 Okt 2023</td>
                    <td><span class="status-tag tag-late">Terlambat 2 Hari</span></td>
                    <td style="color:var(--primary-blue); font-weight:600;">Rp1.500</td>
                </tr>
                <tr>
                    <td>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <div class="book-cover" style="width:30px; height:40px;"></div>
                            <div>
                                <strong>Sistem Basis Data Lanjut</strong><br>
                                <small style="color:var(--text-muted)">Polije Press</small>
                            </div>
                        </div>
                    </td>
                    <td>01 Nov 2023</td>
                    <td>08 Nov 2023</td>
                    <td><span class="status-tag" style="background:#f1f5f9; color:#64748b;">Selesai</span></td>
                    <td style="color:var(--primary-blue); font-weight:600;">Rp0</td>
                </tr>
            </tbody>
        </table>
        <a href="#" class="see-all">Lihat semua</a>
    </div>
</div>

<div style="margin-bottom: 50px;"></div>
</body>
</html>