<?php

require_once __DIR__ . '/../models/Anggota.php';
require_once __DIR__ . '/../models/Peminjaman.php';
require_once __DIR__ . '/../models/Reservasi.php';
require_once __DIR__ . '/../config/database.php';

class UserController
{
    private $anggotaModel;
    private $peminjamanModel;
    private $reservasiModel;
    private $conn;

    public function __construct()
    {
        $this->anggotaModel = new Anggota();
        $this->peminjamanModel = new Peminjaman();
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->reservasiModel = new Reservasi($this->conn);
    }

    public function dashboard($idUser)
    {
        $profile = $this->anggotaModel->findById($idUser);
        if (!$profile) {
            header("Location: ../auth/logout.php");
            exit();
        }

        $activeLoans = $this->anggotaModel->getActiveLoans($profile);
        $history = $this->anggotaModel->getLoanHistory($profile);
        
        // Calculate fines and status for active loans
        foreach ($activeLoans as &$loan) {
            $meta = $this->peminjamanModel->getMeta($loan);
            $loan['status_teks'] = $meta['status'];
            $loan['denda_teks'] = $meta['denda'];
            $loan['terlambat'] = $meta['terlambat'];
        }

        // Get active reservations
        $activeReservations = $this->getActiveReservations($profile['id_anggota']);

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
            'activeReservations' => $activeReservations,
            'history' => $history,
            'stats' => [
                'totalBuku' => $totalBukuDipinjam,
                'totalKeterlambatan' => $riwayatKeterlambatan,
                'totalDenda' => $totalDenda
            ]
        ];
    }

    private function getActiveReservations($idAnggota)
    {
        $sql = 'SELECT r.*, b.judul, b.cover, b.penulis
                FROM reservasi r
                JOIN buku b ON r.id_buku = b.id_buku
                WHERE r.id_anggota = ? AND r.status IN ("menunggu", "disetujui")
                ORDER BY r.tanggal_reservasi DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idAnggota);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}