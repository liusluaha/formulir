<?php
require 'db_connect.php';

$link_id = $_GET['link'] ?? '';

if (!$link_id) {
    die("Link tidak valid.");
}

// Ambil data form berdasarkan unique_link_id
$stmt = $pdo->prepare("SELECT * FROM tb_link_form WHERE unique_link_id = ?");
$stmt->execute([$link_id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$form) {
    die("Formulir tidak ditemukan.");
}

if (!$form['is_active']) {
    die("Formulir ini sedang tidak aktif.");
}

// Cek apakah form dibatasi 1 respon dan user sudah pernah mengisi (via Cookie)
if ($form['limit_one_response'] == 1 && isset($_COOKIE['submitted_' . $form['id']])) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sudah Diisi</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
        <div class="bg-white p-8 rounded-lg shadow-md text-center max-w-md w-full border-t-4 border-yellow-500">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Anda Sudah Mengisi</h1>
            <p class="text-gray-600">Formulir ini hanya memperbolehkan satu kali pengisian per pengguna.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$fields = json_decode($form['form_schema_json'], true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($form['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen py-10 px-4 font-sans">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header Form -->
        <div class="bg-yellow-500 p-6 text-white border-b-4 border-yellow-700">
            <h1 class="text-3xl font-bold"><?= htmlspecialchars($form['title']) ?></h1>
            <?php if (!empty($form['description'])): ?>
                <p class="mt-2 opacity-90 text-white"><?= htmlspecialchars($form['description']) ?></p>
            <?php endif; ?>
        </div>

        <form action="submit_response.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            <input type="hidden" name="form_id" value="<?= $form['id'] ?>">
            
            <?php foreach ($fields as $field) { ?>
                <div class="space-y-2">
                    <label class="block font-semibold text-gray-700 text-lg">
                        <?= htmlspecialchars($field['label']) ?>
                        <?php if ($field['required']) { ?>
                            <span class="text-red-500">*</span>
                        <?php } ?>
                    </label>

                    <?php 
                    $required = $field['required'] ? 'required' : '';
                    $name = "field_" . $field['id'];
                    
                    switch ($field['type']) {
                        case 'text': 
                            $inputType = (isset($field['validationType']) && $field['validationType'] === 'number') ? 'number' : 'text';
                            ?>
                            <input type="<?= $inputType ?>" name="<?= $name ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:border-yellow-500 focus:ring-yellow-500 p-3 border" <?= $required ?>>
                            <?php break;
                        
                        case 'textarea': ?>
                            <textarea name="<?= $name ?>" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-yellow-500 focus:ring-yellow-500 p-3 border" <?= $required ?>></textarea>
                            <?php break;

                        case 'radio': 
                            foreach (($field['options'] ?? []) as $opt) { ?>
                                <div class="flex items-center mb-2">
                                    <input type="radio" name="<?= $name ?>" value="<?= htmlspecialchars($opt) ?>" class="h-5 w-5 text-yellow-600 border-gray-300 focus:ring-yellow-500" <?= $required ?>>
                                    <label class="ml-3 block text-gray-700"><?= htmlspecialchars($opt) ?></label>
                                </div>
                            <?php }
                            break;

                        case 'checkbox': 
                            foreach (($field['options'] ?? []) as $opt) { ?>
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" name="<?= $name ?>[]" value="<?= htmlspecialchars($opt) ?>" class="h-5 w-5 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500">
                                    <label class="ml-3 block text-gray-700"><?= htmlspecialchars($opt) ?></label>
                                </div>
                            <?php }
                            break;

                        case 'select': ?>
                            <select name="<?= $name ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:border-yellow-500 focus:ring-yellow-500 p-3 border" <?= $required ?>>
                                <option value="">-- Pilih Salah Satu --</option>
                                <?php foreach (($field['options'] ?? []) as $opt) { ?>
                                    <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                <?php } ?>
                            </select>
                            <?php break;

                        case 'date': ?>
                            <input type="date" name="<?= $name ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:border-yellow-500 focus:ring-yellow-500 p-3 border" <?= $required ?>>
                            <?php break;

                        case 'time': ?>
                            <input type="time" name="<?= $name ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:border-yellow-500 focus:ring-yellow-500 p-3 border" <?= $required ?>>
                            <?php break;

                        case 'file': 
                            $maxSize = $field['maxSize'] ?? 10;
                            $allowedTypes = $field['allowedTypes'] ?? [];
                            $accept = "";
                            $typeMap = [
                                'PDF' => '.pdf',
                                'Image' => 'image/*',
                                'Document' => '.doc,.docx,.txt,.rtf',
                                'Spreadsheet' => '.xls,.xlsx,.csv',
                                'Presentation' => '.ppt,.pptx'
                            ];
                            if (!empty($allowedTypes)) {
                                $accepts = [];
                                foreach ($allowedTypes as $t) {
                                    if (isset($typeMap[$t])) $accepts[] = $typeMap[$t];
                                }
                                $accept = implode(',', $accepts);
                            }
                            ?>
                            <input type="file" name="<?= $name ?>" accept="<?= $accept ?>" onchange="validateFileSize(this, <?= $maxSize ?>)" class="w-full border-gray-300 rounded-md shadow-sm p-2 border bg-gray-50" <?= $required ?>>
                            <p class="text-xs text-gray-500 mt-1">Maksimal: <?= $maxSize ?> MB. <?= !empty($allowedTypes) ? 'Tipe: ' . implode(', ', $allowedTypes) : '' ?></p>
                            <?php break;

                        case 'signature': ?>
                            <div class="border-2 border-dashed border-gray-300 rounded p-4 bg-gray-50 text-center relative">
                                <canvas id="canvas-<?= $field['id'] ?>" width="400" height="200" class="border border-gray-400 bg-white mx-auto touch-none shadow-sm"></canvas>
                                <input type="text" name="<?= $name ?>" id="input-<?= $field['id'] ?>" <?= $required ?> style="opacity: 0; position: absolute; left: 50%; top: 50%; height: 1px; width: 1px; z-index: -1;" oninvalid="this.setCustomValidity('Mohon tanda tangani bidang ini')" oninput="this.setCustomValidity('')">
                                <button type="button" onclick="clearSignature('<?= $field['id'] ?>')" class="mt-3 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm font-medium">Hapus Tanda Tangan</button>
                            </div>
                            <script>
                                (function() {
                                    const canvas = document.getElementById('canvas-<?= $field['id'] ?>');
                                    const signaturePad = new SignaturePad(canvas);
                                    const input = document.getElementById('input-<?= $field['id'] ?>');
                                    
                                    signaturePad.addEventListener("endStroke", () => {
                                        if (!signaturePad.isEmpty()) {
                                            input.value = signaturePad.toDataURL();
                                            input.setCustomValidity(''); // Hapus pesan error saat sudah diisi
                                        }
                                    });
                                    window.signaturePads = window.signaturePads || {};
                                    window.signaturePads['<?= $field['id'] ?>'] = signaturePad;
                                })();
                            </script>
                            <?php break;
                    } ?>
                </div>
            <?php } ?>

            <div class="pt-6 border-t border-gray-100">
                <button type="submit" class="w-full bg-yellow-500 text-white font-bold py-4 px-6 rounded-lg hover:bg-yellow-600 transition duration-200 shadow-lg transform active:scale-95">
                    Kirim Jawaban
                </button>
            </div>
        </form>
    </div>
    <div class="text-center text-gray-500 text-sm mt-8">
    Website ini di kembangkan oleh Lius Luaha | 0823 2542 9695 (Bagian Sistem Informasi Unit WR-III)
    </div>
    <script>
        function clearSignature(id){
            if(window.signaturePads&&window.signaturePads[id]){
                window.signaturePads[id].clear();
                document.getElementById('input-'+id).value='';
            }
        }
        function validateFileSize(input, maxSizeMB) {
            if (input.files && input.files[0]) {
                const fileSize = input.files[0].size / 1024 / 1024; // in MB
                if (fileSize > maxSizeMB) {
                    alert('Ukuran file terlalu besar! Maksimal ' + maxSizeMB + ' MB.');
                    input.value = ''; // Reset input
                }
            }
        }
    </script>
</body>
</html>