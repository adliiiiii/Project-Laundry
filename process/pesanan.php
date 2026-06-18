<?php
require __DIR__ . '/../databases/config.php';

// TAMBAH PESANAN
if (isset($_POST['tambah_transaksi'])) {
    $id_customer = (int)$_POST['id_customer'];
    $id_layanan  = (int)$_POST['id_layanan']; // Ambil ID layanan dari database
    $berat       = (float)$_POST['berat'];
    $metode      = $_POST['metode_pengambilan'];
    
    if ($metode == 'Ambil di Tempat') {
        $id_kurir = "NULL";
    } else {
        $id_kurir = (int)$_POST['id_kurir'];
    }
    
    $biaya_layanan = ($metode == 'Kurir') ? 5000 : 0;
    
    // AMBIL HARGA DARI DATABASE
    $q = $conn->query("SELECT harga_kg, satuan FROM layanan WHERE id_layanan = $id_layanan");
    if ($q->num_rows == 0) {
        die("Layanan tidak ditemukan!");
    }
    $layanan_data = $q->fetch_assoc();
    $harga_kg = $layanan_data['harga_kg'];
    $satuan = $layanan_data['satuan'];
    
    // CEK APAKAH PER SATUAN
    $per_item = in_array($satuan, ['per potong', 'per pasang']);
    
    if ($per_item) {
        $berat = 1; // Force berat = 1 untuk item satuan
        $subtotal = $harga_kg;
    } else {
        $subtotal = $berat * $harga_kg;
    }
    
    $total = $subtotal + $biaya_layanan;
    
    // AMBIL KARYAWAN
    $row = $conn->query("SELECT id_karyawan FROM karyawan LIMIT 1")->fetch_assoc();
    $id_karyawan = $row['id_karyawan'];
    
    $status_kurir = ($metode == 'Kurir') ? 'Kurir Menuju Rumah' : 'Selesai Diterima';
    $tgl = date('Y-m-d');
    
    // INSERT TRANSAKSI (TANPA MENAMBAH LAYANAN)
    $sql = "INSERT INTO transaksi (
        id_customer,
        id_karyawan,
        id_kurir,
        id_layanan,
        tanggal_masuk,
        berat,
        total_harga,
        status_laundry,
        status_kurir,
        metode_pengambilan,
        biaya_layanan
    ) VALUES (
        $id_customer,
        $id_karyawan,
        $id_kurir,
        $id_layanan,
        '$tgl',
        $berat,
        $total,
        'Proses',
        '$status_kurir',
        '$metode',
        $biaya_layanan
    )";
    
    if (!$conn->query($sql)) {
        die("MYSQL ERROR: " . $conn->error);
    }
    header("Location: pesanan.php?msg=added");
    exit;
}

// UPDATE STATUS
if (isset($_GET['action']) && $_GET['action'] == 'update_laundry') {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    $conn->query("UPDATE transaksi SET status_laundry='$status' WHERE id_transaksi=$id");
    header("Location: pesanan.php");
    exit;
}

// HAPUS TRANSAKSI
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM transaksi WHERE id_transaksi=$id");
    header("Location: pesanan.php");
    exit;
}

