<?php
require 'db_connect.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID Submission tidak valid.");
}

// Ambil data submission gabung dengan info form
$stmt = $pdo->prepare("
    SELECT s.*, f.title, f.description, f.form_schema_json 
    FROM tb_submissions s 
    JOIN tb_link_form f ON s.form_id = f.id 
    WHERE s.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Data tidak ditemukan.");
}

$fields = json_decode($data['form_schema_json'], true);
$answers = json_decode($data['submission_data_json'], true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pendaftaran - <?= htmlspecialchars($data['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
            .page-break { page-break-inside: avoid; }
        }
        .a4-paper {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-100 py-10 font-sans">

    <!-- Tombol Print (Hanya muncul di layar) -->
    <div class="no-print fixed top-4 right-4 z-50">
        <button onclick="window.print()" class="bg-yellow-500 text-white px-6 py-3 rounded-lg shadow-lg font-bold hover:bg-yellow-600 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="a4-paper">
        <!-- Header -->
        <div class="border-b-2 border-gray-800 pb-6 mb-8 text-center">
            <!-- Logo Instansi: Pastikan file 'logo.png' ada di folder yang sama -->
            <!-- Ubah 'h-24' untuk mengatur tinggi logo -->
            <img src="logo.png" alt="Logo Instansi" class="h-24 mx-auto mb-4 object-contain" onerror="this.style.display='none'">
            
            <h1 class="text-3xl font-bold text-gray-900 uppercase"><?= htmlspecialchars($data['title']) ?></h1>
            <p class="text-gray-500 mt-2">Bukti Pendaftaran / Pengisian Formulir</p>
        </div>

        <!-- Info Dasar -->
        <div class="mb-8 bg-gray-50 p-4 rounded border border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-gray-500 block">ID Referensi:</span>
                    <span class="font-mono font-bold text-lg">#<?= str_pad($data['id'], 6, '0', STR_PAD_LEFT) ?></span>
                    <div class="mt-2">
                        <span class="text-gray-500 block">Waktu Submit:</span>
                        <span class="font-bold"><?= date('d F Y, H:i', strtotime($data['submitted_at'])) ?></span>
                    </div>
                </div>
                <div>
                    <!-- QR Code Validasi Data -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode("ID:" . $data['id'] . "|Form:" . $data['title']) ?>" alt="QR Validasi" class="border p-1 bg-white">
                </div>
            </div>
        </div>

        <!-- Detail Jawaban -->
        <div class="space-y-6">
            <?php foreach ($fields as $field): 
                $val = $answers[$field['id']] ?? '-';
            ?>
                <div class="page-break">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">
                        <?= htmlspecialchars($field['label']) ?>
                    </h3>
                    <div class="text-gray-900 text-lg border-b border-gray-100 pb-2">
                        <?php if ($field['type'] === 'signature' && !empty($val)): ?>
                            <img src="<?= $val ?>" alt="Tanda Tangan" class="h-24 border border-dashed border-gray-300 rounded p-2 bg-white">
                        <?php elseif ($field['type'] === 'file' && !empty($val)): ?>
                            <span class="text-yellow-600 italic"><i class="fas fa-paperclip"></i> <?= basename($val) ?> (File Terlampir)</span>
                        <?php else: ?>
                            <?= nl2br(htmlspecialchars($val)) ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer -->
        <div class="mt-12 pt-6 border-t border-gray-300 text-center text-sm text-gray-500">
            <p>Dokumen ini dicetak secara otomatis oleh sistem.</p>
            <p class="mt-1">Website ini di kembangkan oleh Lius Luaha | 0823 2542 9695 (Bagian Sistem Informasi Unit WR-III)
        </div>
    </div>

    <script>
        // Otomatis muncul dialog print saat halaman dibuka
        window.onload = function() { setTimeout(function(){ window.print(); }, 500); };
    </script>
</body>
</html>