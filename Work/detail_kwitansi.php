<?php
require_once 'config/auth.php';
requireLogin();

$page_title = "Detail Kwitansi";

require_once 'config/database.php';

$no_kwitansi = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($no_kwitansi)) {
    header("Location: laporan.php");
    exit;
}

// Ambil Header
$stmtH = $conn->prepare("SELECT * FROM Kwit_manual_h WHERE no_kwitansi = :id");
$stmtH->execute([':id' => $no_kwitansi]);
$header = $stmtH->fetch(PDO::FETCH_ASSOC);

if (!$header) {
    die("<div style='padding:2rem;text-align:center;'><h3>Kwitansi tidak ditemukan!</h3><a href='laporan.php'>Kembali</a></div>");
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
    <title><?= $page_title ?> - RKZ Surabaya</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .detail-box { background: var(--bg-color); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 2rem; }
        .detail-box p { margin-bottom: 0.5rem; color: var(--text-main); }
        .detail-box strong { display: inline-block; width: 150px; }
    </style>
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <main class="main-content">
        <?php include 'components/topbar.php'; ?>

        <div class="content-area">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="color: var(--text-main);"><i class="fas fa-receipt"></i> Detail Kwitansi #<?= htmlspecialchars($header['no_kwitansi']) ?></h2>
                <div>
                    <a href="laporan.php" class="btn" style="background-color: #f1f5f9; color: var(--text-main);"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <a href="cetak_invoice.php?id=<?= urlencode($header['no_kwitansi']) ?>" target="_blank" class="btn btn-primary" style="margin-left: 0.5rem;"><i class="fas fa-print"></i> Cetak Invoice</a>
                </div>
            </div>

            <div class="card">
                <div class="detail-box">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <p><strong>No. Kwitansi</strong>: <?= htmlspecialchars($header['no_kwitansi']) ?></p>
                            <p><strong>No. Faktur</strong>: <?= htmlspecialchars($header['no_faktur']) ?></p>
                            <p><strong>Terima Dari</strong>: <?= htmlspecialchars($header['terima_dari']) ?></p>
                        </div>
                        <div>
                            <p><strong>Tanggal</strong>: <?= date('d M Y', strtotime($header['tgl_kwitansi'])) ?> <?= htmlspecialchars($header['jam']) ?></p>
                            <p><strong>Kasir (User)</strong>: <?= htmlspecialchars($header['user']) ?></p>
                            <p><strong>Status</strong>: <span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 12px; font-weight: bold; font-size: 0.85rem;"><?= htmlspecialchars($header['st']) ?></span></p>
                        </div>
                    </div>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--border-color);">
                        <p><strong>Keterangan 1</strong>: <?= htmlspecialchars($header['keterangan1']) ?></p>
                        <p><strong>Keterangan 2</strong>: <?= htmlspecialchars($header['keterangan2']) ?></p>
                    </div>
                </div>

                <h3 style="margin-bottom: 1rem;">Rincian Layanan</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Barang/Layanan</th>
                                <th>Nama Layanan</th>
                                <th style="text-align: right;">Subtotal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($details) === 0): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">Tidak ada detail layanan untuk kwitansi ini.</td>
                            </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($details as $row): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['Kd_brg']) ?></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td style="text-align: right;"><?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" style="text-align: right; font-size: 1.1rem;">TOTAL KESELURUHAN</th>
                                <th style="text-align: right; font-size: 1.2rem; color: var(--primary-color);">Rp <?= number_format($header['Jumlah'], 0, ',', '.') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
