<?php

class Buku
{
    private string $file;

    public function __construct(?string $file = null)
    {
        $this->file = $file ?: __DIR__ . '/../admin/pages/data_buku.json';
    }

    public function all(): array
    {
        $data = array_map([$this, 'mapRow'], $this->read());

        usort($data, function (array $a, array $b): int {
            return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
        });

        return $data;
    }

    public function find(int $id): ?array
    {
        foreach ($this->read() as $row) {
            if ((int) ($row['id'] ?? $row['id_buku'] ?? 0) === $id) {
                return $this->mapRow($row);
            }
        }

        return null;
    }

    public function create(array $data): int
    {
        $rows = $this->read();
        $id = $this->nextId($rows);
        $rows[] = $this->normalizeForStorage($data, $id);
        $this->write($rows);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $rows = $this->read();

        foreach ($rows as $index => $row) {
            if ((int) ($row['id'] ?? $row['id_buku'] ?? 0) !== $id) {
                continue;
            }

            $current = $this->mapRow($row);
            $merged = array_merge($current, $data);

            if (trim((string) ($data['kategori'] ?? '')) === '') {
                $merged['kategori'] = $current['kategori'];
            }

            if (trim((string) ($data['cover_buku'] ?? '')) === '') {
                $merged['cover_buku'] = $current['cover'];
            }

            $rows[$index] = $this->normalizeForStorage($merged, $id);
            $this->write($rows);
            return;
        }
    }

    public function delete(int $id): void
    {
        $rows = array_values(array_filter($this->read(), function (array $row) use ($id): bool {
            return (int) ($row['id'] ?? $row['id_buku'] ?? 0) !== $id;
        }));

        $this->write($rows);
    }

    public function kategoriOptions(): array
    {
        $kategori = [];

        foreach ($this->read() as $row) {
            $nama = trim((string) ($row['kategori'] ?? $row['nama_kategori'] ?? ''));

            if ($nama !== '') {
                $kategori[] = $nama;
            }
        }

        $kategori = array_values(array_unique($kategori));
        natcasesort($kategori);

        return array_values($kategori);
    }

    public function countByTitle(string $judul, ?int $exceptId = null): int
    {
        $target = strtolower(trim($judul));
        $total = 0;

        foreach ($this->read() as $row) {
            $id = (int) ($row['id'] ?? $row['id_buku'] ?? 0);
            $current = strtolower(trim((string) ($row['judul'] ?? '')));

            if ($current === $target && (!$exceptId || $id !== $exceptId)) {
                $total++;
            }
        }

        return $total;
    }

    private function read(): array
    {
        if (!file_exists($this->file)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($this->file), true);

        return is_array($data) ? array_values($data) : [];
    }

    private function write(array $rows): void
    {
        $dir = dirname($this->file);

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents(
            $this->file,
            json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    private function nextId(array $rows): int
    {
        $max = 0;

        foreach ($rows as $row) {
            $max = max($max, (int) ($row['id'] ?? $row['id_buku'] ?? 0));
        }

        return $max + 1;
    }

    private function normalizeForStorage(array $data, int $id): array
    {
        $row = [
            'id' => $id,
            'judul' => trim((string) ($data['judul'] ?? '')),
            'penulis' => trim((string) ($data['penulis'] ?? $data['pengarang'] ?? '')),
            'penerbit' => trim((string) ($data['penerbit'] ?? '')),
            'tahun' => (int) ($data['tahun'] ?? 0),
            'kategori' => trim((string) ($data['kategori'] ?? '')),
            'stok' => max(0, (int) ($data['stok'] ?? $data['total_stok'] ?? 0)),
        ];

        $optionalFields = [
            'isbn' => $data['isbn'] ?? '',
            'tempat_terbit' => $data['tempat_terbit'] ?? '',
            'sinopsis' => $data['sinopsis'] ?? '',
            'cover' => $data['cover'] ?? $data['cover_buku'] ?? '',
        ];

        foreach ($optionalFields as $key => $value) {
            $value = trim((string) $value);

            if ($value !== '') {
                $row[$key] = $value;
            }
        }

        return $row;
    }

    private function activeBorrowCount(string $judul): int
    {
        $file = __DIR__ . '/../admin/pages/data_peminjaman.json';

        if (!file_exists($file)) {
            return 0;
        }

        $data = json_decode((string) file_get_contents($file), true);

        if (!is_array($data)) {
            return 0;
        }

        $total = 0;

        foreach ($data as $item) {
            $sameBook = trim((string) ($item['buku'] ?? '')) === $judul;
            $active = empty($item['returned_at']);

            if ($sameBook && $active) {
                $total++;
            }
        }

        return $total;
    }

    private function mapRow(array $row): array
    {
        $id = (int) ($row['id'] ?? $row['id_buku'] ?? 0);
        $judul = (string) ($row['judul'] ?? '');
        $stokTotal = max(0, (int) ($row['stok'] ?? $row['total_stok'] ?? $row['stok_tersedia'] ?? 0));
        $dipinjam = $this->activeBorrowCount($judul);
        $stokTersedia = max(0, $stokTotal - $dipinjam);
        $cover = (string) ($row['cover'] ?? $row['cover_buku'] ?? '');

        return [
            'id' => $id,
            'id_buku' => $id,
            'isbn' => $row['isbn'] ?? '',
            'judul' => $judul,
            'penulis' => $row['penulis'] ?? $row['pengarang'] ?? '',
            'pengarang' => $row['penulis'] ?? $row['pengarang'] ?? '',
            'penerbit' => $row['penerbit'] ?? $row['nama_penerbit'] ?? '',
            'tahun' => $row['tahun'] ?? $row['tahun_terbit'] ?? '',
            'kategori' => $row['kategori'] ?? $row['nama_kategori'] ?? '',
            'stok' => $stokTotal,
            'total_stok' => $stokTotal,
            'stok_tersedia' => $stokTersedia,
            'dipinjam' => $dipinjam,
            'cover' => $cover,
            'cover_buku' => $cover,
        ];
    }
}
