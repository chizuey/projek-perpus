<?php

require_once __DIR__ . '/../../../controllers/BukuController.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['action'] ?? '') !== 'delete_eksemplar') {
    header('Location: ../../index.php?menu=databuku');
    exit;
}

(new BukuController())->deleteEksemplar($_POST);
