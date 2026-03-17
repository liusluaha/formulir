<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
require 'db_connect.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_lengkap'];
    $password = $_POST['password'];

    try {
        if (!empty($password)) {
            // Update Nama dan Password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE tb_admin SET nama_lengkap = ?, password = ? WHERE id = ?");
            $stmt->execute([$nama, $hashed_password, $_SESSION['admin_id']]);
        } else {
            // Update Nama saja
            $stmt = $pdo->prepare("UPDATE tb_admin SET nama_lengkap = ? WHERE id = ?");
            $stmt->execute([$nama, $_SESSION['admin_id']]);
        }
        
        $_SESSION['admin_name'] = $nama; // Update session nama
        $msg = "Profil berhasil diperbarui!";
    } catch (PDOException $e) {
        $msg = "Gagal memperbarui profil: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Pengaturan Profil</h2>
            <a href="dashboard.php" class="text-blue-600 hover:underline text-sm">Kembali</a>
        </div>

        <?php if ($msg): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= $msg ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($_SESSION['admin_name']) ?>" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password Baru (Opsional)</label>
                <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengganti" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                Simpan Perubahan
            </button>
        </form>
    </div>
</body>
</html>