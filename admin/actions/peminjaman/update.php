<?php

require_once __DIR__ . '/../../../controllers/PeminjamanController.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['action'] ?? '') !== 'perpanjang_peminjaman') {
    header('Location: ../../index.php?menu=peminjaman');
    exit;
}

$controller = new PeminjamanController();
$controller->extend($_POST);
