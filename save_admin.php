<?php
session_start();
// Keamanan: Hanya Super Admin yang boleh akses
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: dashboard.php");
    exit;
}
require 'db_connect.php';

// --- PROSES TAMBAH ADMIN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $nama = $_POST['nama_lengkap'];
    $user = $_POST['username'];
    $pass = $_POST['password'];
    
    // Hash password
    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO tb_admin (username, password, nama_lengkap, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$user, $hashed_pass, $nama]);
        header("Location: manage_admins.php?msg=Berhasil menambahkan admin baru!");
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            header("Location: manage_admins.php?msg=Gagal: Username sudah digunakan.");
        } else {
            header("Location: manage_admins.php?msg=Gagal: " . $e->getMessage());
        }
    }
    exit;
}

// --- PROSES HAPUS ADMIN ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    // Cegah menghapus diri sendiri (walaupun query select sudah memfilter)
    if ($id == $_SESSION['admin_id']) {
        header("Location: manage_admins.php?msg=Tidak bisa menghapus akun sendiri.");
        exit;
    }

    // Hapus admin (Formulir terkait akan error jika tidak ada ON DELETE CASCADE di DB, tapi untuk sekarang kita hapus adminnya saja)
    $stmt = $pdo->prepare("DELETE FROM tb_admin WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: manage_admins.php?msg=Admin berhasil dihapus.");
    exit;
}
?>