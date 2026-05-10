<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Perpustakaan Polije</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../public/css/style.css?v=6"

  <-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php 
require_once __DIR__ . '/../models/Buku.php';
$bukuModel = new Buku();
$newestBooks = $bukuModel->getNewest(6);
$popularBooks = $bukuModel->getPopular(6);
?>

<?php include 'navbar.php'; ?>

<!-- HERO -->
<section class="hero">
    <div class="container hero-content">
        <div class="hero-text">
            <h1>Selamat Datang di <br><span>Perpustakaan Polije</span></h1>
            <p>Temukan berbagai judul dan referensi lainnya
untuk mendukung proses belajar mengajar. Temukan inspirasi
dan pengetahuan tanpa batas.
            
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" placeholder="Cari judul buku atau penulis...">
                <button class="btn-search">Cari Buku</button>
            </div>
        </div>

        <div class="hero-image">
            <img src="gambar/bg-bku.png" alt="">
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="container features">
   <div class="feature-card">
        <div class="icon-box blue"><img src="gambar/koleksi.png"></div>
        <h3>Koleksi Buku</h3>
        <p>Tersedia berbagai macam judul buku cetak dari berbagai kategori untuk mendukung referensi belajar Anda.</p>
   </div>

   <div class="feature-card">
        <div class="icon-box blue"><img src="gambar/akses.png"></div>
        <h3>Katalog Buku Ter-update</h3>
        <p>Cari dan temukan ketersediaan buku favorit Anda dengan mudah melalui sistem katalog digital kami.
   </div>

   <div class="feature-card">
        <div class="icon-box blue"><img src="gambar/wifi.png"></div>
        <h3>Fasilitas Nyaman</h3>
        <p>Ruang baca tenang dilengkapi
WiFi kecepatan tinggi dan area
diskusi.</p>
   </div>

   <div class="feature-card">
        <div class="icon-box blue"><img src="gambar/layanan.png"></div>
        <h3>Peminjaman Mudah</h3>
        <p>Sistem peminjaman buku yang
mudah, cepat, dan terintegrasi
secara digital.</p>
   </div>
</section>

<!-- KOLEKSI TERBARU -->
<section class="container book-section">
    <div class="section-header">
        <h2>Koleksi Terbaru</h2>
        <a href="#" class="view-all">Lihat Semua <i class="fas fa-chevron-right" style="font-size: 10px;"></i></a>
    </div>

    <div class="book-grid">
        <?php foreach ($newestBooks as $b): ?>
            <div class="book-card">
                <div class="book-cover">
                    <img src="../<?= !empty($b['cover']) ? htmlspecialchars($b['cover']) : 'user/gambar/buku.png'; ?>" alt="<?= htmlspecialchars($b['judul']); ?>">
                </div>
                <div class="book-info">
                    <h4><?= htmlspecialchars($b['judul']); ?></h4>
                    <p><?= htmlspecialchars($b['penulis']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- TERPOPULER -->
<section class="container book-section">
    <div class="section-header">
        <h2>Buku Terpopuler</h2>
        <a href="#" class="view-all">Lihat Semua <i class="fas fa-chevron-right" style="font-size: 10px;"></i></a>
    </div>

    <div class="book-grid">
        <?php foreach ($popularBooks as $b): ?>
            <div class="book-card">
                <div class="book-cover">
                    <img src="../<?= !empty($b['cover']) ? htmlspecialchars($b['cover']) : 'user/gambar/buku.png'; ?>" alt="<?= htmlspecialchars($b['judul']); ?>">
                </div>
                <div class="book-info">
                    <h4><?= htmlspecialchars($b['judul']); ?></h4>
                    <p><?= htmlspecialchars($b['penulis']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include 'foot.php'; ?>

</body>
</html>