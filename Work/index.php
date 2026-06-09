<?php
require_once 'config/auth.php';
requireLogin();
require_once 'config/database.php';
$page_title = "Dashboard Utama";

// We will fetch mock statistics here since the db might be empty or missing
// In a real app we'd do: $stmt = $conn->query("SELECT COUNT(*) FROM Kwit_manual_h");
$total_kwitansi = -0;
$total_pendapatan = "Rp 0";
$pengunjung_hari_ini = 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RKZ Surabaya - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'components/sidebar.php'; ?>

    <main class="main-content">
        <?php include 'components/topbar.php'; ?>
        
        <div class="content-area">
            
            <div class="stat-cards">
                <div class="card">
                    <div class="card-info">
                        <span class="card-title">Total Pendapatan</span>
                        <span class="card-value"><?= $total_pendapatan ?></span>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div class="card">
                    <div class="card-info">
                        <span class="card-title">Kwitansi Terbit</span>
                        <span class="card-value"><?= number_format($total_kwitansi) ?></span>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div class="card">
                    <div class="card-info">
                        <span class="card-title">Pengunjung Hari Ini</span>
                        <span class="card-value"><?= $pengunjung_hari_ini ?></span>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="table-container mt-4">
                <div class="table-header">
                    <h3>Kwitansi Terbaru</h3>
                    <a href="laporan.php" class="btn btn-primary btn-sm">Lihat Semua</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>No Kwitansi</th>
                            <th>Tanggal</th>
                            <th>Terima Dari</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Placeholder rows, normally fetched from db -->
                        <tr>
                            <td>KWT-26040</td>
                            <td>09 Jun 2026</td>
                            <td>RERE</td>
                            <td>Rp 1.741.000</td>
                            <td><span class="badge badge-success">Lunas</span></td>
                        </tr>
                        <tr>
                            <td>KWT-26039</td>
                            <td>08 Jun 2026</td>
                            <td>MITA</td>
                            <td>Rp 1.290.400</td>
                            <td><span class="badge badge-success">Lunas</span></td>
                        </tr>
                        <tr>
                            <td>KWT-26038</td>
                            <td>06 Jun 2026</td>
                            <td>MITA</td>
                            <td>Rp 1.285.200</td>
                            <td><span class="badge badge-success">Lunas</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

</body>
</html>
