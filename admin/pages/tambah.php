<?php
$dataBukuFile = __DIR__ . '/data_buku.json';
$errorsTambahBuku = [];
$oldTambahBuku = [
    'judul' => '',
    'penulis' => '',
    'penerbit' => '',
    'tahun' => '',
    'tempat_terbit' => '',
    'isbn' => '',
    'kategori' => [],
    'sinopsis' => '',
    'stok' => 5,
];

function eTambahBuku($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function loadTambahBukuData(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }

    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function saveTambahBukuData(string $file, array $data): void
{
    file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function nextTambahBukuId(array $data): int
{
    $maxId = 0;

    foreach ($data as $item) {
        $maxId = max($maxId, (int) ($item['id'] ?? 0));
    }

    return $maxId + 1;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $oldTambahBuku = [
        'judul' => trim($_POST['judul'] ?? ''),
        'penulis' => trim($_POST['penulis'] ?? ''),
        'penerbit' => trim($_POST['penerbit'] ?? ''),
        'tahun' => trim($_POST['tahun'] ?? ''),
        'tempat_terbit' => trim($_POST['tempat_terbit'] ?? ''),
        'isbn' => trim($_POST['isbn'] ?? ''),
        'kategori' => array_values(array_filter((array) ($_POST['kategori'] ?? []))),
        'sinopsis' => trim($_POST['sinopsis'] ?? ''),
        'stok' => max(0, (int) ($_POST['stok'] ?? 0)),
    ];

    if ($oldTambahBuku['judul'] === '') {
        $errorsTambahBuku[] = 'Judul buku wajib diisi.';
    }

    if ($oldTambahBuku['penulis'] === '') {
        $errorsTambahBuku[] = 'Penulis wajib diisi.';
    }

    if ($oldTambahBuku['penerbit'] === '') {
        $errorsTambahBuku[] = 'Penerbit wajib diisi.';
    }

    if ($oldTambahBuku['tahun'] === '' || !ctype_digit($oldTambahBuku['tahun'])) {
        $errorsTambahBuku[] = 'Tahun terbit wajib berupa angka.';
    }

    if (empty($oldTambahBuku['kategori'])) {
        $errorsTambahBuku[] = 'Pilih minimal satu kategori.';
    }

    if ($oldTambahBuku['stok'] < 1) {
        $errorsTambahBuku[] = 'Stok buku minimal 1.';
    }

    $dataBuku = loadTambahBukuData($dataBukuFile);

    foreach ($dataBuku as $item) {
        if (strcasecmp(trim((string) ($item['judul'] ?? '')), $oldTambahBuku['judul']) === 0) {
            $errorsTambahBuku[] = 'Judul buku sudah ada di Data Buku.';
            break;
        }
    }

    if (empty($errorsTambahBuku)) {
        $dataBuku[] = [
            'id' => nextTambahBukuId($dataBuku),
            'judul' => $oldTambahBuku['judul'],
            'penulis' => $oldTambahBuku['penulis'],
            'penerbit' => $oldTambahBuku['penerbit'],
            'tahun' => (int) $oldTambahBuku['tahun'],
            'kategori' => implode(', ', $oldTambahBuku['kategori']),
            'stok' => $oldTambahBuku['stok'],
            'tempat_terbit' => $oldTambahBuku['tempat_terbit'],
            'isbn' => $oldTambahBuku['isbn'],
            'sinopsis' => $oldTambahBuku['sinopsis'],
            'cover' => '',
        ];

        saveTambahBukuData($dataBukuFile, $dataBuku);
        echo '<script>window.location.href = "?menu=databuku";</script>';
        exit;
    }
}

$kategoriList = ['Investasi', 'Sains', 'Sejarah', 'Teknologi', 'Novel', 'Psikologi'];
?>

<div class="breadcrumb-bar">
    <a href="?menu=databuku" class="breadcrumb-back-btn">
        <i class="bi bi-chevron-left"></i>
    </a>
    <span class="breadcrumb-title">Tambah Buku</span>
</div>

<main class="tambah-content">
    <?php if (!empty($errorsTambahBuku)): ?>
        <div class="form-alert">
            <?= eTambahBuku(implode(' ', $errorsTambahBuku)); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="panel-card mb-3">
            <div class="panel-title">Input Data Buku</div>

            <label class="form-label-custom" for="judul">Judul Buku</label>
            <input class="form-input-custom" id="judul" name="judul" type="text" placeholder="Masukkan judul buku..." value="<?= eTambahBuku($oldTambahBuku['judul']); ?>" required>

            <label class="form-label-custom" for="penulis">Penulis</label>
            <input class="form-input-custom" id="penulis" name="penulis" type="text" placeholder="Masukkan nama penulis..." value="<?= eTambahBuku($oldTambahBuku['penulis']); ?>" required>

            <label class="form-label-custom" for="penerbit">Penerbit</label>
            <input class="form-input-custom" id="penerbit" name="penerbit" type="text" placeholder="Masukkan nama penerbit..." value="<?= eTambahBuku($oldTambahBuku['penerbit']); ?>" required>

            <label class="form-label-custom" for="tahun">Tahun Terbit</label>
            <input class="form-input-custom" id="tahun" name="tahun" type="number" min="0" placeholder="cth: 2023" value="<?= eTambahBuku($oldTambahBuku['tahun']); ?>" required>

            <label class="form-label-custom" for="tempat_terbit">Tempat Terbit</label>
            <input class="form-input-custom" id="tempat_terbit" name="tempat_terbit" type="text" placeholder="cth: Jakarta" value="<?= eTambahBuku($oldTambahBuku['tempat_terbit']); ?>">

            <label class="form-label-custom" for="isbn">ISBN</label>
            <input class="form-input-custom" id="isbn" name="isbn" type="text" placeholder="cth: 978-3-16-148410-0" value="<?= eTambahBuku($oldTambahBuku['isbn']); ?>">

            <span class="kategori-label">Kategori :</span>
            <div class="kategori-grid">
                <?php foreach ($kategoriList as $kategori): ?>
                    <label class="kategori-item">
                        <input
                            type="checkbox"
                            class="kategori-checkbox"
                            name="kategori[]"
                            value="<?= eTambahBuku($kategori); ?>"
                            <?= in_array($kategori, $oldTambahBuku['kategori'], true) ? 'checked' : ''; ?>
                        >
                        <span class="kategori-text"><?= eTambahBuku($kategori); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <span class="cover-section-title">Input Cover Buku</span>
            <label class="cover-upload-area" for="cover">
                <span class="cover-upload-hint">Klik untuk upload cover</span>
            </label>
            <input type="file" id="cover" name="cover" accept="image/*" class="cover-file-input">

            <label class="synopsis-label" for="sinopsis">Deskripsi/Sinopsis :</label>
            <textarea class="synopsis-area" id="sinopsis" name="sinopsis" placeholder="Masukkan deskripsi atau sinopsis buku..."><?= eTambahBuku($oldTambahBuku['sinopsis']); ?></textarea>

            <button type="submit" class="btn-tambahkan">Tambahkan</button>
        </div>

        <div class="panel-card">
            <div class="copy-panel-title">Copy Buku</div>
            <label class="form-label-custom" for="stok">Jumlah Copy / Stok</label>
            <input class="form-input-custom stock-input" id="stok" name="stok" type="number" min="1" value="<?= (int) $oldTambahBuku['stok']; ?>" required>
            <div class="copy-list-row" id="copyPreview" aria-live="polite"></div>
        </div>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const stokInput = document.getElementById('stok');
    const copyPreview = document.getElementById('copyPreview');

    function renderCopyPreview() {
        if (!stokInput || !copyPreview) {
            return;
        }

        const total = Math.max(1, parseInt(stokInput.value || '1', 10));
        const visible = Math.min(total, 12);
        copyPreview.innerHTML = '';

        for (let index = 1; index <= visible; index++) {
            const badge = document.createElement('span');
            badge.className = 'copy-badge';
            badge.textContent = 'b' + String(index).padStart(3, '0');
            copyPreview.appendChild(badge);
        }

        if (total > visible) {
            const badge = document.createElement('span');
            badge.className = 'copy-badge';
            badge.textContent = '+' + (total - visible);
            copyPreview.appendChild(badge);
        }
    }

    if (stokInput) {
        stokInput.addEventListener('input', renderCopyPreview);
        renderCopyPreview();
    }
});
</script>
