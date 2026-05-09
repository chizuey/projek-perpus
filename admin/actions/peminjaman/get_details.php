<?php
require_once __DIR__ . '/../../../controllers/PeminjamanController.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode([]);
    exit;
}

$controller = new PeminjamanController();
$details = $controller->details($id);

header('Content-Type: application/json');
echo json_encode($details);
