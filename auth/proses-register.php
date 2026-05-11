<?php
session_start();

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit();
}

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

if ($nama === '' || $email === '' || $password === '' || $konfirmasiPassword === '') {
    echo "<script>alert('Semua field wajib diisi.'); window.location='register.php';</script>";
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Format email tidak valid.'); window.location='register.php';</script>";
    exit();
}

if ($password !== $konfirmasiPassword) {
    echo "<script>alert('Konfirmasi kata sandi tidak sesuai.'); window.location='register.php';</script>";
    exit();
}

$nim = strtoupper(strtok($email, '@') ?: '');
if ($nim === '') {
    $nim = 'USR' . date('YmdHis');
}

$stmt = $koneksi->prepare('SELECT id_anggota FROM anggota WHERE email = ? OR nim = ? LIMIT 1');
$stmt->bind_param('ss', $email, $nim);
$stmt->execute();

if ($stmt->get_result()->fetch_assoc()) {
    echo "<script>alert('Email atau NIM sudah terdaftar.'); window.location='register.php';</script>";
    exit();
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$jurusan = '-';

$stmt = $koneksi->prepare(
    'INSERT INTO anggota (nama, nama_anggota, nim, email, password, jurusan)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('ssssss', $nama, $nama, $nim, $email, $hash, $jurusan);
$stmt->execute();

$_SESSION['id_user'] = $koneksi->insert_id;
$_SESSION['id_anggota'] = $koneksi->insert_id;
$_SESSION['nama'] = $nama;
$_SESSION['nim'] = $nim;
$_SESSION['jurusan'] = $jurusan;
$_SESSION['level'] = 'user';

header('Location: ../user/mahasiswa.php');
exit();
