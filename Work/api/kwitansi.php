<?php
require_once '../config/auth.php';
require_once '../config/database.php';

// Menentukan metode HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Mengambil input JSON jika ada
$input = json_decode(file_get_contents('php://input'), true);

// Header Response Default
header('Content-Type: application/json');

switch ($method) {
    case 'GET':
        // User Umum & Admin bisa akses GET
        requireApiRole(); 
        
        if (!isset($conn) || $conn === null) {
            echo json_encode(["status" => "success", "message" => "DB not connected, simulasi API GET berhasil", "data" => []]);
            exit;
        }

        try {
            $sql = "SELECT * FROM Kwit_manual_h ORDER BY date_created DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["status" => "success", "data" => $result]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
        break;

    case 'POST':
        // User Umum & Admin bisa akses POST
        requireApiRole(); 
        
        if (!isset($conn) || $conn === null) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Simulasi tambah data sukses (DB offline)", "data" => ["no_kwitansi" => "99999"]]);
            exit;
        }

        try {
            $conn->beginTransaction();

            // 1. Dapatkan nomor terakhir
            $stmt = $conn->query("SELECT No_Kwt, No_Fkt FROM master_nomor LIMIT 1");
            $nomor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $current_kwt = $nomor['No_Kwt'];
            $current_year = date('y'); // format 2 digit tahun, misal 26
            $kwt_year = substr($current_kwt, 0, 2);
            $kwt_seq = (int)substr($current_kwt, 2);

            if ($current_year === $kwt_year) {
                $next_seq = $kwt_seq + 1;
            } else {
                $next_seq = 1;
            }
            $next_kwt = $current_year . str_pad($next_seq, 3, '0', STR_PAD_LEFT);
            
            // Generate faktur default jika tidak disediakan
            $next_fkt = isset($input['no_faktur']) && !empty($input['no_faktur']) ? $input['no_faktur'] : 'FK' . $next_kwt;

            // 2. Insert Header
            $sqlH = "INSERT INTO Kwit_manual_h (no_kwitansi, no_faktur, terima_dari, Jumlah, keterangan1, keterangan2, user, tgl_kwitansi, jam, st) 
                    VALUES (:no_kwitansi, :no_faktur, :terima_dari, :jumlah, :ket1, :ket2, :user, :tgl, :jam, :st)";
            $stmtH = $conn->prepare($sqlH);
            
            $terima_dari = isset($input['terima_dari']) ? $input['terima_dari'] : '';
            $jumlah = isset($input['Jumlah']) ? $input['Jumlah'] : 0;
            $ket1 = isset($input['keterangan1']) ? $input['keterangan1'] : '';
            $ket2 = isset($input['keterangan2']) ? $input['keterangan2'] : '';
            $tgl = isset($input['tgl_kwitansi']) ? $input['tgl_kwitansi'] : date('Y-m-d');
            $jam = date('H:i:s');
            $user = $_SESSION['username'];
            $st = 'LNS';
            
            $stmtH->execute([
                ':no_kwitansi' => $next_kwt,
                ':no_faktur' => $next_fkt,
                ':terima_dari' => $terima_dari,
                ':jumlah' => $jumlah,
                ':ket1' => $ket1,
                ':ket2' => $ket2,
                ':user' => $user,
                ':tgl' => $tgl,
                ':jam' => $jam,
                ':st' => $st
            ]);

            // 3. Insert Details
            if (isset($input['details']) && is_array($input['details'])) {
                $sqlD = "INSERT INTO Kwit_manual_d (no_faktur, Kd_brg, nama, jumlah) VALUES (:no_faktur, :kd_brg, :nama, :jumlah)";
                $stmtD = $conn->prepare($sqlD);
                foreach ($input['details'] as $detail) {
                    $stmtD->execute([
                        ':no_faktur' => $next_fkt,
                        ':kd_brg' => $detail['Kd_brg'],
                        ':nama' => $detail['nama'],
                        ':jumlah' => isset($detail['jumlah']) ? $detail['jumlah'] : 0
                    ]);
                }
            }

            // 4. Update Nomor Master
            $stmtUpd = $conn->prepare("UPDATE master_nomor SET No_Kwt = :kwt, No_Fkt = :fkt");
            $stmtUpd->execute([':kwt' => $next_kwt, ':fkt' => $next_fkt]);

            $conn->commit();

            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Data berhasil ditambahkan", "data" => ["no_kwitansi" => $next_kwt]]);
        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
        break;

    case 'PATCH':
        // HANYA ADMIN yang bisa akses PATCH
        requireApiRole('Admin');

        if (!$input || !isset($input['no_kwitansi'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
            exit;
        }

        if (!isset($conn) || $conn === null) {
            echo json_encode(["status" => "success", "message" => "Simulasi update data sukses (DB offline)"]);
            exit;
        }

        try {
            $sql = "UPDATE Kwit_manual_h SET terima_dari = :terima_dari, Jumlah = :jumlah WHERE no_kwitansi = :no_kwitansi";
            $stmt = $conn->prepare($sql);
            
            $terima_dari = isset($input['terima_dari']) ? $input['terima_dari'] : '';
            $jumlah = isset($input['jumlah']) ? $input['jumlah'] : 0;
            
            $stmt->execute([
                ':no_kwitansi' => $input['no_kwitansi'],
                ':terima_dari' => $terima_dari,
                ':jumlah' => $jumlah
            ]);
            echo json_encode(["status" => "success", "message" => "Data berhasil diperbarui"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        // HANYA ADMIN yang bisa akses DELETE
        requireApiRole('Admin');

        if (!$input || !isset($input['no_kwitansi'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
            exit;
        }

        if (!isset($conn) || $conn === null) {
            echo json_encode(["status" => "success", "message" => "Simulasi hapus data sukses (DB offline)"]);
            exit;
        }

        try {
            $sql = "DELETE FROM Kwit_manual_h WHERE no_kwitansi = :no_kwitansi";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':no_kwitansi' => $input['no_kwitansi']]);
            echo json_encode(["status" => "success", "message" => "Data berhasil dihapus"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
        break;
}
?>
