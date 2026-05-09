<?php

require_once __DIR__ . '/../config/database.php';

class Buku
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public function all()
    {
        $sql = "SELECT buku.*, kategori.nama_kategori, 
                       (SELECT COUNT(*) FROM eksemplar WHERE id_buku = buku.id_buku AND status = 'tersedia') as stok_tersedia
                FROM buku
                LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori
                ORDER BY buku.id_buku DESC";
        $result = $this->conn->query($sql);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Mapping agar sesuai dengan tampilan lama
            $row['id'] = $row['id_buku'];
            $row['penulis'] = $row['penulis'];
            $row['tahun'] = $row['tahun_terbit'];
            $row['kategori'] = $row['nama_kategori'];
            $row['stok'] = $row['copy']; // 'copy' adalah total stok fisik
            $data[] = $row;
        }
        return $data;
    }

    public function find($id)
    {
        $sql = "SELECT buku.*, kategori.nama_kategori
                FROM buku
                LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori
                WHERE buku.id_buku = $id";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        
        if ($row) {
            $row['id'] = $row['id_buku'];
            $row['tahun'] = $row['tahun_terbit'];
            $row['kategori'] = $row['nama_kategori'];
            $row['stok'] = $row['copy'];
        }
        return $row;
    }

    public function create($data)
    {
        $id_kategori = $this->getKategoriId($data['kategori']);
        $copy_count = (int)$data['stok'];
        
        $sql = "INSERT INTO buku (isbn, judul, penulis, penerbit, tahun_terbit, copy, id_kategori, cover) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssiis", 
            $data['isbn'], $data['judul'], $data['penulis'], $data['penerbit'], $data['tahun'], 
            $copy_count, $id_kategori, $data['cover']
        );
        
        if ($stmt->execute()) {
            $id_buku = $this->conn->insert_id;
            // Generate eksemplar fisik sesuai jumlah copy
            for ($i = 0; $i < $copy_count; $i++) {
                $this->conn->query("INSERT INTO eksemplar (id_buku, status) VALUES ($id_buku, 'tersedia')");
            }
            return true;
        }
        return false;
    }

    public function update($id, $data)
    {
        $id_kategori = $this->getKategoriId($data['kategori']);
        $new_copy_count = (int)$data['stok'];
        
        // Ambil jumlah copy lama
        $res = $this->conn->query("SELECT copy FROM buku WHERE id_buku = $id");
        $old_copy_count = $res->fetch_assoc()['copy'];

        $sql = "UPDATE buku SET isbn=?, judul=?, penulis=?, penerbit=?, tahun_terbit=?, copy=?, id_kategori=?, cover=? WHERE id_buku=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssiisi", 
            $data['isbn'], $data['judul'], $data['penulis'], $data['penerbit'], $data['tahun'], 
            $new_copy_count, $id_kategori, $data['cover'], $id
        );
        
        if ($stmt->execute()) {
            // Jika jumlah copy bertambah, tambah eksemplar
            if ($new_copy_count > $old_copy_count) {
                $diff = $new_copy_count - $old_copy_count;
                for ($i = 0; $i < $diff; $i++) {
                    $this->conn->query("INSERT INTO eksemplar (id_buku, status) VALUES ($id, 'tersedia')");
                }
            } 
            // Jika berkurang, hapus eksemplar yang sedang 'tersedia' (sederhana)
            elseif ($new_copy_count < $old_copy_count) {
                $diff = $old_copy_count - $new_copy_count;
                $this->conn->query("DELETE FROM eksemplar WHERE id_buku = $id AND status = 'tersedia' LIMIT $diff");
            }
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        // Eksemplar akan terhapus otomatis karena CONSTRAINT ON DELETE CASCADE di database
        $sql = "DELETE FROM buku WHERE id_buku = $id";
        return $this->conn->query($sql);
    }

    public function kategoriOptions()
    {
        $result = $this->conn->query("SELECT nama_kategori FROM kategori");
        $options = [];
        while ($row = $result->fetch_assoc()) {
            $options[] = $row['nama_kategori'];
        }
        return $options;
    }

    private function getKategoriId($nama)
    {
        $nama = $this->conn->real_escape_string($nama);
        $res = $this->conn->query("SELECT id_kategori FROM kategori WHERE nama_kategori = '$nama'");
        if ($row = $res->fetch_assoc()) {
            return $row['id_kategori'];
        }
        $this->conn->query("INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
        return $this->conn->insert_id;
    }

    public function countByTitle($judul, $exceptId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM buku WHERE judul = ?";
        if ($exceptId) $sql .= " AND id_buku != ?";
        
        $stmt = $this->conn->prepare($sql);
        if ($exceptId) {
            $stmt->bind_param("si", $judul, $exceptId);
        } else {
            $stmt->bind_param("s", $judul);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['total'];
    }
}
