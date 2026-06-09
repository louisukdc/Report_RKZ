<?php
require_once 'config/auth.php';
requireLogin();

// Hanya Admin yang boleh akses halaman Tambah Kwitansi
if ($_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

$page_title = "Tambah Kwitansi Baru";

require_once 'config/database.php';

// Ambil data master untuk pilihan dropdown
$stmt = $conn->query("SELECT * FROM master ORDER BY Nama ASC");
$master_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; color: var(--text-main); }
        .form-control { width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-family: inherit; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .detail-row { display: grid; grid-template-columns: 3fr 2fr 1fr; gap: 1rem; margin-bottom: 1rem; align-items: end; }
    </style>
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <main class="main-content">
        <?php include 'components/topbar.php'; ?>

        <div class="content-area">
            <h2 style="margin-bottom: 1.5rem; color: var(--text-main);">Form Tambah Kwitansi Baru</h2>

            <div class="card">
                <form id="formKwitansi" onsubmit="submitKwitansi(event)">
                    <!-- Header Kwitansi -->
                    <h3 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Informasi Umum</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Terima Dari (Nama Pasien/Pembayar)</label>
                            <input type="text" id="terima_dari" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Kwitansi</label>
                            <input type="date" id="tgl_kwitansi" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label>No. Faktur (Opsional)</label>
                            <input type="text" id="no_faktur" class="form-control" placeholder="Kosongkan untuk generate otomatis">
                        </div>
                        <div class="form-group">
                            <label>Keterangan 1</label>
                            <input type="text" id="keterangan1" class="form-control" placeholder="Contoh: PENGOBATAN DI RKZ SURABAYA" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Keterangan 2 (Opsional)</label>
                        <input type="text" id="keterangan2" class="form-control">
                    </div>

                    <!-- Detail Layanan -->
                    <h3 style="margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; justify-content: space-between;">
                        Rincian Layanan / Tindakan
                        <button type="button" class="btn btn-sm btn-primary" onclick="addDetailRow()">+ Tambah Baris</button>
                    </h3>

                    <div id="detailContainer">
                        <!-- Baris detail akan ditambahkan ke sini oleh JS -->
                    </div>

                    <div style="text-align: right; margin-top: 1rem; font-size: 1.25rem;">
                        <strong>Total Pembayaran: Rp <span id="totalLabel">0</span></strong>
                    </div>

                    <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color); text-align: right;">
                        <a href="laporan.php" class="btn" style="background-color: #f1f5f9; color: var(--text-main);">Batal</a>
                        <button type="submit" class="btn btn-primary" style="margin-left: 0.5rem;"><i class="fas fa-save"></i> Simpan Kwitansi</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

<script>
    // Data master dari PHP ke Javascript
    const masterItems = <?= json_encode($master_items) ?>;
    
    // Inisialisasi baris pertama saat halaman dimuat
    document.addEventListener("DOMContentLoaded", () => {
        addDetailRow();
    });

    function addDetailRow() {
        const container = document.getElementById('detailContainer');
        const rowId = Date.now();
        
        let optionsHtml = '<option value="">-- Pilih Layanan --</option>';
        masterItems.forEach(item => {
            optionsHtml += `<option value="${item.kode}" data-nama="${item.Nama}" data-harga="${item.harga}">${item.kode} - ${item.Nama} (Rp ${parseFloat(item.harga).toLocaleString('id-ID')})</option>`;
        });

        const rowHtml = `
            <div class="detail-row" id="row-${rowId}">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Pilih Layanan</label>
                    <select class="form-control item-select" required onchange="updateRowPrice(${rowId}, this)">
                        ${optionsHtml}
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Subtotal (Rp)</label>
                    <input type="number" class="form-control item-price" required oninput="calculateTotal()">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <button type="button" class="btn" style="background-color: #fee2e2; color: #991b1b; width:100%; border: 1px solid #fecaca;" onclick="removeRow(${rowId})"><i class="fas fa-trash"></i> Hapus</button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', rowHtml);
    }

    function updateRowPrice(rowId, selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const priceInput = document.querySelector(`#row-${rowId} .item-price`);
        
        if (selectedOption.value !== "") {
            priceInput.value = selectedOption.getAttribute('data-harga');
        } else {
            priceInput.value = "";
        }
        calculateTotal();
    }

    function removeRow(rowId) {
        const row = document.getElementById(`row-${rowId}`);
        if (row) {
            row.remove();
            calculateTotal();
        }
    }

    function calculateTotal() {
        const priceInputs = document.querySelectorAll('.item-price');
        let total = 0;
        priceInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('totalLabel').innerText = total.toLocaleString('id-ID');
        return total;
    }

    async function submitKwitansi(e) {
        e.preventDefault();
        
        const terima_dari = document.getElementById('terima_dari').value;
        const tgl_kwitansi = document.getElementById('tgl_kwitansi').value;
        const no_faktur = document.getElementById('no_faktur').value;
        const keterangan1 = document.getElementById('keterangan1').value;
        const keterangan2 = document.getElementById('keterangan2').value;
        const total = calculateTotal();

        if (total <= 0) {
            alert("Kwitansi harus memiliki setidaknya satu layanan dengan nominal lebih dari 0.");
            return;
        }

        // Kumpulkan detail
        const details = [];
        const rows = document.querySelectorAll('.detail-row');
        rows.forEach(row => {
            const select = row.querySelector('.item-select');
            const price = row.querySelector('.item-price').value;
            
            if (select.value !== "") {
                const opt = select.options[select.selectedIndex];
                details.push({
                    Kd_brg: select.value,
                    nama: opt.getAttribute('data-nama'),
                    jumlah: parseFloat(price)
                });
            }
        });

        if (details.length === 0) {
            alert("Silakan pilih minimal 1 layanan.");
            return;
        }

        const payload = {
            terima_dari: terima_dari,
            tgl_kwitansi: tgl_kwitansi,
            no_faktur: no_faktur,
            keterangan1: keterangan1,
            keterangan2: keterangan2,
            Jumlah: total,
            details: details
        };

        try {
            const response = await fetch('api/kwitansi.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            
            if (response.ok && result.status === 'success') {
                alert("Kwitansi Berhasil Dibuat! Nomor Kwitansi Anda: " + result.data.no_kwitansi);
                window.location.href = 'laporan.php';
            } else {
                alert('Error: ' + result.message);
            }
        } catch (e) {
            alert('Terjadi kesalahan koneksi saat menyimpan kwitansi.');
        }
    }
</script>
</body>
</html>
