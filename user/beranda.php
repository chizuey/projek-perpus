<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Perpustakaan Polije</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/projek-perpus/public/css/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="/projek-perpus/public/css/stylekoleksi.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="/projek-perpus/public/css/animations.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
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
    <div class="container hero-content" data-aos="fade-up" data-aos-delay="100">
        <div class="hero-text">
            <h1>Selamat Datang di <br><span>Perpustakaan Polije</span></h1>
            <p>Temukan berbagai judul dan referensi lainnya
untuk mendukung proses belajar mengajar. Temukan inspirasi
dan pengetahuan tanpa batas.
            
            <form class="search-container" id="searchForm" method="GET" action="koleksi.php">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" id="searchInput" placeholder="Cari judul buku atau penulis...">
                <button type="submit" class="btn-search">Cari Buku</button>
            </form>
        </div>

        <div class="hero-image">
           <img src="../public/img/bg-bku2.png" alt="Gambar Latar Perpustakaan" data-aos="fade-in">
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="container features">
   <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
        <div class="icon-box blue"><img src="gambar/koleksi.png"></div>
        <h3>Koleksi Buku</h3>
        <p>Tersedia berbagai macam judul buku cetak dari berbagai kategori untuk mendukung referensi belajar Anda.</p>
   </div>

   <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
        <div class="icon-box blue"><img src="gambar/akses.png"></div>
        <h3>Akses Katalog</h3>
        <p>Cari dan temukan berbagai koleksi buku dengan mudah melalui sistem digital.</p>
   </div>

   <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
        <div class="icon-box blue"><img src="gambar/wifi.png"></div>
        <h3>Fasilitas Nyaman</h3>
        <p>Ruang baca tenang dilengkapi
WiFi kecepatan tinggi dan area
diskusi.</p>
   </div>

   <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
        <div class="icon-box blue"><img src="gambar/layanan.png"></div>
        <h3>Peminjaman Mudah</h3>
        <p>Sistem peminjaman buku yang
mudah, cepat, dan terintegrasi
secara digital.</p>
   </div>
</section>

<!-- KOLEKSI TERBARU -->
<section class="container book-section">
    <div class="section-header" data-aos="fade-up">
        <h2>Koleksi Terbaru</h2>
    </div>

    <div class="book-grid animate-stagger">
        <?php foreach ($newestBooks as $b): 
            $id_pop    = (int)($b['id'] ?? $b['id_buku'] ?? 0);
            $titel_pop = addslashes(htmlspecialchars($b['judul']));
            $kat_pop   = htmlspecialchars($b['kategori'] ?? 'Umum');
            $desk_pop  = addslashes(htmlspecialchars($b['deskripsi'] ?? 'Tidak ada deskripsi.'));
            $img_pop   = !empty($b['cover']) ? '../' . htmlspecialchars($b['cover']) : 'user/gambar/buku.png';
            
            // Tambahkan ini untuk mengambil data stok dari database
            $stok_new  = (int)($b['stok_tersedia'] ?? 0); 
        ?>
            <div class="book-card hover-lift" data-aos="fade-up" style="cursor: pointer;" 
                 onclick="bukaPopup(<?= $id_pop ?>, '<?= $titel_pop ?>', '<?= $kat_pop ?>', '<?= $img_pop ?>', '<?= $desk_pop ?>', <?= $stok_new ?>)">
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
    <div class="section-header" data-aos="fade-up">
        <h2>Buku Terpopuler</h2>
    </div>

    <div class="book-grid animate-stagger">
        <?php foreach ($popularBooks as $b): 
            $id_pop    = (int)($b['id'] ?? $b['id_buku'] ?? 0);
            $titel_pop = addslashes(htmlspecialchars($b['judul']));
            $kat_pop   = htmlspecialchars($b['kategori'] ?? 'Umum');
            $desk_pop  = addslashes(htmlspecialchars($b['deskripsi'] ?? 'Tidak ada deskripsi.'));
            $img_pop   = !empty($b['cover']) ? '../' . htmlspecialchars($b['cover']) : 'user/gambar/buku.png';
            
            // Tetap ambil data stok untuk dikirim ke fungsi popup
            $stok_pop  = (int)($b['stok_tersedia'] ?? 0); 
        ?>
            <div class="book-card hover-lift" data-aos="fade-up" style="cursor: pointer;" 
                 onclick="bukaPopup(<?= $id_pop ?>, '<?= $titel_pop ?>', '<?= $kat_pop ?>', '<?= $img_pop ?>', '<?= $desk_pop ?>', <?= $stok_pop ?>)">
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
</section> <?php include 'foot.php'; ?>
<?php include 'modal_detail.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.min.js"></script>
<script>
  // Initialize AOS (Animate On Scroll)
  AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true,
    mirror: false,
    offset: 100
  });
</script>

</body>
</html>
