<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
require 'db_connect.php';

$form_id = $_GET['form_id'] ?? null;

if (!$form_id) {
    die("ID Form tidak valid.");
}

// Ambil info form dan verifikasi kepemilikan
if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
    $stmt = $pdo->prepare("SELECT * FROM tb_link_form WHERE id = ?");
    $stmt->execute([$form_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM tb_link_form WHERE id = ? AND admin_id = ?");
    $stmt->execute([$form_id, $_SESSION['admin_id']]);
}
$form = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$form) {
    die("Formulir tidak ditemukan atau akses ditolak.");
}

// --- LOGIKA PENCARIAN ---
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM tb_submissions WHERE form_id = ?";
$params = [$form_id];

if (!empty($search)) {
    $sql .= " AND submission_data_json LIKE ?";
    $params[] = "%$search%";
}
$sql .= " ORDER BY submitted_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Decode schema form untuk mendapatkan header tabel
$fields = json_decode($form['form_schema_json'], true);
if (!is_array($fields)) {
    $fields = []; // Antisipasi jika schema rusak
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lihat Jawaban</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <div class="mb-6">
            <a href="dashboard.php" class="text-blue-600 hover:underline"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard</a>
            <h1 class="text-3xl font-bold text-gray-800 mt-2">Jawaban untuk: "<?= htmlspecialchars($form['title']) ?>"</h1>
        </div>

        <!-- Form Pencarian -->
        <form method="GET" action="" class="mb-4 flex gap-2">
            <input type="hidden" name="form_id" value="<?= $form_id ?>">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama atau isi jawaban..." class="border border-gray-300 rounded p-2 w-full md:w-1/3 focus:outline-none focus:border-blue-500">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"><i class="fas fa-search"></i> Cari</button>
            <?php if(!empty($search)): ?>
                <a href="view_submissions.php?form_id=<?= $form_id ?>" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center">Reset</a>
            <?php endif; ?>
        </form>

        <div class="bg-white shadow-md rounded-lg overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Waktu Submit</th>
                        <?php foreach ($fields as $field): ?>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= htmlspecialchars($field['label']) ?></th>
                        <?php endforeach; ?>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr>
                            <td colspan="<?= count($fields) + 2 ?>" class="text-center py-10 text-gray-500">Data tidak ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($submissions as $sub): ?>
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-gray-600"><?= htmlspecialchars($sub['submitted_at']) ?></td>
                            <?php
                            $data = json_decode($sub['submission_data_json'], true);
                            foreach ($fields as $field):
                                $field_id = $field['id'];
                                $value = $data[$field_id] ?? '';
                            ?>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <?php
                                    if ($field['type'] === 'signature' && !empty($value)) {
                                        echo '<img src="' . $value . '" alt="Tanda Tangan" class="h-10 bg-gray-100 border rounded p-1">';
                                    } elseif ($field['type'] === 'file' && !empty($value)) {
                                        echo '<a href="' . htmlspecialchars($value) . '" target="_blank" class="text-blue-500 hover:underline">Lihat File</a>';
                                    } else {
                                        echo '<span class="text-gray-900">' . htmlspecialchars($value) . '</span>';
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <a href="print_submission.php?id=<?= $sub['id'] ?>" target="_blank" class="text-green-600 hover:text-green-900 mr-3" title="Cetak Bukti"><i class="fas fa-print"></i></a>
                                <a href="delete_submission.php?id=<?= $sub['id'] ?>&form_id=<?= $form_id ?>" onclick="return confirm('Yakin ingin menghapus data ini?')" class="text-red-600 hover:text-red-900" title="Hapus Data"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>