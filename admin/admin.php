<?php
$menu = $_GET['menu'] ?? 'akun';

$allowedMenus = [
    'akun' => [
        'file' => 'akun.php',
        'styles' => ['css/akun.css'],
    ],
    'peminjaman' => [
        'file' => 'datapeminjam/datapeminjam.php',
        'styles' => [
            'datapeminjam/datapeminjam.css',
            'datapeminjam/popuppeminjaman.css',
        ],
    ],
    'laporan' => [
        'file' => 'laporantransaksi/laporantransaksi.php',
        'styles' => [
            'laporantransaksi/laporantransaksi.css',
        ],
    ],
    'tambahbuku' => [
        'file' => 'tambah.php',
        'styles' => [
            'css/tambah.css'
        ]
    ],
    'dashboard' => [
        'file' => 'dash.php',
        'styles' => [
            'css/dash.css'
        ]
    ]
];

if (!isset($allowedMenus[$menu])) {
    $menu = 'akun';
}

$currentMenu = $menu;
$currentFile = $allowedMenus[$menu]['file'];
$currentStyles = $allowedMenus[$menu]['styles'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta charset="utf-8" />

    <!-- <link rel="stylesheet" href="css/tambah.css"> -->
    <link rel="stylesheet" href="css/sidetop.css">

    <?php foreach ($currentStyles as $style): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($style, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endforeach; ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- ICON -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>
<body>

    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>

    <main class="main-content">
        <?php include $currentFile; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>