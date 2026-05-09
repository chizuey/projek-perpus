<?php
require_once __DIR__ . '/../models/Anggota.php';
require_once __DIR__ . '/../models/Peminjaman.php';

class MahasiswaController {
    private $anggotaModel;
    private $peminjamanModel;

    public function __construct() {
        $this->anggotaModel = new Anggota();
        $this->peminjamanModel = new Peminjaman();
    }

    public function getProfileData($id_user) {
        $user = $this->anggotaModel->getById($id_user);
        $peminjamanAktif = $this->anggotaModel->getPeminjamanAktif($id_user);
        $riwayat = $this->anggotaModel->getRiwayatPeminjaman($id_user);
        
        $totalDenda = 0;
        $totalTerlambat = 0;
        
        foreach ($riwayat as &$item) {
            $meta = $this->peminjamanModel->getMeta($item);
            $item['status_teks'] = $meta['status'];
            $item['denda_nilai'] = (int)str_replace(['Rp ', '.'], '', $meta['denda']);
            $item['terlambat_teks'] = $meta['terlambat'];
            
            if ($item['status_teks'] === 'Terlambat') {
                $totalTerlambat++;
            }
            $totalDenda += $item['denda_nilai'];
        }

        // Update total_denda di tabel anggota
        $this->anggotaModel->updateDenda($id_user, $totalDenda);

        return [
            'user' => $user,
            'peminjamanAktif' => $peminjamanAktif,
            'riwayat' => $riwayat,
            'stats' => [
                'total_pinjam' => count($riwayat),
                'total_terlambat' => $totalTerlambat,
                'total_denda' => $totalDenda,
                'slot_tersedia' => 3 - count($peminjamanAktif)
            ]
        ];
    }
}
