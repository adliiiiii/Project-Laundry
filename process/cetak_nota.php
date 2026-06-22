<?php
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'laundry_web2';
$conn = new mysqli($host, $user, $pass, $db);
if (!isset($_GET['id'])) die("ID transaksi tidak ditemukan.");
$id = intval($_GET['id']);
$q = $conn->query("SELECT t.*, c.nama_customer, k.nama_karyawan, l.nama_layanan, l.harga_kg 
                   FROM transaksi t 
                   LEFT JOIN customer c ON t.id_customer=c.id_customer 
                   LEFT JOIN karyawan k ON t.id_karyawan=k.id_karyawan 
                   LEFT JOIN layanan l ON t.id_layanan=l.id_layanan 
                   WHERE t.id_transaksi=$id");
if ($q->num_rows == 0) die("Transaksi tidak ditemukan.");
$t = $q->fetch_assoc();

$subtotal = $t['total_harga'];
$pajak = round($subtotal * 0.05);
$grand = $subtotal + $pajak;
$bayar = ceil($grand / 1000) * 1000;
$kembali = $bayar - $grand;
$no_nota = $t['order_id'] ?? 'LRJ-'.str_pad($t['id_transaksi'],4,'0',STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Laundry</title>
    <style>
        body { font-family: 'Courier New', monospace; background:#eee; display:flex; justify-content:center; padding:20px; }
        .nota { width:300px; background:#fff; padding:15px; border:1px solid #aaa; font-size:13px; }
        .header { text-align:center; border-bottom:1px dashed #333; padding-bottom:8px; margin-bottom:8px; }
        .header h1 { font-size:20px; font-weight:bold; }
        .row { display:flex; justify-content:space-between; }
        table { width:100%; border-collapse:collapse; font-size:12px; margin:8px 0; }
        th { border-bottom:1px solid #333; text-align:left; }
        td { border-bottom:1px dashed #ccc; padding:3px 0; }
        .text-right { text-align:right; }
        .text-center { text-align:center; }
        .total { border-top:1px solid #333; padding-top:6px; margin-top:6px; }
        .grand { font-weight:bold; font-size:16px; border-top:2px double #333; padding-top:6px; margin-top:6px; }
        .payment { margin-top:10px; }
        .footer { text-align:center; border-top:1px dashed #ccc; padding-top:10px; margin-top:12px; font-size:11px; }
        .btn-print { display:block; width:100%; padding:8px; background:#2B7BE4; color:#fff; border:none; cursor:pointer; margin-top:15px; }
        @media print { body { background:#fff; } .nota { border:none; } .btn-print { display:none; } }
    </style>
</head>
<body>
<div class="nota">
    <div class="header">
        <h1>White Clean</h1>
        <p>Pencuci Handal & Terpercaya</p>
        <p style="font-size:10px;color:#666;">Jl. Merdeka No. 123, Telp. (021) 1234567</p>
    </div>
    <div class="row"><strong>No. Nota</strong><span><?= htmlspecialchars($no_nota) ?></span></div>
    <div class="row"><strong>Tanggal</strong><span><?= date('d/m/Y H:i',strtotime($t['tanggal_masuk'])) ?> WIB</span></div>
    <div class="row"><strong>Customer</strong><span><?= htmlspecialchars($t['nama_customer'] ?? '-') ?></span></div>
    <div class="row"><strong>Kasir</strong><span><?= htmlspecialchars($t['nama_karyawan'] ?? 'Admin') ?></span></div>
    <table>
        <thead><tr><th>Item</th><th class="text-center">Jml</th><th class="text-right">Harga</th><th class="text-right">Total</th></tr></thead>
        <tbody>
            <?php 
            $layanan = $t['nama_layanan'];
            $harga_kg = $t['harga_kg'];
            $berat = $t['berat'];
            $qty = ($berat > 0 && $berat != 1) ? number_format($berat,2).' kg' : '1x';
            $price = number_format($harga_kg,0,',','.');
            $total = number_format($t['total_harga'],0,',','.');
            ?>
            <tr><td><?= htmlspecialchars($layanan) ?></td><td class="text-center"><?= $qty ?></td><td class="text-right">Rp <?= $price ?></td><td class="text-right">Rp <?= $total ?></td></tr>
            <?php if (in_array($layanan, ['Cuci Kering','Cuci Setrika','Cuci Lipat','Setrika'])): ?>
                <tr><td colspan="4" style="font-size:10px;color:#555;">(Pakaian Campuran, <?= $layanan ?>)</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="total"><div class="row"><span>Subtotal</span><span>Rp <?= number_format($subtotal,0,',','.') ?></span></div>
    <div class="row"><span>Pajak (5%)</span><span>Rp <?= number_format($pajak,0,',','.') ?></span></div></div>
    <div class="grand"><div class="row"><span>GRAND TOTAL</span><span>Rp <?= number_format($grand,0,',','.') ?></span></div></div>
    <div class="payment">
        <div class="row"><span>Bayar (Tunai)</span><span>Rp <?= number_format($bayar,0,',','.') ?></span></div>
        <div class="row"><span>Kembali</span><span>Rp <?= number_format($kembali,0,',','.') ?></span></div>
        <div class="row" style="margin-top:5px;"><span>Status</span><span style="font-weight:bold;">LUNAS</span></div>
        <div style="font-size:11px;color:#555;margin-top:5px;">Ambil: Estimasi <?= date('d/m/Y',strtotime($t['tanggal_masuk'].' +2 days')) ?> - <?= date('d/m/Y',strtotime($t['tanggal_masuk'].' +3 days')) ?></div>
    </div>
    <div class="footer">Terima Kasih Atas Kepercayaan Anda!</div>
    <div class="footer">Note : Pembayaran dapat dilakukan setelah baju diambil</div>
    <button class="btn-print" onclick="window.print()">🖨️ Cetak Nota</button>
</div>
</body>
</html>
<?php $conn->close(); ?>