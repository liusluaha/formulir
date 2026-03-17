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
        // 1. Ambil data formulir asli
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
            $stmt = $pdo->prepare("SELECT * FROM tb_link_form WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM tb_link_form WHERE id = ? AND admin_id = ?");
            $stmt->execute([$id, $_SESSION['admin_id']]);
        }
        $form = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($form) {
            // 2. Siapkan data baru
            $new_title = $form['title'] . " (Salinan)";
            $new_link = substr(md5(uniqid(rand(), true)), 0, 8); // Generate link baru
            
            // 3. Insert ke database
            $stmt = $pdo->prepare("INSERT INTO tb_link_form 
                (admin_id, unique_link_id, title, description, form_schema_json, limit_one_response, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, 0)"); // Default non-aktif dulu
            
            $stmt->execute([
                $_SESSION['admin_id'], // Pemiliknya adalah yang melakukan duplikasi
                $new_link,
                $new_title,
                $form['description'],
                $form['form_schema_json'],
                $form['limit_one_response']
            ]);
        }
    } catch (PDOException $e) {
        // Silent error or log
    }
}

header("Location: dashboard.php");
exit;
?>