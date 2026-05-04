CREATE DATABASE IF NOT EXISTS db_perpustakaan
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_perpustakaan;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS laporan_transaksi;
DROP TABLE IF EXISTS peminjaman;
DROP TABLE IF EXISTS buku;
DROP TABLE IF EXISTS penerbit;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS admin_profile;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    level ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_profile (
    id VARCHAR(20) PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    jabatan VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL,
    no_hp VARCHAR(30) NOT NULL DEFAULT '-',
    last_login VARCHAR(50) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kategori (
    id_kategori INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE penerbit (
    id_penerbit INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_penerbit VARCHAR(150) NOT NULL UNIQUE,
    alamat_penerbit VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE buku (
    id_buku INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(50) NULL,
    judul VARCHAR(200) NOT NULL,
    pengarang VARCHAR(150) NOT NULL,
    tahun_terbit YEAR NOT NULL,
    stok_tersedia INT UNSIGNED NOT NULL DEFAULT 0,
    total_stok INT UNSIGNED NOT NULL DEFAULT 0,
    cover_buku VARCHAR(255) NULL,
    id_kategori INT UNSIGNED NOT NULL,
    id_penerbit INT UNSIGNED NOT NULL,
    sinopsis TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_buku_judul (judul),
    KEY idx_buku_kategori (id_kategori),
    KEY idx_buku_penerbit (id_penerbit),
    CONSTRAINT fk_buku_kategori
        FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_buku_penerbit
        FOREIGN KEY (id_penerbit) REFERENCES penerbit(id_penerbit)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE peminjaman (
    id VARCHAR(40) PRIMARY KEY,
    nim VARCHAR(30) NOT NULL,
    nama VARCHAR(150) NOT NULL,
    id_buku INT UNSIGNED NULL,
    buku VARCHAR(200) NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    returned_at DATE NULL,
    extended_at DATE NULL,
    status_pinjam ENUM('borrowed', 'returned', 'overdue') NOT NULL DEFAULT 'borrowed',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_peminjaman_nim (nim),
    KEY idx_peminjaman_buku (id_buku),
    KEY idx_peminjaman_status (status_pinjam),
    CONSTRAINT fk_peminjaman_buku
        FOREIGN KEY (id_buku) REFERENCES buku(id_buku)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE laporan_transaksi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_id VARCHAR(40) NULL,
    tanggal DATE NOT NULL,
    peminjam VARCHAR(150) NOT NULL,
    judul_buku VARCHAR(200) NOT NULL,
    tgl_pinjam DATE NOT NULL,
    tgl_jatuh_tempo DATE NOT NULL,
    tgl_kembali DATE NULL,
    status ENUM('Dikembalikan', 'Terlambat', 'Belum Kembali') NOT NULL DEFAULT 'Belum Kembali',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_laporan_source_id (source_id),
    KEY idx_laporan_tanggal (tanggal),
    KEY idx_laporan_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (id, nama_lengkap, email, password, level) VALUES
(1, 'Admin', 'admin@polije.ac.id', '$2y$10$5S53Y.pue8JfLInpQmiM4Ot0Ry.8SeLTQIuqQz.ATsTdHh3ukPmuC', 'admin');

INSERT INTO admin_profile (id, nama, jabatan, email, no_hp, last_login) VALUES
('ADM001', 'Admin', 'Admin Perpustakaan', 'admin@polije.ac.id', '-', '');

INSERT INTO kategori (id_kategori, nama_kategori) VALUES
(1, 'Fiksi'),
(2, 'Fiksi Sejarah'),
(3, 'Non-Fiksi'),
(4, 'Edukasi'),
(5, 'Novel'),
(6, 'Sejarah'),
(7, 'Teknologi'),
(8, 'Sains');

INSERT INTO penerbit (id_penerbit, nama_penerbit, alamat_penerbit) VALUES
(1, 'Bentang Pustaka', NULL),
(2, 'Hasta Mitra', NULL),
(3, 'Kompas', NULL),
(4, 'Gramedia', NULL),
(5, 'KPG', NULL),
(6, 'Republika', NULL),
(7, 'Narasi', NULL),
(8, 'Informatika', NULL),
(9, 'Pearson', NULL),
(10, 'BPFE', NULL),
(11, 'Polije Press', NULL),
(12, 'Pt Fariz', NULL);

INSERT INTO buku
    (id_buku, isbn, judul, pengarang, tahun_terbit, stok_tersedia, total_stok, cover_buku, id_kategori, id_penerbit, sinopsis)
VALUES
(1, NULL, 'Laskar Pelangi', 'Andrea Hirata', 2005, 24, 24, NULL, 1, 1, NULL),
(2, NULL, 'Bumi Manusia', 'Pramoedya Ananta Toer', 1980, 12, 12, NULL, 2, 2, NULL),
(3, NULL, 'Filosofi Teras', 'Henry Manampiring', 2018, 45, 45, NULL, 3, 3, NULL),
(4, NULL, 'Atomic Habits', 'James Clear', 2019, 89, 89, NULL, 4, 4, NULL),
(5, NULL, 'Laut Bercerita', 'Leila S. Chudori', 2017, 18, 18, NULL, 2, 5, NULL),
(6, NULL, 'Cantik Itu Luka', 'Eka Kurniawan', 2002, 7, 7, NULL, 1, 4, NULL),
(7, NULL, 'Negeri 5 Menara', 'A. Fuadi', 2009, 30, 30, NULL, 1, 4, NULL),
(8, NULL, 'Pulang', 'Tere Liye', 2015, 15, 15, NULL, 5, 6, NULL),
(9, NULL, 'Madilog', 'Tan Malaka', 1943, 10, 10, NULL, 6, 7, NULL),
(10, NULL, 'Rich Dad Poor Dad', 'Robert T. Kiyosaki', 1997, 22, 22, NULL, 4, 4, NULL),
(11, NULL, 'Sapiens', 'Yuval Noah Harari', 2011, 14, 14, NULL, 6, 5, NULL),
(12, NULL, 'Bumi', 'Tere Liye', 2014, 28, 28, NULL, 5, 4, NULL),
(13, NULL, 'Algoritma', 'Rinaldi Munir', 2016, 20, 20, NULL, 7, 8, NULL),
(14, NULL, 'Basis Data', 'Fathansyah', 2018, 19, 19, NULL, 7, 8, NULL),
(15, NULL, 'Jaringan Komputer', 'Andrew S. Tanenbaum', 2011, 16, 16, NULL, 7, 9, NULL),
(16, NULL, 'Manajemen Keuangan', 'Suad Husnan', 2015, 10, 11, NULL, 4, 10, NULL),
(17, NULL, 'Pemrograman Java', 'Tim Perpustakaan', 2023, 4, 5, NULL, 7, 11, NULL),
(18, NULL, 'Teknik Elektro', 'Tim Perpustakaan', 2023, 5, 5, NULL, 7, 11, NULL),
(19, NULL, 'Vibe Coding', 'Fariz', 2026, 4, 5, NULL, 8, 12, NULL);

INSERT INTO peminjaman
    (id, nim, nama, id_buku, buku, tanggal_pinjam, tanggal_kembali, returned_at, extended_at, status_pinjam)
VALUES
('pjm_69f60c15ea0a33.40412529', '283628', 'Alafrezy', 19, 'Vibe Coding', '2026-05-02', '2026-05-09', NULL, NULL, 'borrowed'),
('pjm_69f350a89d7f21.71728820', '333333', 'Antok', 17, 'Pemrograman Java', '2026-04-30', '2026-05-14', NULL, '2026-04-30', 'borrowed'),
('pjm_69f1fead654796.63442227', '123462', 'Widya', 16, 'Manajemen Keuangan', '2026-04-25', '2026-05-04', NULL, NULL, 'borrowed');

INSERT INTO laporan_transaksi
    (id, source_id, tanggal, peminjam, judul_buku, tgl_pinjam, tgl_jatuh_tempo, tgl_kembali, status)
VALUES
(1, 'pjm_69f1fead6542d7.66330027', '2026-04-23', 'Dina', 'Basis Data', '2026-04-23', '2026-05-01', '2026-04-30', 'Dikembalikan'),
(2, 'pjm_69f1fead6546d3.15832446', '2026-04-15', 'Eko', 'Teknik Elektro', '2026-04-15', '2026-04-26', '2026-04-30', 'Dikembalikan'),
(3, 'pjm_69f1fead654796.63442227', '2026-04-25', 'Widya', 'Manajemen Keuangan', '2026-04-25', '2026-05-04', NULL, 'Belum Kembali'),
(4, 'pjm_69f1fead653bc4.62075702', '2026-04-26', 'Fajar', 'Pemrograman Web', '2026-04-26', '2026-05-05', '2026-04-30', 'Dikembalikan'),
(5, 'pjm_69f1fead6543d5.53504827', '2026-04-17', 'Budi', 'Jaringan Komputer', '2026-04-17', '2026-04-27', '2026-04-30', 'Dikembalikan'),
(6, 'pjm_69f34df4ab3fb5.93853495', '2026-04-30', 'Abdul', 'Algoritma', '2026-04-30', '2026-05-07', '2026-04-30', 'Dikembalikan'),
(7, 'pjm_69f350a89d7f21.71728820', '2026-04-30', 'Antok', 'Pemrograman Java', '2026-04-30', '2026-05-14', NULL, 'Belum Kembali'),
(8, 'pjm_69f3603d8357b0.74135335', '2026-04-30', 'Rezaa', 'Vibe Coding', '2026-04-30', '2026-05-07', '2026-04-30', 'Dikembalikan'),
(9, 'pjm_69f60c15ea0a33.40412529', '2026-05-02', 'Alafrezy', 'Vibe Coding', '2026-05-02', '2026-05-09', NULL, 'Belum Kembali');

ALTER TABLE users AUTO_INCREMENT = 2;
ALTER TABLE kategori AUTO_INCREMENT = 9;
ALTER TABLE penerbit AUTO_INCREMENT = 13;
ALTER TABLE buku AUTO_INCREMENT = 20;
ALTER TABLE laporan_transaksi AUTO_INCREMENT = 10;
