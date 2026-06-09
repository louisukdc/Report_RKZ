<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="brand">
        <i class="fas fa-hospital"></i> RKZ Surabaya
    </div>
    <nav>
        <a href="index.php" class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="laporan.php" class="nav-link <?= $current_page == 'laporan.php' || $current_page == 'detail_kwitansi.php' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice"></i> Laporan Kwitansi
        </a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
        <a href="tambah_kwitansi.php" class="nav-link <?= $current_page == 'tambah_kwitansi.php' ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i> Tambah Kwitansi
        </a>
        <a href="master_barang.php" class="nav-link <?= $current_page == 'master_barang.php' ? 'active' : '' ?>">
            <i class="fas fa-box"></i> Master Barang
        </a>
        <?php endif; ?>
    </nav>
</aside>
