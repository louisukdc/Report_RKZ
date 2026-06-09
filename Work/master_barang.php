<?php
require_once 'config/auth.php';
requireLogin();

// Hanya Admin yang boleh akses halaman Master Barang
if ($_SESSION['role'] !== 'Admin') {
    die("<div style='padding: 2rem; text-align: center; font-family: sans-serif; color: #991b1b;'>
        <h3>Akses Ditolak</h3><p>Anda tidak memiliki izin untuk membuka halaman ini.</p>
        <a href='index.php'>Kembali ke Dashboard</a>
    </div>");
}

$page_title = "Master Layanan / Barang";

require_once 'config/database.php';
$stmt = $conn->query("SELECT * FROM master ORDER BY Nama ASC");
$master_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - RKZ Surabaya</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <main class="main-content">
        <?php include 'components/topbar.php'; ?>

        <div class="content-area">
            <h2 style="margin-bottom: 1.5rem; color: var(--text-main); display: flex; justify-content: space-between; align-items: center;">
                Master Layanan & Barang
                <button class="btn btn-primary" onclick="tambahMaster()">
                    <i class="fas fa-plus"></i> Tambah Baru
                </button>
            </h2>

            <div class="card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Layanan/Barang</th>
                                <th>Harga (Rp)</th>
                                <th width="200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($master_data) === 0): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted);">Belum ada data master.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($master_data as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['kode']) ?></td>
                                    <td><strong><?= htmlspecialchars($row['Nama']) ?></strong></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <button class="btn btn-sm" style="background-color: #fef08a; color: #854d0e; border: 1px solid #fde047;" onclick="openEditModal(<?= $row['kode'] ?>, '<?= htmlspecialchars(addslashes($row['Nama'])) ?>', <?= floatval($row['harga']) ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm" style="background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; margin-left: 0.25rem;" onclick="hapusMaster(<?= $row['kode'] ?>)">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Form (Bisa untuk Tambah dan Edit) -->
    <div id="masterModal" class="modal-backdrop" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
        <div class="card" style="width: 100%; max-width: 400px; margin: 2rem;">
            <h3 id="modalTitle" style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Tambah Layanan Baru</h3>
            <form id="masterForm" onsubmit="submitMaster(event)">
                <input type="hidden" id="form_kode">
                <input type="hidden" id="form_mode" value="add">
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:bold;">Nama Layanan/Barang</label>
                    <input type="text" id="form_nama" style="width:100%; padding:0.5rem; border:1px solid var(--border-color); border-radius:4px;" required>
                </div>
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:bold;">Harga (Rp)</label>
                    <input type="number" id="form_harga" style="width:100%; padding:0.5rem; border:1px solid var(--border-color); border-radius:4px;" required>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                    <button type="button" class="btn" style="background:#f1f5f9; color:var(--text-main);" onclick="closeMasterModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

<script>
    function tambahMaster() {
        document.getElementById('modalTitle').innerText = 'Tambah Layanan Baru';
        document.getElementById('form_mode').value = 'add';
        document.getElementById('form_kode').value = '';
        document.getElementById('form_nama').value = '';
        document.getElementById('form_harga').value = '';
        document.getElementById('masterModal').style.display = 'flex';
    }

    function openEditModal(kode, nama, harga) {
        document.getElementById('modalTitle').innerText = 'Edit Layanan';
        document.getElementById('form_mode').value = 'edit';
        document.getElementById('form_kode').value = kode;
        document.getElementById('form_nama').value = nama;
        document.getElementById('form_harga').value = harga;
        document.getElementById('masterModal').style.display = 'flex';
    }

    function closeMasterModal() {
        document.getElementById('masterModal').style.display = 'none';
    }

    async function submitMaster(e) {
        e.preventDefault();
        const mode = document.getElementById('form_mode').value;
        const kode = document.getElementById('form_kode').value;
        const nama = document.getElementById('form_nama').value;
        const harga = document.getElementById('form_harga').value;

        const method = mode === 'add' ? 'POST' : 'PATCH';
        const payload = mode === 'add' ? { Nama: nama, harga: parseFloat(harga) || 0 } : { kode: kode, Nama: nama, harga: parseFloat(harga) || 0 };

        try {
            const response = await fetch('api/master.php', {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            
            if (response.ok && result.status === 'success') {
                alert(result.message);
                window.location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (e) {
            alert('Terjadi kesalahan koneksi.');
        }
    }

async function hapusMaster(kode) {
    if (!confirm('Apakah Anda yakin ingin menghapus layanan ini?')) return;

    try {
        const response = await fetch('api/master.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ kode: kode })
        });
        const result = await response.json();
        
        if (response.ok && result.status === 'success') {
            alert(result.message);
            window.location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (e) {
        alert('Terjadi kesalahan koneksi.');
    }
}
</script>
</body>
</html>
