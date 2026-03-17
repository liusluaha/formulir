<?php
session_start();
// Cek Login dan Cek Role Super Admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: dashboard.php");
    exit;
}
require 'db_connect.php';

// Ambil daftar admin (kecuali diri sendiri)
$stmt = $pdo->prepare("SELECT * FROM tb_admin WHERE id != ? ORDER BY id DESC");
$stmt->execute([$_SESSION['admin_id']]);
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6 max-w-4xl">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <a href="dashboard.php" class="text-blue-600 hover:underline"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard</a>
                <h1 class="text-3xl font-bold text-gray-800 mt-2">Kelola Admin</h1>
                <p class="text-gray-600">Buat akun untuk bawahan Anda agar mereka bisa membuat formulir.</p>
            </div>
        </div>

        <!-- Form Tambah Admin -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8 border-t-4 border-purple-600">
            <h2 class="text-xl font-bold mb-4">Tambah Admin Baru</h2>
            <form action="save_admin.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="w-full border rounded p-2" required placeholder="Contoh: Budi Staff">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" class="w-full border rounded p-2" required placeholder="Username login">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                    <input type="text" name="password" class="w-full border rounded p-2" required placeholder="Password login">
                </div>
                <div class="md:col-span-3 text-right">
                    <button type="submit" name="add_admin" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded">
                        <i class="fas fa-plus-circle mr-2"></i>Buat Akun Admin
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Admin -->
        <div class="bg-white shadow-md rounded-lg overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Username</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($admins)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-10 text-gray-500">Belum ada admin lain. Silakan tambahkan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 font-bold"><?= htmlspecialchars($admin['nama_lengkap']) ?></p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-600"><?= htmlspecialchars($admin['username']) ?></p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <span class="bg-gray-200 text-gray-700 text-xs font-semibold px-2 py-1 rounded">Admin Biasa</span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <a href="save_admin.php?delete_id=<?= $admin['id'] ?>" onclick="return confirm('Hapus admin ini? Semua formulir yang dibuat oleh admin ini juga akan terhapus!')" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if(isset($_GET['msg'])): ?>
    <script>
        alert("<?= htmlspecialchars($_GET['msg']) ?>");
        window.history.replaceState(null, null, window.location.pathname);
    </script>
    <?php endif; ?>
</body>
</html>