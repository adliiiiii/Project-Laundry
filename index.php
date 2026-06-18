<?php
require __DIR__ . '/databases/config.php';

// AMBIL STATISTIK
$transaksi = $conn->query("SELECT t.*, c.nama_customer, l.nama_layanan 
    FROM transaksi t 
    LEFT JOIN customer c ON t.id_customer=c.id_customer 
    LEFT JOIN layanan l ON t.id_layanan=l.id_layanan 
    ORDER BY t.tanggal_masuk DESC");

$total_pesanan = $transaksi->num_rows;
$pendapatan = 0; $proses = 0; $selesai = 0;
while ($row = $transaksi->fetch_assoc()) {
    $pendapatan += $row['total_harga'];
    if (in_array($row['status_laundry'], ['Selesai','Selesai Di-packing'])) $selesai++;
    else $proses++;
}
$transaksi->data_seek(0);

$total_customer = $conn->query("SELECT COUNT(*) AS c FROM customer")->fetch_assoc()['c'];

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
$basePath   = '';
require __DIR__ . '/includes/header.php';
?>

<!-- STATISTIK -->
<div class="stats">
    <div class="card">
        <div class="num"><?= $total_pesanan ?></div>
        <div class="label">Total Pesanan</div>
    </div>
    <div class="card">
        <div class="num">Rp <?= number_format($pendapatan,0,',','.') ?></div>
        <div class="label">Total Pendapatan</div>
    </div>
    <div class="card">
        <div class="num"><?= $proses ?></div>
        <div class="label">Proses Aktif</div>
    </div>
    <div class="card">
        <div class="num"><?= $selesai ?></div>
        <div class="label">Selesai</div>
    </div>
</div>

<!-- PESANAN TERBARU -->
<div class="panel">
    <div class="panel-header">
        <h2>📋 Pesanan Terbaru</h2>
        <a href="process/pesanan.php" class="btn btn-primary btn-sm">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>ID</th><th>Pelanggan</th><th>Layanan</th><th>Harga</th><th>Tgl Masuk</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php
            $count = 0;
            if ($transaksi->num_rows > 0):
                while ($t = $transaksi->fetch_assoc()):
                    if ($count >= 5) break;
                    $count++;
                    $status = $t['status_laundry'];
                    $badge  = 'status-antrean';
                    if (in_array($status,['Diproses','Sedang Dicuci'])) $badge = 'status-proses';
                    elseif ($status == 'Siap Kirim') $badge = 'status-siapkirim';
                    elseif (in_array($status,['Selesai','Selesai Di-packing'])) $badge = 'status-selesai';
            ?>
                <tr>
                    <td><strong><?= 'LDR-'.$t['id_transaksi'] ?></strong></td>
                    <td><?= $t['nama_customer'] ?? '-' ?></td>
                    <td><?= $t['nama_layanan'] ?? '-' ?></td>
                    <td>Rp <?= number_format($t['total_harga'],0,',','.') ?></td>
                    <td><?= $t['tanggal_masuk'] != '0000-00-00' ? date('d/m/Y',strtotime($t['tanggal_masuk'])) : '-' ?></td>
                    <td><span class="status-badge <?= $badge ?>"><?= $status ?></span></td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="6" class="empty-msg">Belum ada pesanan.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- RINGKASAN BAWAH -->
<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-top:10px;">
    <div class="panel" style="margin-bottom:0;">
        <div class="panel-header"><h2>👤 Customer</h2></div>
        <p style="font-size:32px; font-weight:700; color:#0b2a4a;"><?= $total_customer ?></p>
        <p style="color:#4a6b8a; margin-top:6px; font-size:14px;">Customer terdaftar</p>
        <a href="process/customer.php" class="btn btn-primary btn-sm" style="margin-top:14px;">Kelola Customer</a>
    </div>
    <div class="panel" style="margin-bottom:0;">
        <div class="panel-header"><h2>🛵 Kurir</h2></div>
        <?php $total_kurir = $conn->query("SELECT COUNT(*) AS c FROM kurir")->fetch_assoc()['c']; ?>
        <p style="font-size:32px; font-weight:700; color:#0b2a4a;"><?= $total_kurir ?></p>
        <p style="color:#4a6b8a; margin-top:6px; font-size:14px;">Kurir aktif</p>
        <a href="process/kurir.php" class="btn btn-primary btn-sm" style="margin-top:14px;">Kelola Kurir</a>
    </div>
    <div class="panel" style="margin-bottom:0;">
        <div class="panel-header"><h2>🧺 Layanan</h2></div>
        <?php $total_layanan = $conn->query("SELECT COUNT(*) AS c FROM layanan")->fetch_assoc()['c']; ?>
        <p style="font-size:32px; font-weight:700; color:#0b2a4a;"><?= $total_layanan ?></p>
        <p style="color:#4a6b8a; margin-top:6px; font-size:14px;">Jenis layanan</p>
        <a href="process/layanan.php" class="btn btn-primary btn-sm" style="margin-top:14px;">Kelola Layanan</a>
    </div>
</div>

<?php
require __DIR__ . '/includes/footer.php';
$conn->close();
?>