<?php
require_once 'config/auth.php';
requireLogin();

$page_title = "Laporan Kwitansi";

require_once 'config/database.php';

if (!isset($conn) || $conn === null) {
    $error_msg = isset($db_connection_error) ? htmlspecialchars($db_connection_error) : 'Unknown Error';
    die("<div style='padding: 2rem; text-align: center; font-family: sans-serif; color: #991b1b; background: #fee2e2; border-bottom: 2px solid #fecaca;'>
        <h3>Koneksi MySQL Terputus</h3>
        <p><b>Detail Error dari Server:</b> {$error_msg}</p>
        <p>Silakan periksa konfigurasi password atau nyalakan MySQL di XAMPP/CMD.</p>
    </div>");
}

// Simple filtering logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : '';
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '';

$where_clauses = [];
$params = [];

if ($search !== '') {
    $where_clauses[] = "(no_kwitansi LIKE :search OR terima_dari LIKE :search OR no_faktur LIKE :search)";
    $params[':search'] = "%{$search}%";
}
if ($tgl_mulai !== '') {
    $where_clauses[] = "tgl_kwitansi >= :tgl_mulai";
    $params[':tgl_mulai'] = $tgl_mulai;
}
if ($tgl_akhir !== '') {
    $where_clauses[] = "tgl_kwitansi <= :tgl_akhir";
    $params[':tgl_akhir'] = $tgl_akhir;
}

$sql = "SELECT * FROM Kwit_manual_h";
if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " ORDER BY date_created DESC, no_kwitansi DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$filtered_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        .filter-card {
            background-color: var(--card-bg);
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .filter-input {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-color);
            font-family: var(--font-family);
            min-width: 200px;
        }
        .filter-input:focus {
            outline: none;
            border-color: var(--primary);
        }
    </style>
</head>
<body>

    <?php include 'components/sidebar.php'; ?>

    <main class="main-content">
        <?php include 'components/topbar.php'; ?>
        
        <div class="content-area">
            
            <form method="GET" action="laporan.php" class="filter-card">
                <div class="filter-group">
                    <label style="font-weight: 500; font-size: 0.875rem;">Pencarian (No. Kwt / Nama)</label>
                    <input type="text" name="search" class="filter-input" placeholder="Cari..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="filter-group">
                    <label style="font-weight: 500; font-size: 0.875rem;">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" class="filter-input" value="<?= htmlspecialchars($tgl_mulai) ?>">
                </div>
                <div class="filter-group">
                    <label style="font-weight: 500; font-size: 0.875rem;">Sampai Tanggal</label>
                    <input type="date" name="tgl_akhir" class="filter-input" value="<?= htmlspecialchars($tgl_akhir) ?>">
                </div>
                <button type="submit" class="btn btn-primary" style="height: 38px;">
                    <i class="fas fa-search"></i> Terapkan
                </button>
                <a href="laporan.php" class="btn" style="height: 38px; border: 1px solid var(--border-color); background: #fff; color: var(--text-main);">Reset</a>
            </form>

            <div class="table-container">
                <div class="table-header">
                    <h3>Data Kwitansi Kunjungan</h3>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-print"></i> Cetak Laporan</button>
                </div>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>No Kwitansi</th>
                                <th>Tgl Kwitansi</th>
                                <th>Jam</th>
                                <th>No Faktur</th>
                                <th>Terima Dari</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Kasir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($filtered_data) > 0): ?>
                                <?php foreach ($filtered_data as $row): ?>
                                <tr>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($row['no_kwitansi']) ?></td>
                                    <td><?= htmlspecialchars(date('d-m-Y', strtotime($row['tgl_kwitansi']))) ?></td>
                                    <td><?= htmlspecialchars($row['jam']) ?></td>
                                    <td><?= htmlspecialchars($row['no_faktur']) ?></td>
                                    <td><?= htmlspecialchars($row['terima_dari']) ?></td>
                                    <td style="font-weight: 600; color: var(--primary);">Rp <?= number_format($row['Jumlah'], 0, ',', '.') ?></td>
                                    <td><span class="badge badge-success"><?= htmlspecialchars($row['st']) ?></span></td>
                                    <td><?= htmlspecialchars($row['user']) ?></td>
                                    <td>
                                        <a href="detail_kwitansi.php?id=<?= urlencode($row['no_kwitansi']) ?>" class="btn btn-sm" style="background-color: #f1f5f9; color: var(--text-main); border: 1px solid var(--border-color); text-decoration: none;">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                                            <button class="btn btn-sm" style="background-color: #fef08a; color: #854d0e; border: 1px solid #fde047; margin-left: 0.25rem;" onclick="editKwitansi('<?= htmlspecialchars($row['no_kwitansi']) ?>', '<?= htmlspecialchars(addslashes($row['terima_dari'])) ?>', <?= $row['Jumlah'] ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm" style="background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; margin-left: 0.25rem;" onclick="deleteKwitansi('<?= htmlspecialchars($row['no_kwitansi']) ?>')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                        Data tidak ditemukan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- Edit Modal -->
    <div id="editModal" class="modal-backdrop" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
        <div class="card" style="width: 100%; max-width: 400px; margin: 2rem;">
            <h3 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Edit Kwitansi</h3>
            <form id="editForm" onsubmit="submitEdit(event)">
                <input type="hidden" id="edit_no_kwitansi">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:bold;">Terima Dari</label>
                    <input type="text" id="edit_terima_dari" class="filter-input" style="width:100%;" required>
                </div>
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:bold;">Total (Rp)</label>
                    <input type="number" id="edit_jumlah" class="filter-input" style="width:100%;" required>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                    <button type="button" class="btn" style="background:#f1f5f9; color:var(--text-main);" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

<script>
async function deleteKwitansi(no_kwitansi) {
    if (!confirm('Apakah Anda yakin ingin menghapus data kwitansi ' + no_kwitansi + '?')) return;

    try {
        const response = await fetch('api/kwitansi.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ no_kwitansi: no_kwitansi })
        });
        const result = await response.json();
        
        if (response.ok) {
            alert(result.message);
            window.location.reload(); // Refresh table
        } else {
            alert('Error: ' + result.message);
        }
    } catch (e) {
        alert('Terjadi kesalahan koneksi.');
    }
}

function editKwitansi(no_kwitansi, current_terima_dari, current_jumlah) {
    document.getElementById('edit_no_kwitansi').value = no_kwitansi;
    document.getElementById('edit_terima_dari').value = current_terima_dari;
    document.getElementById('edit_jumlah').value = current_jumlah;
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

async function submitEdit(e) {
    e.preventDefault();
    const no_kwitansi = document.getElementById('edit_no_kwitansi').value;
    const terima_dari = document.getElementById('edit_terima_dari').value;
    const jumlah = document.getElementById('edit_jumlah').value;

    try {
        const response = await fetch('api/kwitansi.php', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                no_kwitansi: no_kwitansi,
                terima_dari: terima_dari,
                jumlah: parseFloat(jumlah) || 0
            })
        });
        const result = await response.json();
        
        if (response.ok && result.status === 'success') {
            alert(result.message);
            window.location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (e) {
        alert('Terjadi kesalahan koneksi saat mengedit kwitansi.');
    }
}
</script>
</body>
</html>
