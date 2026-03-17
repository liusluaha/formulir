<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
require 'db_connect.php';

// Logika untuk aktivasi/deaktivasi
if (isset($_GET['toggle_id'])) {
    $toggle_id = $_GET['toggle_id'];
    
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
        $stmt = $pdo->prepare("UPDATE tb_link_form SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$toggle_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE tb_link_form SET is_active = NOT is_active WHERE id = ? AND admin_id = ?");
        $stmt->execute([$toggle_id, $_SESSION['admin_id']]);
    }
    header("Location: dashboard.php");
    exit;
}

// --- LOGIKA PENCARIAN & PAGINASI ---
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 10; // Batas 10 formulir per halaman
$offset = ($page - 1) * $limit;

$params = [];

if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
    // --- SUPER ADMIN ---
    $whereClause = "WHERE 1=1";
    
    if (!empty($search)) {
        $whereClause .= " AND (f.title LIKE ? OR a.nama_lengkap LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Hitung Total Data
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM tb_link_form f LEFT JOIN tb_admin a ON f.admin_id = a.id $whereClause");
    $countStmt->execute($params);
    $total_records = $countStmt->fetchColumn();

    // Ambil Data dengan Limit
    $sql = "SELECT f.*, a.nama_lengkap as creator_name,
            (SELECT COUNT(*) FROM tb_submissions s WHERE s.form_id = f.id) as submission_count 
        FROM tb_link_form f 
        LEFT JOIN tb_admin a ON f.admin_id = a.id
        $whereClause
        ORDER BY f.created_at DESC LIMIT $limit OFFSET $offset";

} else {
    // --- ADMIN BIASA ---
    $whereClause = "WHERE f.admin_id = ?";
    $params[] = $_SESSION['admin_id'];

    if (!empty($search)) {
        $whereClause .= " AND f.title LIKE ?";
        $params[] = "%$search%";
    }

    // Hitung Total Data
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM tb_link_form f $whereClause");
    $countStmt->execute($params);
    $total_records = $countStmt->fetchColumn();

    // Ambil Data dengan Limit
    $sql = "SELECT f.*, 
            (SELECT COUNT(*) FROM tb_submissions s WHERE s.form_id = f.id) as submission_count 
        FROM tb_link_form f 
        $whereClause 
        ORDER BY f.created_at DESC LIMIT $limit OFFSET $offset";
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$forms = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_pages = ceil($total_records / $limit);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard Formulir</h1>
            <div class="flex flex-wrap items-center justify-start md:justify-end gap-x-4 gap-y-2">
                <span class="text-gray-600">Halo, <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
                <a href="profile.php" class="text-yellow-600 hover:underline"><i class="fas fa-user-circle"></i> Profil</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'): ?>
                    <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded">Super Admin</span>
                    <a href="manage_admins.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-users-cog mr-2"></i>Kelola Admin
                    </a>
                <?php endif; ?>
                <a href="logout.php" class="text-red-500 hover:underline">Logout</a>
            </div>
        </div>

        <div class="mb-6">
            <a href="admin.php" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-plus mr-2"></i> Buat Formulir Baru
            </a>
        </div>

        <!-- Form Pencarian -->
        <form method="GET" action="" class="mb-4 flex flex-col sm:flex-row gap-2">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari judul formulir..." class="border border-gray-300 rounded p-2 w-full sm:w-auto flex-grow focus:outline-none focus:border-yellow-500">
            <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600"><i class="fas fa-search"></i> Cari</button>
            <?php if(!empty($search)): ?>
                <a href="dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center justify-center">Reset</a>
            <?php endif; ?>
        </form>

        <div class="bg-white shadow-md rounded-lg overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Judul Formulir</th>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'): ?>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Pembuat</th>
                        <?php endif; ?>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Link</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Jawaban</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($forms)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">Tidak ada formulir yang ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($forms as $form): ?>
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap"><?= htmlspecialchars($form['title']) ?></p>
                                <p class="text-gray-600 whitespace-no-wrap text-xs">Dibuat: <?= date('d M Y', strtotime($form['created_at'])) ?></p>
                            </td>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'): ?>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm hidden md:table-cell">
                                <span class="text-gray-900 font-semibold"><?= htmlspecialchars($form['creator_name'] ?? 'Unknown') ?></span>
                            </td>
                            <?php endif; ?>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <div class="flex items-center gap-2">
                                    <a href="<?= $form['unique_link_id'] ?>" target="_blank" class="text-yellow-600 hover:underline truncate max-w-[150px]" title="/<?= $form['unique_link_id'] ?>">
                                        /<?= $form['unique_link_id'] ?>
                                    </a>
                                    <button type="button" onclick="copyLink('<?= $form['unique_link_id'] ?>')" class="text-gray-400 hover:text-yellow-600 transition-colors" title="Salin Link">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <?php if ($form['is_active']): ?>
                                    <span class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                                        <span aria-hidden class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                                        <span class="relative">Aktif</span>
                                    </span>
                                <?php else: ?>
                                    <span class="relative inline-block px-3 py-1 font-semibold text-red-900 leading-tight">
                                        <span aria-hidden class="absolute inset-0 bg-red-200 opacity-50 rounded-full"></span>
                                        <span class="relative">Non-Aktif</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                <span class="font-bold text-gray-700"><?= $form['submission_count'] ?></span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <a href="view_submissions.php?form_id=<?= $form['id'] ?>" class="text-yellow-600 hover:text-yellow-900" title="Lihat Jawaban"><i class="fas fa-eye"></i></a>
                                    <a href="admin.php?edit_id=<?= $form['id'] ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="javascript:void(0)" onclick="showQr('<?= $form['unique_link_id'] ?>', '<?= htmlspecialchars($form['title'], ENT_QUOTES) ?>')" class="text-purple-600 hover:text-purple-900" title="QR Code"><i class="fas fa-qrcode"></i></a>
                                    <a href="duplicate_form.php?id=<?= $form['id'] ?>" class="text-orange-500 hover:text-orange-700" title="Duplikasi Form"><i class="fas fa-copy"></i></a>
                                    <a href="dashboard.php?toggle_id=<?= $form['id'] ?>" class="text-gray-600 hover:text-gray-900" title="<?= $form['is_active'] ? 'Non-aktifkan' : 'Aktifkan' ?>"><i class="fas fa-power-off"></i></a>
                                    <a href="export.php?form_id=<?= $form['id'] ?>" class="text-green-600 hover:text-green-900" title="Export ke Excel"><i class="fas fa-file-excel"></i></a>
                                    <a href="delete_form.php?id=<?= $form['id'] ?>" onclick="return confirm('Yakin ingin menghapus formulir ini? Semua data jawaban akan hilang permanen.')" class="text-red-600 hover:text-red-900" title="Hapus"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex flex-wrap justify-center gap-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 bg-white border rounded hover:bg-gray-100 text-gray-700">Prev</a>
            <?php endif; ?>
            
            <span class="px-3 py-1 bg-gray-100 border rounded text-gray-700">Halaman <?= $page ?> dari <?= $total_pages ?></span>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 bg-white border rounded hover:bg-gray-100 text-gray-700">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal QR Code -->
    <div id="qrModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full text-center">
            <h3 id="qrTitle" class="text-lg font-bold mb-4 text-gray-800">Scan untuk Mengisi</h3>
            <div class="flex justify-center mb-4">
                <img id="qrImage" src="" alt="QR Code" class="border p-2 rounded">
            </div>
            <p class="text-sm text-gray-500 mb-4">Arahkan kamera HP ke kode di atas untuk membuka formulir.</p>
            <button onclick="document.getElementById('qrModal').classList.add('hidden'); document.getElementById('qrModal').classList.remove('flex')" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded w-full">
                Tutup
            </button>
        </div>
    </div>

    <script>
        function copyLink(linkId) {
            // Dapatkan URL dasar website saat ini
            const baseUrl = window.location.origin + window.location.pathname.replace('dashboard.php', '');
            const fullUrl = baseUrl + linkId;
            
            navigator.clipboard.writeText(fullUrl).then(() => {
                alert('Link berhasil disalin: ' + fullUrl);
            }).catch(err => {
                prompt("Salin link manual:", fullUrl);
            });
        }

        function showQr(linkId, title) {
            // Dapatkan URL dasar website saat ini
            const baseUrl = window.location.origin + window.location.pathname.replace('dashboard.php', '');
            const fullUrl = baseUrl + linkId;
            
            // Gunakan API QR Code (goqr.me)
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(fullUrl)}`;
            
            document.getElementById('qrTitle').innerText = title;
            document.getElementById('qrImage').src = qrUrl;
            
            const modal = document.getElementById('qrModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    </script>
</body>
</html>
