-- ============================================================
-- DATABASE SISTEM PERPUSTAKAAN
-- Final Structure (3NF) - 2026-05-09
-- ============================================================

-- ============================================================
-- 1. ADMIN
-- ============================================================
CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'petugas') NOT NULL DEFAULT 'petugas',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- 2. ANGGOTA
-- ============================================================
CREATE TABLE IF NOT EXISTS anggota (
    id_anggota INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_telepon VARCHAR(20),
    alamat TEXT,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- 3. KATEGORI
-- ============================================================
CREATE TABLE IF NOT EXISTS kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- 4. BUKU
-- ============================================================
CREATE TABLE IF NOT EXISTS buku (
    id_buku INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) UNIQUE,
    judul VARCHAR(255) NOT NULL,
    penulis VARCHAR(255) NOT NULL,
    penerbit VARCHAR(255) NOT NULL,
    tahun_terbit YEAR NOT NULL,
    copy INT NOT NULL DEFAULT 0,
    id_kategori INT NOT NULL,
    cover VARCHAR(255) DEFAULT NULL,
    stok_tersedia INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_buku_kategori
        FOREIGN KEY (id_kategori)
        REFERENCES kategori(id_kategori)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- ============================================================
-- 5. EKSEMPLAR
-- Menyimpan copy fisik setiap buku
-- ============================================================
CREATE TABLE IF NOT EXISTS eksemplar (
    id_eksemplar INT AUTO_INCREMENT PRIMARY KEY,
    id_buku INT NOT NULL,
    status ENUM('tersedia', 'direservasi', 'dipinjam', 'rusak', 'hilang') NOT NULL DEFAULT 'tersedia',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_eksemplar_buku
        FOREIGN KEY (id_buku)
        REFERENCES buku(id_buku)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- ============================================================
-- 6. PEMINJAMAN (HEADER TRANSAKSI)
-- 1 transaksi dapat berisi banyak buku
-- ============================================================
CREATE TABLE IF NOT EXISTS peminjaman (
    id_peminjaman INT AUTO_INCREMENT PRIMARY KEY,
    id_anggota INT NOT NULL,
    id_admin INT NOT NULL,
    tanggal_peminjaman DATE NOT NULL,
    batas_waktu DATE NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    laporan_hidden_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_peminjaman_anggota
        FOREIGN KEY (id_anggota)
        REFERENCES anggota(id_anggota)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_peminjaman_admin
        FOREIGN KEY (id_admin)
        REFERENCES admin(id_admin)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- ============================================================
-- 7. DETAIL_PEMINJAMAN
-- 1 row = 1 eksemplar yang dipinjam
-- ============================================================
CREATE TABLE IF NOT EXISTS detail_peminjaman (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_peminjaman INT NOT NULL,
    id_eksemplar INT NOT NULL,
    tanggal_kembali DATE NULL,
    status_pengembalian ENUM('dipinjam', 'kembali', 'hilang') NOT NULL DEFAULT 'dipinjam',
    extended_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_detail_peminjaman
        FOREIGN KEY (id_peminjaman)
        REFERENCES peminjaman(id_peminjaman)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_detail_eksemplar
        FOREIGN KEY (id_eksemplar)
        REFERENCES eksemplar(id_eksemplar)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- ============================================================
-- 8. RESERVASI
-- Reservasi dilakukan berdasarkan judul buku
-- ============================================================
CREATE TABLE IF NOT EXISTS reservasi (
    id_reservasi INT AUTO_INCREMENT PRIMARY KEY,
    id_anggota INT NOT NULL,
    id_admin INT NULL,
    id_buku INT NOT NULL,
    id_eksemplar INT NULL,
    tanggal_reservasi DATE NOT NULL,
    status ENUM('menunggu', 'disetujui', 'dibatalkan', 'selesai') NOT NULL DEFAULT 'menunggu',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reservasi_anggota
        FOREIGN KEY (id_anggota)
        REFERENCES anggota(id_anggota)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_reservasi_admin
        FOREIGN KEY (id_admin)
        REFERENCES admin(id_admin)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_reservasi_buku
        FOREIGN KEY (id_buku)
        REFERENCES buku(id_buku)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_reservasi_eksemplar
        FOREIGN KEY (id_eksemplar)
        REFERENCES eksemplar(id_eksemplar)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- ============================================================
-- 9. DENDA
-- Denda per detail peminjaman (per eksemplar)
-- ============================================================
CREATE TABLE IF NOT EXISTS denda (
    id_denda INT AUTO_INCREMENT PRIMARY KEY,
    id_detail INT NOT NULL UNIQUE,
    jumlah_hari_terlambat INT NOT NULL DEFAULT 0,
    tanggal_bayar DATE NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_denda_detail
        FOREIGN KEY (id_detail)
        REFERENCES detail_peminjaman(id_detail)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- ============================================================
-- INDEX TAMBAHAN (OPSIONAL)
-- ============================================================
CREATE INDEX idx_buku_judul ON buku(judul);
CREATE INDEX idx_buku_penulis ON buku(penulis);
CREATE INDEX idx_peminjaman_tanggal ON peminjaman(tanggal_peminjaman);
CREATE INDEX idx_detail_status ON detail_peminjaman(status_pengembalian);

-- ============================================================
-- CONTOH DATA KATEGORI
-- ============================================================
INSERT INTO kategori (nama_kategori) VALUES
('Pemrograman'),
('Basis Data'),
('Matematika'),
('Jaringan')
ON DUPLICATE KEY UPDATE nama_kategori = VALUES(nama_kategori);
