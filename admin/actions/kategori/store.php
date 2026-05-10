<?php

require_once __DIR__ . '/../../../controllers/KategoriController.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['action'] ?? '') !== 'add_kategori') {
    header('Location: ../../index.php?menu=kategori');
    exit;
}

(new KategoriController())->store($_POST);
