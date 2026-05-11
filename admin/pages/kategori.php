<?php
require_once __DIR__ . '/../../controllers/KategoriController.php';
$kategoriController = new KategoriController();
extract($kategoriController->index(), EXTR_SKIP);

$sedangEdit = !empty($editKategori);
$namaForm = $sedangEdit ? $editKategori['nama_kategori'] : ($oldKategori['nama_kategori'] ?? '');
?>

<section class="databuku-page">
    <div class="breadcrumb-bar" style="margin: -18px -24px 20px;">
        <a href="?menu=databuku" class="breadcrumb-back-btn">
            <i class="bi bi-chevron-left"></i>
        </a>
        <span class="breadcrumb-title">Kelola Kategori</span>
    </div>

    <?php if (!empty($errorsKategori)): ?>
        <div class="form-alert"><?= eKategori(implode(' ', $errorsKategori)); ?></div>
    <?php endif; ?>

    <?php if (!empty($successKategori)): ?>
        <div class="form-alert kategori-success"><?= eKategori($successKategori); ?></div>
    <?php endif; ?>

    <div class="panel-card">
        <div class="panel-title"><?= $sedangEdit ? 'Edit Kategori' : 'Tambah Kategori'; ?></div>
        <form
            method="post"
            action="<?= $sedangEdit ? 'actions/kategori/update.php' : 'actions/kategori/store.php'; ?>"
            class="kategori-form"
        >
            <input type="hidden" name="action" value="<?= $sedangEdit ? 'edit_kategori' : 'add_kategori'; ?>">
            <?php if ($sedangEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $editKategori['id_kategori']; ?>">
            <?php endif; ?>

            <label class="form-label-custom" for="nama_kategori">Nama Kategori</label>
            <div class="kategori-form-row">
                <input
                    class="form-input-custom"
                    id="nama_kategori"
                    name="nama_kategori"
                    type="text"
                    value="<?= eKategori($namaForm); ?>"
                    placeholder="Masukkan nama kategori..."
                    required
                >
                <button type="submit" class="btn-tambahkan">
                    <?= $sedangEdit ? 'Simpan Edit' : 'Tambah Kategori'; ?>
                </button>
                <?php if ($sedangEdit): ?>
                    <a href="?menu=kategori" class="btn-batal-form">Batal Edit</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="databuku-header">
        <h1>Daftar Kategori</h1>
    </div>

    <div class="databuku-table-wrap">
        <table class="databuku-table kategori-table">
            <colgroup>
                <col style="width: 8%;">
                <col style="width: 48%;">
                <col style="width: 18%;">
                <col style="width: 26%;">
            </colgroup>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Kategori</th>
                    <th>Jumlah Buku</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kategoriList)): ?>
                    <tr>
                        <td colspan="4" class="databuku-empty">Belum ada kategori.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($kategoriList as $index => $kategori): ?>
                        <tr>
                            <td><?= $index + 1; ?>.</td>
                            <td><?= eKategori($kategori['nama_kategori']); ?></td>
                            <td><?= (int) $kategori['jumlah_buku']; ?></td>
                            <td class="databuku-actions">
                                <a href="?menu=kategori&edit=<?= (int) $kategori['id_kategori']; ?>" class="btn-table-action">
                                    <i class="bi bi-pencil"></i>
                                    <span>Edit</span>
                                </a>
                                <form
                                    method="post"
                                    action="actions/kategori/delete.php"
                                    class="databuku-delete-form"
                                    onsubmit="return confirm('Yakin ingin menghapus kategori ini?')"
                                >
                                    <input type="hidden" name="action" value="delete_kategori">
                                    <input type="hidden" name="id" value="<?= (int) $kategori['id_kategori']; ?>">
                                    <button type="submit" class="btn-table-action btn-delete-book">
                                        <i class="bi bi-trash"></i>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
