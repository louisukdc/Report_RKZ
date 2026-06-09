<?php
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');

// Only Admins can modify Master data. GET is open to User if needed, but let's restrict all to Admin.
requireApiRole('Admin');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $stmt = $conn->query("SELECT kode, Nama, harga FROM master ORDER BY Nama ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $data]);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $nama = isset($input['Nama']) ? trim($input['Nama']) : '';
            $harga = isset($input['harga']) ? floatval($input['harga']) : 0;

            if (empty($nama)) {
                echo json_encode(['status' => 'error', 'message' => 'Nama layanan tidak boleh kosong']);
                exit;
            }

            $stmt = $conn->prepare("INSERT INTO master (Nama, harga) VALUES (:nama, :harga)");
            $stmt->execute([':nama' => $nama, ':harga' => $harga]);
            
            echo json_encode(['status' => 'success', 'message' => 'Data layanan/barang berhasil ditambahkan']);
            break;

        case 'PATCH':
            $input = json_decode(file_get_contents('php://input'), true);
            $kode = isset($input['kode']) ? intval($input['kode']) : 0;
            $nama = isset($input['Nama']) ? trim($input['Nama']) : '';
            $harga = isset($input['harga']) ? floatval($input['harga']) : 0;

            if ($kode <= 0 || empty($nama)) {
                echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
                exit;
            }

            $stmt = $conn->prepare("UPDATE master SET Nama = :nama, harga = :harga WHERE kode = :kode");
            $stmt->execute([':nama' => $nama, ':harga' => $harga, ':kode' => $kode]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Data layanan berhasil diperbarui']);
            } else {
                echo json_encode(['status' => 'success', 'message' => 'Tidak ada perubahan data']);
            }
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            $kode = isset($input['kode']) ? intval($input['kode']) : 0;

            if ($kode <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Kode tidak valid']);
                exit;
            }

            $stmt = $conn->prepare("DELETE FROM master WHERE kode = :kode");
            $stmt->execute([':kode' => $kode]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Data layanan berhasil dihapus']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Metode tidak didukung']);
            break;
    }
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
