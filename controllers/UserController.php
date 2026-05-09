<?php

require_once __DIR__ . '/../models/Anggota.php';
require_once __DIR__ . '/../models/Peminjaman.php';

class UserController
{
    private $anggotaModel;
    private $peminjamanModel;

    public function __construct()
    {
        $this->anggotaModel = new Anggota();
        $this->peminjamanModel = new Peminjaman();
    }

    public function dashboard($idUser)
    {
        $profile = $this->anggotaModel->findById($idUser);
        if (!$profile) {
            header("Location: ../auth/logout.php");
            exit();
        }

        $activeLoans = $this->anggotaModel->getActiveLoans($idUser);
        $history = $this->anggotaModel->getLoanHistory($idUser);
        
        // Calculate fines and status for active loans
        foreach ($activeLoans as &$loan) {
            $meta = $this->peminjamanModel->getMeta($loan);
            $loan['status_teks'] = $meta['status'];
            $loan['denda_teks'] = $meta['denda'];
            $loan['terlambat'] = $meta['terlambat'];
        }

        // Calculate stats
        $totalBukuDipinjam = count($history);
        $riwayatKeterlambatan = 0;
        $totalDenda = 0;

        foreach ($history as &$h) {
            $meta = $this->peminjamanModel->getMeta($h);
            $h['status_teks'] = $meta['status'];
            $h['denda_teks'] = $meta['denda'];
            $h['terlambat'] = $meta['terlambat'];
            
            if ($meta['status'] === 'Terlambat') {
                $riwayatKeterlambatan++;
            }
            
            // Extract numeric denda
            $dendaNumeric = (int)str_replace(['Rp ', '.', ','], '', $meta['denda']);
            $totalDenda += $dendaNumeric;
        }

        // Sync total denda to database
        if ($totalDenda != $profile['total_denda']) {
            $this->anggotaModel->updateDenda($idUser, $totalDenda);
            $profile['total_denda'] = $totalDenda;
        }

        return [
            'profile' => $profile,
            'activeLoans' => $activeLoans,
            'history' => $history,
            'stats' => [
                'totalBuku' => $totalBukuDipinjam,
                'totalKeterlambatan' => $riwayatKeterlambatan,
                'totalDenda' => $totalDenda
            ]
        ];
    }
}
