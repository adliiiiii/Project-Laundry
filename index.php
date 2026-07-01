<?php
require __DIR__ . '/databases/config.php';

// ===== STATISTIK =====
$transaksi = $conn->query("SELECT t.*, c.nama_customer, l.nama_layanan 
    FROM transaksi t 
    LEFT JOIN customer c ON t.id_customer=c.id_customer 
    LEFT JOIN layanan l ON t.id_layanan=l.id_layanan 
    ORDER BY t.tanggal_masuk DESC");

$total_pesanan = $transaksi->num_rows;
$pendapatan = 0;
$proses = 0;
$selesai = 0;
while ($row = $transaksi->fetch_assoc()) {
    $pendapatan += $row['total_harga'];
    if (in_array($row['status_laundry'], ['Selesai', 'Selesai Di-packing'])) $selesai++;
    else $proses++;
}
$transaksi->data_seek(0);
$total_customer = $conn->query("SELECT COUNT(*) AS c FROM customer")->fetch_assoc()['c'];
$total_kurir = $conn->query("SELECT COUNT(*) AS c FROM kurir")->fetch_assoc()['c'];
$total_layanan = $conn->query("SELECT COUNT(*) AS c FROM layanan")->fetch_assoc()['c'];

// ===== DATA CUSTOMER =====
$customers = $conn->query("SELECT c.*, COUNT(t.id_transaksi) AS total_order 
    FROM customer c 
    LEFT JOIN transaksi t ON c.id_customer=t.id_customer 
    GROUP BY c.id_customer 
    ORDER BY c.id_customer");

// ===== DATA KURIR =====
$kurirs = $conn->query("SELECT kr.*, COUNT(t.id_transaksi) AS total_antar 
    FROM kurir kr 
    LEFT JOIN transaksi t ON kr.id_kurir=t.id_kurir 
    GROUP BY kr.id_kurir 
    ORDER BY kr.id_kurir");

// ===== DATA LAYANAN =====
$layanan_data = $conn->query("SELECT * FROM layanan ORDER BY paket, nama_layanan");

// ===== PROSES FORM =====
// Tambah Customer
if (isset($_POST['tambah_customer'])) {
    $nama = $conn->real_escape_string($_POST['nama_customer']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $telp = $conn->real_escape_string($_POST['no_telp']);
    $conn->query("INSERT INTO customer (nama_customer, alamat, no_telp) VALUES ('$nama','$alamat','$telp')");
    header("Location: index.php#customer-section");
    exit;
}

// Hapus Customer
if (isset($_GET['action']) && $_GET['action'] == 'delete_customer') {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM transaksi WHERE id_customer=$id");
    $conn->query("DELETE FROM customer WHERE id_customer=$id");
    header("Location: index.php#customer-section");
    exit;
}

// Tambah Kurir
if (isset($_POST['tambah_kurir'])) {
    $nama = $conn->real_escape_string($_POST['nama_kurir']);
    $hp = $conn->real_escape_string($_POST['no_hp']);
    $conn->query("INSERT INTO kurir (nama_kurir, no_hp) VALUES ('$nama','$hp')");
    header("Location: index.php#kurir-section");
    exit;
}

// Hapus Kurir
if (isset($_GET['action']) && $_GET['action'] == 'delete_kurir') {
    $id = (int)$_GET['id'];
    $check = $conn->query("SELECT COUNT(*) as total FROM transaksi WHERE id_kurir = $id");
    $row = $check->fetch_assoc();
    if ($row['total'] == 0) {
        $conn->query("DELETE FROM kurir WHERE id_kurir=$id");
    }
    header("Location: index.php#kurir-section");
    exit;
}

// Edit Kurir
if (isset($_POST['edit_kurir'])) {
    $id = (int)$_POST['id_kurir'];
    $nama = $conn->real_escape_string($_POST['nama_kurir']);
    $hp = $conn->real_escape_string($_POST['no_hp']);
    $conn->query("UPDATE kurir SET nama_kurir='$nama', no_hp='$hp' WHERE id_kurir=$id");
    header("Location: index.php#kurir-section");
    exit;
}

// Tambah Layanan
if (isset($_POST['tambah_layanan'])) {
    $paket = $conn->real_escape_string($_POST['paket']);
    $nama = $conn->real_escape_string($_POST['nama_layanan']);
    $harga = (int)$_POST['harga_kg'];
    $satuan = $conn->real_escape_string($_POST['satuan']);
    $conn->query("INSERT INTO layanan (paket, nama_layanan, harga_kg, satuan) VALUES ('$paket', '$nama', $harga, '$satuan')");
    header("Location: index.php#layanan-section");
    exit;
}

// Edit Layanan
if (isset($_POST['edit_layanan'])) {
    $id = (int)$_POST['id_layanan_hidden'];
    $harga = (int)$_POST['harga_kg_edit'];
    $conn->query("UPDATE layanan SET harga_kg=$harga WHERE id_layanan=$id");
    header("Location: index.php#layanan-section");
    exit;
}

// Tambah Pesanan
if (isset($_POST['tambah_transaksi'])) {
    $id_customer = (int)$_POST['id_customer'];
    $id_layanan = (int)$_POST['id_layanan'];
    $berat = (float)$_POST['berat'];
    $metode = $_POST['metode_pengambilan'];
    
    if ($metode == 'Ambil di Tempat') {
        $id_kurir = "NULL";
    } else {
        $id_kurir = (int)$_POST['id_kurir'];
    }
    
    $biaya_layanan = ($metode == 'Kurir') ? 5000 : 0;
    
    $q = $conn->query("SELECT harga_kg, satuan FROM layanan WHERE id_layanan = $id_layanan");
    $layanan_data2 = $q->fetch_assoc();
    $harga_kg = $layanan_data2['harga_kg'];
    $satuan = $layanan_data2['satuan'];
    
    $per_item = in_array($satuan, ['potong', 'pasang']);
    if ($per_item) {
        $berat = 1;
        $subtotal = $harga_kg;
    } else {
        $subtotal = $berat * $harga_kg;
    }
    
    $total = $subtotal + $biaya_layanan;
    $row = $conn->query("SELECT id_karyawan FROM karyawan LIMIT 1")->fetch_assoc();
    $id_karyawan = $row['id_karyawan'];
    $status_kurir = ($metode == 'Kurir') ? 'Kurir Menuju Rumah' : 'Selesai Diterima';
    $tgl = date('Y-m-d');
    
    $sql = "INSERT INTO transaksi (id_customer, id_karyawan, id_kurir, id_layanan,
            tanggal_masuk, berat, total_harga, status_laundry, status_kurir, 
            metode_pengambilan, biaya_layanan) VALUES (
            $id_customer, $id_karyawan, $id_kurir, $id_layanan,
            '$tgl', $berat, $total, 'Proses', '$status_kurir', '$metode', $biaya_layanan)";
    $conn->query($sql);
    header("Location: index.php#pesanan-section");
    exit;
}

// Update Status Pesanan
if (isset($_GET['action']) && $_GET['action'] == 'update_laundry') {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    $conn->query("UPDATE transaksi SET status_laundry='$status' WHERE id_transaksi=$id");
    header("Location: index.php#pesanan-section");
    exit;
}

// Hapus Pesanan
if (isset($_GET['action']) && $_GET['action'] == 'delete_transaksi') {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM transaksi WHERE id_transaksi=$id");
    header("Location: index.php#pesanan-section");
    exit;
}

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
$basePath = '';
require __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO SECTION ===== -->
<div class="hero-section scroll-animate" id="hero-section">
    <div class="badge">Dashboard</div>
    <h2>White Laundry</h2>
</div>

<!-- ===== STATS ===== -->
<div class="stats-grid" id="stats-section">
    <div class="stat-card scroll-animate delay-1">
        <span class="stat-icon">📋</span>
        <div class="stat-number"><?= $total_pesanan ?></div>
        <div class="stat-label">Total Pesanan</div>
    </div>
    <div class="stat-card scroll-animate delay-2">
        <span class="stat-icon">💰</span>
        <div class="stat-number">Rp <?= number_format($pendapatan, 0, ',', '.') ?></div>
        <div class="stat-label">Total Pendapatan</div>
    </div>
    <div class="stat-card scroll-animate delay-3">
        <span class="stat-icon">⚡</span>
        <div class="stat-number"><?= $proses ?></div>
        <div class="stat-label">Proses Aktif</div>
    </div>
    <div class="stat-card scroll-animate delay-4">
        <span class="stat-icon">✅</span>
        <div class="stat-number"><?= $selesai ?></div>
        <div class="stat-label">Selesai</div>
    </div>
</div>

<!-- Scroll Indicator -->
<div class="scroll-indicator">
    Scroll ke bawah 
    <span class="arrow">↓</span>
</div>

<!-- ============================================ -->
<!-- SECTION: PESANAN -->
<!-- ============================================ -->
<div class="page-section" id="pesanan-section">
    <div class="section-header scroll-animate">
        <div class="badge-light">Pesanan</div>
        <h3>Pesanan</h3>
    </div>

    <div class="panel scroll-animate">
        <div class="panel-header">
            <h3>Daftar Pesanan</h3>
            <button class="btn btn-primary" onclick="bukaModal('modalTransaksi')">+ Tambah Pesanan</button>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Total</th>
                        <th>Tgl</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $transaksi->data_seek(0);
                if ($transaksi->num_rows > 0): while($t=$transaksi->fetch_assoc()):
                    $badge = 'status-antrean';
                    if (in_array($t['status_laundry'], ['Diproses','Sedang Dicuci'])) $badge = 'status-proses';
                    elseif ($t['status_laundry'] == 'Siap Kirim') $badge = 'status-siapkirim';
                    elseif (in_array($t['status_laundry'], ['Selesai','Selesai Di-packing'])) $badge = 'status-selesai';
                ?>
                    <tr>
                        <td><strong>LDR-<?= $t['id_transaksi'] ?></strong></td>
                        <td><?= htmlspecialchars($t['nama_customer']??'-') ?></td>
                        <td><?= htmlspecialchars($t['nama_layanan']??'-') ?></td>
                        <td>Rp <?= number_format($t['total_harga'],0,',','.') ?></td>
                        <td><?= $t['tanggal_masuk']!='0000-00-00' ? date('d/m/Y',strtotime($t['tanggal_masuk'])) : '-' ?></td>
                        <td><span class="status-badge <?= $badge ?>"><?= htmlspecialchars($t['status_laundry']) ?></span></td>
                        <td style="white-space:nowrap;">
                            <?php if ($t['status_laundry'] == 'Proses'): ?>
                                <a href="?action=update_laundry&id=<?= $t['id_transaksi'] ?>&status=Selesai" class="btn btn-success btn-sm">Selesai</a>
                            <?php endif; ?>
                            <a href="?action=delete_transaksi&id=<?= $t['id_transaksi'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus transaksi ini?')">Hapus</a>
                            <a href="process/cetak_nota.php?id=<?= $t['id_transaksi'] ?>" target="_blank" class="btn btn-info btn-sm">Cetak</a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7" class="empty-msg">Belum ada pesanan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECTION: CUSTOMER -->
<!-- ============================================ -->
<div class="page-section" id="customer-section">
    <div class="section-header scroll-animate">
        <div class="badge-light">Customer</div>
        <h3>Customer</h3>
    </div>

    <div class="panel scroll-animate">
        <div class="panel-header">
            <h3>Daftar Customer</h3>
            <button class="btn btn-primary" onclick="bukaModal('modalCustomer')">+ Tambah Customer</button>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>ID</th><th>Nama</th><th>No. HP</th><th>Alamat</th><th>Total Order</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if ($customers->num_rows > 0): while($c=$customers->fetch_assoc()): ?>
                        <tr>
                            <td><?= $c['id_customer'] ?></td>
                            <td><strong><?= htmlspecialchars($c['nama_customer']) ?></strong></td>
                            <td><?= htmlspecialchars($c['no_telp'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($c['alamat'] ?? '-') ?></td>
                            <td><span class="status-badge status-antrean"><?= $c['total_order'] ?> order</span></td>
                            <td>
                                <a href="?action=delete_customer&id=<?= $c['id_customer'] ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Hapus customer ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="6" class="empty-msg">Belum ada customer terdaftar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECTION: KURIR -->
<!-- ============================================ -->
<div class="page-section" id="kurir-section">
    <div class="section-header scroll-animate">
        <div class="badge-light">Kurir</div>
        <h3>Kurir</h3>
    </div>

    <div class="panel scroll-animate">
        <div class="panel-header">
            <h3>Daftar Kurir</h3>
            <button class="btn btn-primary" onclick="bukaModal('modalKurir')">+ Tambah Kurir</button>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>ID</th><th>Nama Kurir</th><th>No. HP</th><th>Total Kiriman</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if ($kurirs->num_rows > 0): while($k=$kurirs->fetch_assoc()): ?>
                        <tr>
                            <td><?= $k['id_kurir'] ?></td>
                            <td><strong><?= htmlspecialchars($k['nama_kurir']) ?></strong></td>
                            <td><?= htmlspecialchars($k['no_hp'] ?? '-') ?></td>
                            <td><span class="status-badge status-antrean"><?= $k['total_antar'] ?> kiriman</span></td>
                            <td>
                                <button class="btn btn-warning btn-sm" 
                                        onclick="editKurir(<?= $k['id_kurir'] ?>, '<?= addslashes($k['nama_kurir']) ?>', '<?= addslashes($k['no_hp'] ?? '') ?>')">Edit</button>
                                <a href="?action=delete_kurir&id=<?= $k['id_kurir'] ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Hapus kurir ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5" class="empty-msg">Belum ada kurir terdaftar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECTION: LAYANAN -->
<!-- ============================================ -->
<div class="page-section" id="layanan-section">
    <div class="section-header scroll-animate">
        <div class="badge-light">Layanan</div>
        <h3>Layanan</h3>
    </div>

    <div class="panel scroll-animate">
        <div class="panel-header">
            <h3>Daftar Layanan</h3>
        </div>
        
        <!-- Tombol Lihat dan Tambah -->
        <div class="layanan-actions">
            <button class="btn btn-primary" onclick="toggleLayanan(this)">Lihat Layanan</button>
            <button class="btn btn-success" onclick="bukaModal('modalTambahLayanan')">+ Tambah Layanan</button>
        </div>
        
        <!-- Tabel Layanan - DISEMBUNYIKAN DEFAULT -->
        <div class="table-responsive" id="layananTable" style="margin-top: 16px; display: none;">
            <table>
                <thead>
                    <tr><th>Paket</th><th>Nama Layanan</th><th>Harga</th><th>Satuan</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $layanan_data->data_seek(0);
                    if ($layanan_data->num_rows > 0): while($l=$layanan_data->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($l['paket']) ?></td>
                            <td><strong><?= htmlspecialchars($l['nama_layanan']) ?></strong></td>
                            <td>Rp <?= number_format($l['harga_kg'],0,',','.') ?></td>
                            <td><?= htmlspecialchars($l['satuan']) ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" 
                                        onclick="editLayanan(<?= $l['id_layanan'] ?>, '<?= addslashes($l['nama_layanan']) ?>', <?= $l['harga_kg'] ?>)">Edit</button>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5" class="empty-msg">Belum ada layanan terdaftar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODALS -->
<!-- ============================================ -->

<!-- MODAL TAMBAH PESANAN -->
<div class="modal-overlay" id="modalTransaksi">
    <div class="modal-box">
        <h2>+ Tambah Pesanan</h2>
        <form method="POST">
            <label>Customer</label>
            <select name="id_customer" required>
                <option value="">-- Pilih Customer --</option>
                <?php 
                $cust = $conn->query("SELECT * FROM customer ORDER BY nama_customer"); 
                while($c=$cust->fetch_assoc()): 
                ?>
                    <option value="<?= $c['id_customer'] ?>"><?= htmlspecialchars($c['nama_customer']) ?></option>
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
                    <option value="<?= $l['id_layanan'] ?>"><?= htmlspecialchars($l['nama_layanan']) ?> — Rp <?= number_format($l['harga_kg'],0,',','.') ?></option>
                <?php endwhile; ?>
                </optgroup>
            </select>
            
            <label>Berat (Kg)</label>
            <input type="number" name="berat" step="0.5" min="0" placeholder="Contoh: 2.5" value="1">
            
            <label>Metode Pengambilan</label>
            <select name="metode_pengambilan" id="metode_pengambilan" onchange="toggleKurir()" required>
                <option value="Kurir">Pakai Kurir (+ Rp 5.000)</option>
                <option value="Ambil di Tempat">Ambil di Tempat (Gratis)</option>
            </select>
            
            <div id="kurirField">
                <label>Kurir</label>
                <select name="id_kurir">
                    <option value="">-- Pilih Kurir --</option>
                    <?php
                    $kur = $conn->query("SELECT * FROM kurir ORDER BY nama_kurir");
                    while($k = $kur->fetch_assoc()):
                    ?>
                        <option value="<?= $k['id_kurir'] ?>"><?= htmlspecialchars($k['nama_kurir']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-danger" onclick="tutupModal('modalTransaksi')">Batal</button>
                <button type="submit" name="tambah_transaksi" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH CUSTOMER -->
<div class="modal-overlay" id="modalCustomer">
    <div class="modal-box">
        <h2>Tambah Customer</h2>
        <form method="POST">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_customer" placeholder="Contoh: Budi Santoso" required>
            <label>No. HP / WhatsApp</label>
            <input type="text" name="no_telp" placeholder="Contoh: 08123456789">
            <label>Alamat</label>
            <input type="text" name="alamat" placeholder="Contoh: Jl. Merdeka No. 5">
            <div class="modal-actions">
                <button type="button" class="btn btn-danger" onclick="tutupModal('modalCustomer')">Batal</button>
                <button type="submit" name="tambah_customer" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH KURIR -->
<div class="modal-overlay" id="modalKurir">
    <div class="modal-box">
        <h2>Tambah Kurir</h2>
        <form method="POST">
            <label>Nama Kurir</label>
            <input type="text" name="nama_kurir" placeholder="Contoh: Andi" required>
            <label>No. HP</label>
            <input type="text" name="no_hp" placeholder="Contoh: 08987654321">
            <div class="modal-actions">
                <button type="button" class="btn btn-danger" onclick="tutupModal('modalKurir')">Batal</button>
                <button type="submit" name="tambah_kurir" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT KURIR -->
<div class="modal-overlay" id="modalEditKurir">
    <div class="modal-box">
        <h2>Edit Kurir</h2>
        <form method="POST">
            <input type="hidden" name="id_kurir" id="edit_kurir_id">
            <label>Nama Kurir</label>
            <input type="text" name="nama_kurir" id="edit_kurir_nama" required>
            <label>No. HP</label>
            <input type="text" name="no_hp" id="edit_kurir_hp">
            <div class="modal-actions">
                <button type="button" class="btn btn-danger" onclick="tutupModal('modalEditKurir')">Batal</button>
                <button type="submit" name="edit_kurir" class="btn btn-warning">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH LAYANAN -->
<div class="modal-overlay" id="modalTambahLayanan">
    <div class="modal-box">
        <h2>Tambah Layanan</h2>
        <form method="POST">
            <label>Paket</label>
            <select name="paket" required>
                <option value="Paket Kiloan">Paket Kiloan</option>
                <option value="Paket Satuan">Paket Satuan</option>
            </select>
            <label>Nama Layanan</label>
            <input type="text" name="nama_layanan" placeholder="Contoh: Cuci Kering" required>
            <label>Harga</label>
            <input type="number" name="harga_kg" placeholder="10000" required min="0">
            <label>Satuan</label>
            <select name="satuan" required>
                <option value="kg">kg</option>
                <option value="potong">potong</option>
                <option value="pasang">pasang</option>
            </select>
            <div class="modal-actions">
                <button type="button" class="btn btn-danger" onclick="tutupModal('modalTambahLayanan')">Batal</button>
                <button type="submit" name="tambah_layanan" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT LAYANAN -->
<div class="modal-overlay" id="modalEditLayanan">
    <div class="modal-box">
        <h2>Edit Harga Layanan</h2>
        <form method="POST">
            <input type="hidden" name="id_layanan_hidden" id="edit_layanan_id">
            <label>Nama Layanan</label>
            <input type="text" id="edit_layanan_nama" disabled style="background:#f0f4f8;">
            <label>Harga Baru (Rp)</label>
            <input type="number" name="harga_kg_edit" id="edit_layanan_harga" required min="0">
            <div class="modal-actions">
                <button type="button" class="btn btn-danger" onclick="tutupModal('modalEditLayanan')">Batal</button>
                <button type="submit" name="edit_layanan" class="btn btn-warning">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
// ===== TOGGLE KURIR =====
function toggleKurir() {
    const metode = document.getElementById('metode_pengambilan').value;
    const kurirField = document.getElementById('kurirField');
    if (metode === 'Ambil di Tempat') {
        kurirField.style.display = 'none';
    } else {
        kurirField.style.display = 'block';
    }
}
document.addEventListener('DOMContentLoaded', toggleKurir);

// ===== EDIT KURIR =====
function editKurir(id, nama, hp) {
    document.getElementById('edit_kurir_id').value = id;
    document.getElementById('edit_kurir_nama').value = nama;
    document.getElementById('edit_kurir_hp').value = hp;
    bukaModal('modalEditKurir');
}

// ===== EDIT LAYANAN =====
function editLayanan(id, nama, harga) {
    document.getElementById('edit_layanan_id').value = id;
    document.getElementById('edit_layanan_nama').value = nama;
    document.getElementById('edit_layanan_harga').value = harga;
    bukaModal('modalEditLayanan');
}

// ===== TOGGLE LAYANAN TABLE =====
function toggleLayanan(btn) {
    const table = document.getElementById('layananTable');
    
    if (table.style.display === 'none' || table.style.display === '') {
        table.style.display = 'block';
        btn.textContent = 'Sembunyikan Layanan';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-warning');
    } else {
        table.style.display = 'none';
        btn.textContent = 'Lihat Layanan';
        btn.classList.remove('btn-warning');
        btn.classList.add('btn-primary');
    }
}

// ============================================
// NAVBAR SCROLL EFFECT & ACTIVE SECTION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const headerWrapper = document.getElementById('headerWrapper');
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.page-section, #hero-section, #stats-section');

    // ===== SCROLL EFFECT NAVBAR =====
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        
        if (currentScroll > 50) {
            headerWrapper.classList.add('scrolled');
        } else {
            headerWrapper.classList.remove('scrolled');
        }

        // ===== ACTIVE SECTION DETECTION =====
        let currentSection = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop - 120;
            const sectionBottom = sectionTop + section.offsetHeight;

            if (currentScroll >= sectionTop && currentScroll < sectionBottom) {
                currentSection = section.id;
            }
        });

        if (currentScroll < 100) {
            currentSection = 'hero-section';
        }

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + currentSection) {
                link.classList.add('active');
            }
        });
    });

    // ===== SMOOTH SCROLL NAV =====
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });

                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    // ===== SCROLL ANIMATION =====
    const animateElements = document.querySelectorAll('.scroll-animate');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { 
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    animateElements.forEach(el => observer.observe(el));

    setTimeout(() => {
        window.dispatchEvent(new Event('scroll'));
    }, 100);
});
</script>

<?php require __DIR__ . '/includes/footer.php'; $conn->close(); ?>