// AMBIL DATA TRANSAKSI
$transaksi = $conn->query("SELECT t.*, c.nama_customer, k.nama_karyawan, kr.nama_kurir, l.nama_layanan, l.satuan
    FROM transaksi t 
    LEFT JOIN customer c ON t.id_customer=c.id_customer 
    LEFT JOIN karyawan k ON t.id_karyawan=k.id_karyawan 
    LEFT JOIN kurir kr ON t.id_kurir=kr.id_kurir 
    LEFT JOIN layanan l ON t.id_layanan=l.id_layanan 
    ORDER BY t.tanggal_masuk DESC");

$pageTitle  = 'Pesanan';
$activePage = 'pesanan';
$basePath   = '../';
require __DIR__ . '/../includes/header.php';
?>

<!-- DAFTAR PESANAN -->
<div class="panel">
    <div class="panel-header">
        <h2>📋 Daftar Pesanan</h2>
        <button class="btn btn-primary" onclick="bukaModal('modalTransaksi')">+ Tambah Pesanan</button>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>Kurir</th>
                    <th>Metode</th>
                    <th>Berat</th>
                    <th>Layanan</th>
                    <th>Harga</th>
                    <th>Biaya Kurir</th>
                    <th>Total</th>
                    <th>Tgl Masuk</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($transaksi->num_rows > 0): while($t=$transaksi->fetch_assoc()):
                $status = $t['status_laundry'];
                $badge = 'status-antrean';
                
                if (in_array($status, ['Sedang Dicuci', 'Sedang Dijemur', 'Sedang Disetrika'])) {
                    $badge = 'status-proses';
                } elseif ($status == 'Selesai Di-packing') {
                    $badge = 'status-selesai';
                }
            ?>
                <tr>
                    <td><strong>LDR-<?= $t['id_transaksi'] ?></strong></td>
                    <td><?= $t['nama_customer'] ?? '-' ?></td>
                    <td><?= $t['nama_kurir'] ?? '-' ?></td>
                    <td><?= $t['metode_pengambilan'] ?></td>
                    <td><?= number_format($t['berat'], 2) ?> Kg</td>
                    <td><?= $t['nama_layanan'] ?></td>
                    <td>Rp <?= number_format($t['total_harga'] - $t['biaya_layanan'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($t['biaya_layanan'], 0, ',', '.') ?></td>
                    <td><strong>Rp <?= number_format($t['total_harga'], 0, ',', '.') ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($t['tanggal_masuk'])) ?></td>   
                    <td><span class="status-badge <?= $badge ?>"><?= $status ?></span></td>
                    <td style="white-space:nowrap;">
                        <?php if ($status == 'Proses'): ?>
                            <a href="?action=update_laundry&id=<?= $t['id_transaksi'] ?>&status=Selesai"
                               class="btn btn-primary btn-sm">
                               Selesai
                            </a>
                        <?php else: ?>
                            <span class="btn btn-success btn-sm">✓ Selesai</span>
                        <?php endif; ?>
                        
                        <a href="?action=delete&id=<?= $t['id_transaksi'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Hapus transaksi ini?')">
                           Hapus
                        </a>
                        
                        <a href="cetak_nota.php?id=<?= $t['id_transaksi'] ?>"
                           target="_blank"
                           class="btn btn-info btn-sm">
                           Cetak
                        </a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="12" class="empty-msg">Belum ada pesanan.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAMBAH PESANAN -->
<div class="modal-overlay" id="modalTransaksi">
    <div class="modal-box">
        <h2>➕ Tambah Pesanan</h2>
        <form method="POST">
            <label>Customer</label>
            <select name="id_customer" required>
                <option value="">-- Pilih Customer --</option>
                <?php 
                $cust = $conn->query("SELECT * FROM customer ORDER BY nama_customer"); 
                while($c=$cust->fetch_assoc()): 
                ?>
                    <option value="<?= $c['id_customer'] ?>">
                        <?= htmlspecialchars($c['nama_customer']) ?> 
                        (<?= $c['no_telp'] ?? '-' ?>)
                    </option>
                <?php endwhile; ?>
            </select>
            
            <label>Layanan</label>
            <select name="id_layanan" required>
                <option value="">-- Pilih Layanan --</option>
                <?php 
                $layanan_list = $conn->query("SELECT * FROM layanan ORDER BY paket, nama_layanan");
                $current_paket = '';
                while($l = $layanan_list->fetch_assoc()):
                    if ($current_paket != $l['paket']) {
                        if ($current_paket != '') echo '</optgroup>';
                        echo '<optgroup label="' . htmlspecialchars($l['paket']) . '">';
                        $current_paket = $l['paket'];
                    }
                ?>
                    <option value="<?= $l['id_layanan'] ?>">
                        <?= htmlspecialchars($l['nama_layanan']) ?> 
                        — Rp <?= number_format($l['harga_kg'], 0, ',', '.') ?> 
                        (<?= $l['satuan'] ?>)
                    </option>
                <?php endwhile; ?>
                </optgroup>
            </select>
            
            <label>Berat (Kg) <small style="font-weight:400; color:#4a6b8a;">(abaikan untuk paket satuan)</small></label>
            <input type="number" name="berat" step="0.5" min="0" placeholder="Contoh: 2.5" value="1">
            
            <label>Metode Pengambilan</label>
            <select name="metode_pengambilan" id="metode_pengambilan" onchange="toggleKurir()" required>
                <option value="Kurir">Pakai Kurir (+ Rp 5.000)</option>
                <option value="Ambil di Tempat">Ambil di Tempat (Gratis)</option>
            </select>
            
            <div id="kurirField">
                <label>Kurir</label>
                <select name="id_kurir" id="id_kurir">
                    <option value="">-- Pilih Kurir --</option>
                    <?php
                    $kur = $conn->query("SELECT * FROM kurir ORDER BY nama_kurir");
                    while($k = $kur->fetch_assoc()):
                    ?>
                        <option value="<?= $k['id_kurir'] ?>">
                            <?= htmlspecialchars($k['nama_kurir']) ?> 
                            (<?= $k['no_hp'] ?? '-' ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-danger" onclick="tutupModal('modalTransaksi')">Batal</button>
                <button type="submit" name="tambah_transaksi" class="btn btn-success">Simpan Pesanan</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleKurir() {
    const metode = document.getElementById('metode_pengambilan').value;
    const kurirField = document.getElementById('kurirField');
    const kurirSelect = document.getElementById('id_kurir');
    
    if (metode === 'Ambil di Tempat') {
        kurirField.style.display = 'none';
        kurirSelect.value = '';
    } else {
        kurirField.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', toggleKurir);
</script>

<?php
require __DIR__ . '/../includes/footer.php';
if(isset($_GET['msg']) && $_GET['msg']=='added') echo "<script>alert('✅ Pesanan berhasil ditambahkan!');</script>";
$conn->close();
?>