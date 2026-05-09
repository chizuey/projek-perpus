<?php
require_once __DIR__ . '/../../../controllers/PeminjamanController.php';

$peminjamanController = new PeminjamanController();
$result = $peminjamanController->report();
$dataTampil = $result['dataTampil'] ?? [];

$filename = "Laporan_Transaksi_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Tambahkan BOM untuk Excel agar mengenali UTF-8 dan pemisah kolom
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header CSV - Gunakan semicolon (;) sebagai pemisah agar tidak nyampur di Excel (Region Indonesia)
fputcsv($output, ['No', 'Nama Peminjam', 'ID Eksemplar', 'Tgl Pinjam', 'Tgl Kembali', 'Denda', 'Status'], ';');

$no = 1;
foreach ($dataTampil as $row) {
    fputcsv($output, [
        $no++,
        $row['peminjam'] ?? '',
        $row['id_eksemplar'] ?? '',
        date('d/m/Y', strtotime($row['tanggal_peminjaman'])),
        !empty($row['tanggal_kembali']) ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-',
        $row['denda'] ?: 'Rp 0',
        $row['status']
    ], ';');
}

fclose($output);
exit;
?>