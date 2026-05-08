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
        $sql = "SELECT buku.*, kategori.nama_kategori, penerbit.nama_penerbit
                FROM buku
                LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori
                LEFT JOIN penerbit ON buku.id_penerbit = penerbit.id_penerbit
                ORDER BY buku.id_buku DESC";
        $result = $this->conn->query($sql);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Mapping sederhana agar sesuai dengan tampilan
            $row['id'] = $row['id_buku'];
            $row['penulis'] = $row['pengarang'];
            $row['penerbit'] = $row['nama_penerbit'];
            $row['tahun'] = $row['tahun_terbit'];
            $row['kategori'] = $row['nama_kategori'];
            $row['stok'] = $row['total_stok'];
            $data[] = $row;
        }
        return $data;
    }

    public function find($id)
    {
        $sql = "SELECT buku.*, kategori.nama_kategori, penerbit.nama_penerbit
                FROM buku
                LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori
                LEFT JOIN penerbit ON buku.id_penerbit = penerbit.id_penerbit
                WHERE buku.id_buku = $id";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        
        if ($row) {
            $row['id'] = $row['id_buku'];
            $row['penulis'] = $row['pengarang'];
            $row['penerbit'] = $row['nama_penerbit'];
            $row['tahun'] = $row['tahun_terbit'];
            $row['kategori'] = $row['nama_kategori'];
            $row['stok'] = $row['total_stok'];
        }
        return $row;
    }

    public function create($data)
    {
        $id_kategori = $this->getKategoriId($data['kategori']);
        $id_penerbit = $this->getPenerbitId($data['penerbit']);
        
        $sql = "INSERT INTO buku (isbn, judul, pengarang, tahun_terbit, stok_tersedia, total_stok, cover_buku, id_kategori, id_penerbit) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssiiisii", 
            $data['isbn'], $data['judul'], $data['penulis'], $data['tahun'], 
            $data['stok'], $data['stok'], $data['cover_buku'], $id_kategori, $id_penerbit
        );
        return $stmt->execute();
    }

    public function update($id, $data)
    {
        $id_kategori = $this->getKategoriId($data['kategori']);
        $id_penerbit = $this->getPenerbitId($data['penerbit']);
        $stok = $data['stok'];
        
        if (!empty($data['cover_buku'])) {
            $sql = "UPDATE buku SET isbn=?, judul=?, pengarang=?, tahun_terbit=?, total_stok=?, stok_tersedia=?, cover_buku=?, id_kategori=?, id_penerbit=? WHERE id_buku=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssiiiisii", 
                $data['isbn'], $data['judul'], $data['penulis'], $data['tahun'], 
                $stok, $stok, $data['cover_buku'], $id_kategori, $id_penerbit, $id
            );
        } else {
            $sql = "UPDATE buku SET isbn=?, judul=?, pengarang=?, tahun_terbit=?, total_stok=?, stok_tersedia=?, id_kategori=?, id_penerbit=? WHERE id_buku=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssiiiiii", 
                $data['isbn'], $data['judul'], $data['penulis'], $data['tahun'], 
                $stok, $stok, $id_kategori, $id_penerbit, $id
            );
        }
        return $stmt->execute();
    }

    public function delete($id)
    {
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

    private function getPenerbitId($nama)
    {
        $nama = $this->conn->real_escape_string($nama);
        $res = $this->conn->query("SELECT id_penerbit FROM penerbit WHERE nama_penerbit = '$nama'");
        if ($row = $res->fetch_assoc()) {
            return $row['id_penerbit'];
        }
        $this->conn->query("INSERT INTO penerbit (nama_penerbit) VALUES ('$nama')");
        return $this->conn->insert_id;
    }

    public function countByTitle($judul, $exceptId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM buku WHERE judul = '$judul'";
        if ($exceptId) $sql .= " AND id_buku != $exceptId";
        $res = $this->conn->query($sql);
        $row = $res->fetch_assoc();
        return $row['total'];
    }
}
