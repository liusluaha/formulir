<?php
require 'db_connect.php';

try {
    // Mengubah kolom unique_link_id menjadi VARCHAR(100) agar bisa menampung link panjang
    $sql = "ALTER TABLE tb_link_form MODIFY unique_link_id VARCHAR(100) NOT NULL";
    $pdo->exec($sql);
    
    echo "<h1>Berhasil!</h1>";
    echo "<p>Database telah diperbarui. Sekarang link bisa menampung hingga 100 karakter.</p>";
    echo "<p>Silakan edit kembali formulir Anda untuk memperbaiki link yang sebelumnya terpotong.</p>";
    echo "<a href='dashboard.php'>Kembali ke Dashboard</a>";
} catch (PDOException $e) {
    echo "<h1>Gagal</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>