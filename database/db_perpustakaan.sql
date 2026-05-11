-- ============================================================
-- DATABASE SISTEM PERPUSTAKAAN
-- Basis dari SQL lama + kolom tambahan untuk fitur sekarang
-- Basic CRUD sederhana
-- ============================================================

DROP DATABASE IF EXISTS db_perpustakaan;
CREATE DATABASE db_perpustakaan
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_perpustakaan;

-- ============================================================
-- TABEL ADMIN
-- Basis lama: nama, email, password, role
-- Tambahan: jabatan_admin, no_hp_admin, last_login_at
-- ============================================================
CREATE TABLE admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'petugas') NOT NULL DEFAULT 'petugas',
    jabatan_admin VARCHAR(100) NOT NULL DEFAULT 'Admin Perpustakaan',
    no_hp_admin VARCHAR(20) DEFAULT NULL,
    last_login_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABEL ANGGOTA
-- Tambahan lama: jurusan, total_denda, cover di buku
-- Tambahan kompatibilitas: nama_anggota
-- ============================================================
CREATE TABLE anggota (
    id_anggota INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    jurusan VARCHAR(100) NOT NULL DEFAULT '-',
    total_denda INT NOT NULL DEFAULT 0,
    nama_anggota VARCHAR(100) DEFAULT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_telepon VARCHAR(20) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE buku (
    id_buku INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) DEFAULT NULL UNIQUE,
    judul VARCHAR(255) NOT NULL,
    penulis VARCHAR(255) NOT NULL,
    penerbit VARCHAR(255) NOT NULL,
    tahun_terbit YEAR NOT NULL,
    copy INT NOT NULL DEFAULT 0,
    id_kategori INT NOT NULL,
    cover VARCHAR(255) DEFAULT NULL,
    total_stok INT NOT NULL DEFAULT 0,
    stok_tersedia INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE eksemplar (
    id_eksemplar INT AUTO_INCREMENT PRIMARY KEY,
    id_buku INT NOT NULL,
    status ENUM('tersedia', 'dipinjam', 'rusak', 'hilang') NOT NULL DEFAULT 'tersedia',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

CREATE TABLE peminjaman (
    id_peminjaman INT AUTO_INCREMENT PRIMARY KEY,
    id_anggota INT NOT NULL,
    id_admin INT NOT NULL,
    tanggal_peminjaman DATE NOT NULL,
    batas_waktu DATE NOT NULL,
    laporan_hidden_at TIMESTAMP NULL DEFAULT NULL,
    id_buku INT DEFAULT NULL,
    tanggal_pinjam DATE DEFAULT NULL,
    tanggal_jatuh_tempo DATE DEFAULT NULL,
    tanggal_kembali DATE DEFAULT NULL,
    status_pinjam ENUM('borrowed', 'returned', 'overdue') NOT NULL DEFAULT 'borrowed',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (id_admin) REFERENCES admin(id_admin)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

CREATE TABLE detail_peminjaman (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_peminjaman INT NOT NULL,
    id_eksemplar INT NOT NULL,
    tanggal_kembali DATE DEFAULT NULL,
    status_pengembalian ENUM('dipinjam', 'kembali', 'hilang') NOT NULL DEFAULT 'dipinjam',
    extended_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (id_eksemplar) REFERENCES eksemplar(id_eksemplar)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE reservasi (
    id_reservasi INT AUTO_INCREMENT PRIMARY KEY,
    id_anggota INT NOT NULL,
    id_admin INT DEFAULT NULL,
    id_buku INT NOT NULL,
    tanggal_reservasi DATE NOT NULL,
    status ENUM('menunggu', 'disetujui', 'dibatalkan', 'selesai') NOT NULL DEFAULT 'menunggu',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (id_admin) REFERENCES admin(id_admin)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE denda (
    id_denda INT AUTO_INCREMENT PRIMARY KEY,
    id_detail INT NOT NULL UNIQUE,
    jumlah_hari_terlambat INT NOT NULL DEFAULT 0,
    tanggal_bayar DATE DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_detail) REFERENCES detail_peminjaman(id_detail)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

CREATE INDEX idx_buku_judul ON buku(judul);
CREATE INDEX idx_buku_penulis ON buku(penulis);
CREATE INDEX idx_detail_status ON detail_peminjaman(status_pengembalian);
CREATE INDEX idx_peminjaman_tanggal ON peminjaman(tanggal_peminjaman);

-- ============================================================
-- DATA LAMA + PENYESUAIAN
-- Password admin: admin123
-- ============================================================
INSERT INTO admin
(id_admin, nama, email, password, role, jabatan_admin, no_hp_admin, created_at, updated_at)
VALUES
(1, 'Administrator', 'admin@perpus.test', '$2y$12$I9391PETEU21feRSRZOt7eIf9m1R4Ar/Dq0hJsFx3AgXxJ3CmQm1G', 'super_admin', 'Admin Perpustakaan', '-', '2026-05-09 05:20:21', '2026-05-09 05:20:21');

INSERT INTO anggota
(id_anggota, nim, nama, jurusan, total_denda, nama_anggota, email, password, no_telepon, alamat, status, created_at, updated_at)
VALUES
(1, 'E417001', 'Ahmad Fauzi', 'Teknologi Informasi', 0, 'Ahmad Fauzi', 'ahmad@student.polije.ac.id', '$2y$10$abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv', '081234567890', 'Jember', 'aktif', '2026-05-09 05:20:21', '2026-05-09 05:20:21'),
(2, 'E417002', 'Siti Aisyah', 'Teknologi Pertanian', 0, 'Siti Aisyah', 'siti@student.polije.ac.id', '$2y$10$abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv', '081234567891', 'Bondowoso', 'aktif', '2026-05-09 05:20:21', '2026-05-09 05:20:21'),
(3, 'E418', 'Idea Brilianta', 'Teknologi Informasi', 0, 'Idea Brilianta', 'e41@student.polije.ac.id', '$2y$10$abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv', '081234567899', 'Jember', 'aktif', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO kategori (id_kategori, nama_kategori, created_at, updated_at) VALUES
(1, 'Pemrograman', '2026-05-09 05:04:43', '2026-05-09 05:04:43'),
(2, 'Basis Data', '2026-05-09 05:04:43', '2026-05-09 05:04:43'),
(3, 'Matematika', '2026-05-09 05:04:43', '2026-05-09 05:04:43'),
(4, 'Jaringan', '2026-05-09 05:04:43', '2026-05-09 05:04:43');

INSERT INTO buku
(id_buku, isbn, judul, penulis, penerbit, tahun_terbit, copy, id_kategori, cover, total_stok, stok_tersedia, created_at, updated_at)
VALUES
(1, '9786020000001', 'Dasar-Dasar PHP', 'Budi Santoso', 'Informatika', '2023', 2, 1, NULL, 2, 1, '2026-05-09 05:29:08', '2026-05-09 05:29:08'),
(2, '9786020000002', 'MySQL untuk Pemula', 'Andi Wijaya', 'Elex Media', '2022', 3, 2, NULL, 3, 2, '2026-05-09 05:29:08', '2026-05-09 05:29:08'),
(3, '9786020000003', 'Matematika Diskrit', 'Siti Nurhaliza', 'Graha Ilmu', '2021', 1, 3, NULL, 1, 1, '2026-05-09 05:29:08', '2026-05-09 05:29:08'),
(4, '9786020000004', 'Konsep Jaringan Komputer', 'Rudi Hartono', 'Andi Offset', '2024', 2, 4, NULL, 2, 2, '2026-05-09 05:29:08', '2026-05-09 05:29:08');

INSERT INTO eksemplar (id_eksemplar, id_buku, status, created_at, updated_at) VALUES
(1, 1, 'dipinjam', '2026-05-09 05:29:08', '2026-05-09 06:07:36'),
(2, 1, 'tersedia', '2026-05-09 05:29:08', '2026-05-09 06:07:13'),
(3, 2, 'dipinjam', '2026-05-09 05:29:08', '2026-05-09 05:29:08'),
(4, 2, 'tersedia', '2026-05-09 05:29:08', '2026-05-09 05:29:08'),
(5, 2, 'tersedia', '2026-05-09 05:29:08', '2026-05-09 06:07:58'),
(6, 3, 'tersedia', '2026-05-09 05:29:08', '2026-05-09 05:29:08'),
(7, 4, 'tersedia', '2026-05-09 05:29:08', '2026-05-09 05:29:08'),
(8, 4, 'tersedia', '2026-05-09 05:29:08', '2026-05-09 05:29:08');

INSERT INTO peminjaman
(id_peminjaman, id_anggota, id_admin, tanggal_peminjaman, batas_waktu, laporan_hidden_at, id_buku, tanggal_pinjam, tanggal_jatuh_tempo, tanggal_kembali, status_pinjam, created_at, updated_at)
VALUES
(1, 1, 1, '2026-05-01', '2026-05-08', NULL, 1, '2026-05-01', '2026-05-08', '2026-05-09', 'returned', '2026-05-09 05:29:08', '2026-05-09 05:29:08'),
(2, 2, 1, '2026-05-03', '2026-05-10', NULL, 2, '2026-05-03', '2026-05-10', '2026-05-09', 'returned', '2026-05-09 05:29:08', '2026-05-09 05:29:08'),
(3, 1, 1, '2026-05-09', '2026-05-16', NULL, 1, '2026-05-09', '2026-05-16', '2026-05-09', 'returned', '2026-05-09 05:30:53', '2026-05-09 05:30:53'),
(4, 1, 1, '2026-05-09', '2026-05-23', NULL, 1, '2026-05-09', '2026-05-23', NULL, 'borrowed', '2026-05-09 06:07:36', '2026-05-09 06:07:49');

INSERT INTO detail_peminjaman
(id_detail, id_peminjaman, id_eksemplar, tanggal_kembali, status_pengembalian, extended_at, created_at, updated_at)
VALUES
(1, 1, 1, '2026-05-09', 'kembali', NULL, '2026-05-09 05:29:08', '2026-05-09 05:31:09'),
(2, 2, 3, '2026-05-09', 'kembali', NULL, '2026-05-09 05:29:08', '2026-05-09 05:29:08'),
(3, 3, 2, '2026-05-09', 'kembali', NULL, '2026-05-09 05:30:53', '2026-05-09 06:07:13'),
(4, 4, 1, NULL, 'dipinjam', '2026-05-09 06:07:49', '2026-05-09 06:07:36', '2026-05-09 06:07:49'),
(5, 4, 5, '2026-05-09', 'kembali', NULL, '2026-05-09 06:07:36', '2026-05-09 06:07:58');

INSERT INTO reservasi
(id_reservasi, id_anggota, id_admin, id_buku, tanggal_reservasi, status, created_at, updated_at)
VALUES
(1, 1, NULL, 3, '2026-05-09', 'menunggu', '2026-05-09 05:29:08', '2026-05-09 05:29:08'),
(2, 2, 1, 4, '2026-05-08', 'disetujui', '2026-05-09 05:29:08', '2026-05-09 05:29:08');

INSERT INTO denda
(id_denda, id_detail, jumlah_hari_terlambat, tanggal_bayar, created_at, updated_at)
VALUES
(1, 1, 1, NULL, '2026-05-09 05:29:08', '2026-05-09 05:29:08');
