<?php
require_once 'config/auth.php';
requireLogin();

require_once 'config/database.php';

$no_kwitansi = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($no_kwitansi)) {
    die("Nomor Kwitansi tidak valid.");
}

// Ambil Header
$stmtH = $conn->prepare("SELECT * FROM Kwit_manual_h WHERE no_kwitansi = :id");
$stmtH->execute([':id' => $no_kwitansi]);
$header = $stmtH->fetch(PDO::FETCH_ASSOC);

if (!$header) {
    die("Data Kwitansi tidak ditemukan.");
}

// Ambil Detail
$stmtD = $conn->prepare("SELECT * FROM Kwit_manual_d WHERE no_faktur = :faktur");
$stmtD->execute([':faktur' => $header['no_faktur']]);
$details = $stmtD->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kwitansi #<?= htmlspecialchars($header['no_kwitansi']) ?> - RKZ Surabaya</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #2f855a; /* RKZ Green */
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-logo {
            font-size: 24px;
            font-weight: bold;
            color: #2f855a;
        }
        .header-title {
            text-align: right;
        }
        .header-title h2 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-bottom: 20px;
        }
        .info-grid table {
            width: 100%;
        }
        .info-grid td {
            padding: 4px 0;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f1f5f9;
        }
        .items-table .text-right {
            text-align: right;
        }
        .total-row th, .total-row td {
            border-top: 2px solid #000;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            text-align: center;
            width: 250px;
        }
        .signature p {
            margin-bottom: 60px;
        }
        
        /* Print Specific Styles */
        @media print {
            body { padding: 0; }
            .invoice-container { border: none; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2f855a; color: white; border: none; border-radius: 4px; cursor: pointer;">Cetak Dokumen</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #ccc; color: black; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">Tutup</button>
    </div>

    <div class="invoice-container">
        <div class="header">
            <div class="header-logo">
                RS RKZ SURABAYA
                <div style="font-size: 12px; font-weight: normal; color: #555; margin-top: 5px;">
                    Jl. Diponegoro No.51, Darmo, Surabaya<br>
                    Telp: (031) 5677562
                </div>
            </div>
            <div class="header-title">
                <h2>BUKTI PEMBAYARAN</h2>
                <p style="margin: 5px 0 0 0;">No. Kwitansi: <strong><?= htmlspecialchars($header['no_kwitansi']) ?></strong></p>
                <p style="margin: 0;">No. Faktur: <strong><?= htmlspecialchars($header['no_faktur']) ?></strong></p>
            </div>
        </div>

        <div class="info-grid">
            <div>
                <table>
                    <tr>
                        <td width="100"><strong>Terima Dari</strong></td>
                        <td width="10">:</td>
                        <td><?= htmlspecialchars($header['terima_dari']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Keterangan 1</strong></td>
                        <td>:</td>
                        <td><?= htmlspecialchars($header['keterangan1']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Keterangan 2</strong></td>
                        <td>:</td>
                        <td><?= htmlspecialchars($header['keterangan2']) ?></td>
                    </tr>
                </table>
            </div>
            <div>
                <table>
                    <tr>
                        <td width="100"><strong>Tanggal</strong></td>
                        <td width="10">:</td>
                        <td><?= date('d M Y', strtotime($header['tgl_kwitansi'])) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Jam</strong></td>
                        <td>:</td>
                        <td><?= htmlspecialchars($header['jam']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Kasir</strong></td>
                        <td>:</td>
                        <td><?= htmlspecialchars($header['user']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Kode</th>
                    <th>Rincian Layanan / Tindakan</th>
                    <th width="150" class="text-right">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($details) === 0): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Tidak ada detail layanan.</td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($details as $row): ?>
                    <tr>
                        <td style="text-align: center;"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['Kd_brg']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td class="text-right"><?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-right"><strong>TOTAL BAYAR (Rp)</strong></td>
                    <td class="text-right"><strong><?= number_format($header['Jumlah'], 0, ',', '.') ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <div style="font-size: 12px; color: #555;">
                <p>Catatan:<br>Bukti pembayaran ini sah dan diterbitkan oleh sistem komputer.<br>Harap simpan dengan baik.</p>
            </div>
            <div class="signature">
                <p>Surabaya, <?= date('d M Y') ?></p>
                <br>
                <p><strong>( <?= htmlspecialchars($header['user']) ?> )</strong><br>Kasir/Petugas</p>
            </div>
        </div>
    </div>
</body>
</html>
