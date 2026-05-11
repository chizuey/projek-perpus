<?php

require_once __DIR__ . '/../../../controllers/KategoriController.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['action'] ?? '') !== 'edit_kategori') {
    header('Location: ../../index.php?menu=kategori');
    exit;
}

(new KategoriController())->update($_POST);
