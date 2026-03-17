<?php
require 'db_connect.php';

echo "<h1>Update Super Admin</h1>";

try {
    // 1. Cek apakah kolom 'role' sudah ada di tabel tb_admin
    $check = $pdo->query("SHOW COLUMNS FROM tb_admin LIKE 'role'");
    if ($check->rowCount() == 0) {
        // Jika belum ada, tambahkan kolom role
        $pdo->exec("ALTER TABLE `tb_admin` ADD `role` ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin'");
        echo "✅ Kolom 'role' berhasil ditambahkan ke tabel database.<br>";
    } else {
        echo "ℹ️ Kolom 'role' sudah ada.<br>";
    }

    // 2. Ubah user 'admin' (atau ID 1) menjadi super_admin
    // Menggunakan username 'admin' sebagai target utama
    $stmt = $pdo->prepare("UPDATE tb_admin SET role = 'super_admin' WHERE username = 'admin' OR id = 1");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Akun admin utama berhasil diubah menjadi <b>Super Admin</b>.<br>";
    } else {
        echo "ℹ️ Akun admin utama mungkin sudah menjadi Super Admin sebelumnya.<br>";
    }

    echo "<br><hr><h3>Langkah Selanjutnya:</h3>";
    echo "Agar menu muncul, Anda HARUS Logout dan Login ulang.<br><br>";
    echo "<a href='logout.php' style='background:red; color:white; padding:10px; text-decoration:none; border-radius:5px;'>LOGOUT SEKARANG</a>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>