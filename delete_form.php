<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
require 'db_connect.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Hapus formulir berdasarkan ID dan Admin ID (agar tidak menghapus punya orang lain)
        // Data di tb_submissions akan otomatis terhapus jika Foreign Key di-set ON DELETE CASCADE
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
            $stmt = $pdo->prepare("DELETE FROM tb_link_form WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM tb_link_form WHERE id = ? AND admin_id = ?");
            $stmt->execute([$id, $_SESSION['admin_id']]);
        }
    } catch (PDOException $e) {
        // Error handling jika diperlukan
    }
}

header("Location: dashboard.php");
exit;
?>