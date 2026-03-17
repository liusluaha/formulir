<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM tb_admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        // Login berhasil
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['nama_lengkap'];
        $_SESSION['role'] = $admin['role']; // Simpan role (super_admin/admin)
        header("Location: dashboard.php");
        exit();
    } else {
        // Login gagal
        header("Location: index.php?error=Username atau password salah");
        exit();
    }
}
?>
