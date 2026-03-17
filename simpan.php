<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak. Silakan login.']);
    exit;
}

require 'db_connect.php';

header('Content-Type: application/json');
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['title']) || !isset($data['fields'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Data tidak valid.']);
    exit;
}

$title = $data['title'];
$description = $data['description'] ?? '';
$custom_link = isset($data['custom_link']) ? trim($data['custom_link']) : '';
$limit_one_response = isset($data['limit_one_response']) ? (int)$data['limit_one_response'] : 0;
$form_schema_json = json_encode($data['fields']);
$admin_id = $_SESSION['admin_id'];

// Cek apakah ini mode EDIT atau mode BUAT BARU
$edit_id = $_GET['edit_id'] ?? null;

// --- LOGIKA VALIDASI LINK ---
// 1. Validasi karakter (Hanya huruf, angka, -, _)
if ($custom_link !== '' && !preg_match('/^[a-zA-Z0-9-_]+$/', $custom_link)) {
    http_response_code(400);
    echo json_encode(['error' => 'Link hanya boleh berisi huruf, angka, strip (-), dan underscore (_).']);
    exit;
}

// 2. Tentukan Link Final (Auto generate jika kosong)
$final_link = ($custom_link === '') ? substr(md5(uniqid(rand(), true)), 0, 8) : $custom_link;

// 3. Cek Unik (Apakah link sudah dipakai orang lain atau form lain?)
$stmt = $pdo->prepare("SELECT id FROM tb_link_form WHERE unique_link_id = ?");
$stmt->execute([$final_link]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    // Jika link ada di DB, cek apakah itu milik form yang sedang diedit?
    if ($edit_id && $existing['id'] == $edit_id) {
        // Aman, ini form yang sama
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Link "' . $final_link . '" sudah digunakan. Silakan ganti dengan yang lain.']);
        exit;
    }
}

try {
    if ($edit_id) {
        // --- MODE UPDATE ---
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
            $stmt = $pdo->prepare(
                "UPDATE tb_link_form SET title = ?, description = ?, form_schema_json = ?, unique_link_id = ?, limit_one_response = ? WHERE id = ?"
            );
            $stmt->execute([$title, $description, $form_schema_json, $final_link, $limit_one_response, $edit_id]);
        } else {
            $stmt = $pdo->prepare(
                "UPDATE tb_link_form SET title = ?, description = ?, form_schema_json = ?, unique_link_id = ?, limit_one_response = ? WHERE id = ? AND admin_id = ?"
            );
            $stmt->execute([$title, $description, $form_schema_json, $final_link, $limit_one_response, $edit_id, $admin_id]);
        }
        echo json_encode(['message' => 'Formulir berhasil diperbarui!']);

    } else {
        // --- MODE INSERT ---
        // Gunakan $final_link yang sudah diproses

        $stmt = $pdo->prepare(
            "INSERT INTO tb_link_form (admin_id, unique_link_id, title, description, form_schema_json, limit_one_response) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$admin_id, $final_link, $title, $description, $form_schema_json, $limit_one_response]);
        echo json_encode(['message' => 'Formulir berhasil disimpan!']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Operasi database gagal: ' . $e->getMessage()]);
}
?>
