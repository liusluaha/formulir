<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak.']);
    exit;
}

require 'db_connect.php';
header('Content-Type: application/json');

$form_id = $_GET['id'] ?? null;

if (!$form_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID Formulir tidak disediakan.']);
    exit;
}

if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
    $stmt = $pdo->prepare("SELECT * FROM tb_link_form WHERE id = ?");
    $stmt->execute([$form_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM tb_link_form WHERE id = ? AND admin_id = ?");
    $stmt->execute([$form_id, $_SESSION['admin_id']]);
}
$form = $stmt->fetch(PDO::FETCH_ASSOC);

if ($form) {
    echo json_encode($form);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Formulir tidak ditemukan.']);
}
?>