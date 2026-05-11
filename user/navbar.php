<nav class="header-navbar">
<?php 
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect from where this file is being included
$trace = debug_backtrace();
$caller_file = isset($trace[0]['file']) ? $trace[0]['file'] : __FILE__;
$caller_dir = dirname($caller_file);

// Get the last directory name in the path
$parts = explode(DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $caller_dir));
$caller_dir_name = end($parts);

// If included from 'user' folder directly
$base = ($caller_dir_name === 'user') ? '' : '../';

// Tentukan link profil berdasarkan status login
// Sesuaikan 'id_user' dengan nama session yang kamu pakai saat login
if (isset($_SESSION['id_user'])) {
    // Jika sudah login, arahkan ke halaman profil (asumsi di folder user)
    $link_profil = $base . "mahasiswa.php";
} else {
    // Jika belum login, arahkan ke login.php (di luar folder user)
    $link_profil = $base . "../auth/login.php";
}
?>
    <div class="container">

        <div class="logo-section">
           <img src="<?php echo $base; ?>gambar/logo-polije.png" alt="Logo" class="logo-gambar">
            <span class="brand-name">Perpustakaan Polije</span>
        </div>

        <ul class="nav-menu">
            <li><a href="<?php echo $base; ?>beranda.php" class="nav-link">Beranda</a></li>

            <li class="dropdown">
                <a class="nav-link">
                    Tentang Kami
                    <svg class="chevron" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.3" fill="none"/>
                    </svg>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base; ?>profil.php">Profil</a></li>
                    <li><a href="<?php echo $base; ?>lokasi.php">Lokasi & Jam Kerja</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a class="nav-link">
                    Panduan
                    <svg class="chevron" width="16" height="16">
                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.3" fill="none"/>
                    </svg>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base; ?>tatatertib.php">Tata Tertib</a></li>
                    <li><a href="<?php echo $base; ?>tatacara.php">Tata Cara</a></li>
                </ul>
            </li>

            <li>
                <a href="<?php echo $base; ?>koleksi.php" class="nav-link">Koleksi</a>
            </li>
        </ul>

        <div class="profile-section">
            <a href="<?php echo $link_profil; ?>" class="profile-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2">
                    <circle cx="12" cy="7" r="4"/>
                    <path d="M5 21c0-4 14-4 14 0"/>
                </svg>
            </a>
        </div>

    </div>
</nav>