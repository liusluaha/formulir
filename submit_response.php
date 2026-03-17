<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Metode tidak diizinkan.");
}

$form_id = $_POST['form_id'] ?? null;

if (!$form_id) {
    die("ID Form tidak valid.");
}

// Ambil schema form untuk validasi field
$stmt = $pdo->prepare("SELECT form_schema_json, unique_link_id FROM tb_link_form WHERE id = ?");
$stmt->execute([$form_id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$form) {
    die("Form tidak ditemukan.");
}

$fields = json_decode($form['form_schema_json'], true);
if (!is_array($fields)) {
    die("Data struktur formulir tidak valid atau rusak.");
}

$submission_data = [];

// Proses setiap field
foreach ($fields as $field) {
    $field_id = $field['id'];
    $field_name = "field_" . $field_id;
    
    if ($field['type'] === 'file') {
        // Validasi Required untuk File
        if (!empty($field['required']) && $field['required']) {
            if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] === UPLOAD_ERR_NO_FILE) {
                die("Gagal: Pertanyaan '" . htmlspecialchars($field['label']) . "' wajib diisi.");
            }
        }

        // Handle File Upload
        if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
            $maxSizeMB = $field['maxSize'] ?? 10;
            $allowedTypes = $field['allowedTypes'] ?? [];

            // Validasi Ukuran
            if ($_FILES[$field_name]['size'] > $maxSizeMB * 1024 * 1024) {
                die("Gagal: File pada pertanyaan '" . htmlspecialchars($field['label']) . "' terlalu besar. Maksimal $maxSizeMB MB.");
            }

            // Validasi Tipe
            if (!empty($allowedTypes)) {
                $ext = strtolower(pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION));
                $validExtensions = [];
                $map = [
                    'PDF' => ['pdf'],
                    'Image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                    'Document' => ['doc', 'docx', 'txt', 'rtf'],
                    'Spreadsheet' => ['xls', 'xlsx', 'csv'],
                    'Presentation' => ['ppt', 'pptx']
                ];
                foreach ($allowedTypes as $t) {
                    if (isset($map[$t])) {
                        $validExtensions = array_merge($validExtensions, $map[$t]);
                    }
                }
                if (!in_array($ext, $validExtensions)) {
                    die("Gagal: Tipe file pada pertanyaan '" . htmlspecialchars($field['label']) . "' tidak diizinkan.");
                }
            }

            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $fileName = uniqid() . '_' . basename($_FILES[$field_name]['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES[$field_name]['tmp_name'], $targetPath)) {
                $submission_data[$field_id] = $targetPath;
            } else {
                $submission_data[$field_id] = "Gagal upload";
            }
        } else {
            $submission_data[$field_id] = "";
        }
    } else {
        // Handle Text/Radio/Checkbox/Signature
        $value = $_POST[$field_name] ?? '';
        
        // Validasi Required untuk Input Lain (termasuk Signature)
        if (!empty($field['required']) && $field['required']) {
            if (is_array($value)) {
                if (empty($value)) die("Gagal: Pertanyaan '" . htmlspecialchars($field['label']) . "' wajib diisi.");
            } else {
                if (trim($value) === '') die("Gagal: Pertanyaan '" . htmlspecialchars($field['label']) . "' wajib diisi.");
            }
        }

        if (is_array($value)) {
            $value = implode(", ", $value); // Untuk checkbox
        }
        
        $submission_data[$field_id] = $value;
    }
}

// Simpan ke database
try {
    $json_data = json_encode($submission_data);
    $stmt = $pdo->prepare("INSERT INTO tb_submissions (form_id, submission_data_json) VALUES (?, ?)");
    $stmt->execute([$form_id, $json_data]);
    $submission_id = $pdo->lastInsertId();
    
    // Set Cookie tanda sudah mengisi (Berlaku 1 tahun)
    setcookie('submitted_' . $form_id, '1', time() + (86400 * 365), "/");

    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Berhasil Terkirim</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
        <div class="bg-white p-10 rounded-xl shadow-2xl text-center max-w-lg w-full border-t-8 border-green-500">
            <div class="mb-6 flex justify-center">
                <div class="rounded-full bg-green-100 p-4">
                    <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800 mb-4 uppercase tracking-wide">
                JAWABAN ANDA<br>TELAH DIKIRIM
            </h1>
            <p class="text-gray-500 mb-8 text-lg">Terima kasih atas partisipasi Anda.</p>
            
            <a href="<?= htmlspecialchars($form['unique_link_id']) ?>" 
               class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-8 rounded-full transition transform hover:scale-105 shadow-lg">
                Kembali ke Formulir
            </a>
            
            <a href="print_submission.php?id=<?= $submission_id ?>" target="_blank"
               class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-full transition transform hover:scale-105 shadow-lg mt-4 md:mt-0 md:ml-4">
                <i class="fas fa-print mr-2"></i>Cetak Bukti (PDF)
            </a>
        </div>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    die("Gagal menyimpan jawaban: " . $e->getMessage());
}
?>