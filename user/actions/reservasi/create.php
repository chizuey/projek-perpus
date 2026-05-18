<?php
// Handle reservasi creation dari modal detail
// Response: JSON

header('Content-Type: application/json; charset=utf-8');

try {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    if (empty($_SESSION['id_anggota']) && !empty($_SESSION['id_user'])) {
        $_SESSION['id_anggota'] = $_SESSION['id_user'];
    }

    if (empty($_SESSION['id_anggota'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Silahkan login terlebih dahulu']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    require_once __DIR__ . '/../../../controllers/ReservasiController.php';

    $result = ReservasiController::create($_POST);
    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Reservasi gagal diproses: ' . $e->getMessage()
    ]);
}
