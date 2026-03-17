<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
require 'db_connect.php';

$form_id = $_GET['form_id'] ?? null;

if (!$form_id) {
    die("ID Form tidak valid.");
}

// Ambil info form
if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
    $stmt = $pdo->prepare("SELECT title, form_schema_json FROM tb_link_form WHERE id = ?");
    $stmt->execute([$form_id]);
} else {
    $stmt = $pdo->prepare("SELECT title, form_schema_json FROM tb_link_form WHERE id = ? AND admin_id = ?");
    $stmt->execute([$form_id, $_SESSION['admin_id']]);
}
$form = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$form) {
    die("Form tidak ditemukan atau akses ditolak.");
}

// Ambil submissions
$stmt = $pdo->prepare("SELECT submission_data_json, submitted_at FROM tb_submissions WHERE form_id = ? ORDER BY submitted_at DESC");
$stmt->execute([$form_id]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Siapkan header CSV
$filename = "Export_" . preg_replace('/[^a-zA-Z0-9]/', '_', $form['title']) . "_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Header Kolom (Timestamp + Label Pertanyaan)
$fields = json_decode($form['form_schema_json'], true);
$headers = ['Waktu Submit'];
foreach ($fields as $field) {
    $headers[] = $field['label'];
}
fputcsv($output, $headers);

// Isi Data
foreach ($submissions as $sub) {
    $data = json_decode($sub['submission_data_json'], true);
    $row = [$sub['submitted_at']];
    
    foreach ($fields as $field) {
        $id = $field['id'];
        $val = $data[$id] ?? '';
        
        // Jika signature (base64), beri indikator teks karena terlalu panjang untuk CSV
        if ($field['type'] === 'signature' && strlen($val) > 100) {
            $val = "Tanda Tangan (Base64 Image)";
        }
        
        $row[] = $val;
    }
    fputcsv($output, $row);
}

fclose($output);
exit;
?>