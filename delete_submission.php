<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
require 'db_connect.php';

$id = $_GET['id'] ?? null;
$form_id = $_GET['form_id'] ?? null;

if ($id && $form_id) {
    try {
        // Verifikasi hak akses sebelum menghapus
        // Pastikan submission ini milik form yang dimiliki oleh admin yang sedang login (atau super admin)
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
            $stmt = $pdo->prepare("DELETE FROM tb_submissions WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            // Cek apakah form_id milik admin ini
            $check = $pdo->prepare("SELECT id FROM tb_link_form WHERE id = ? AND admin_id = ?");
            $check->execute([$form_id, $_SESSION['admin_id']]);
            
            if ($check->rowCount() > 0) {
                $stmt = $pdo->prepare("DELETE FROM tb_submissions WHERE id = ?");
                $stmt->execute([$id]);
            }
        }
    } catch (PDOException $e) {
        // Error handling
    }
}

header("Location: view_submissions.php?form_id=" . $form_id);
exit;
?>