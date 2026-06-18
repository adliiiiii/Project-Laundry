<?php
require __DIR__ . '/../databases/config.php';

// TAMBAH KURIR
if (isset($_POST['tambah_kurir'])) {
    $nama = $conn->real_escape_string($_POST['nama_kurir']);
    $hp   = $conn->real_escape_string($_POST['no_hp']);
    $conn->query("INSERT INTO kurir (nama_kurir, no_hp) VALUES ('$nama','$hp')");
    header("Location: kurir.php?msg=added");
    exit;
}

// HAPUS KURIR
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM kurir WHERE id_kurir=$id");
    header("Location: kurir.php");
    exit;
}

$kurirs = $conn->query("SELECT kr.*, COUNT(t.id_transaksi) AS total_antar 
    FROM kurir kr 
    LEFT JOIN transaksi t ON kr.id_kurir=t.id_kurir 
    GROUP BY kr.id_kurir 
    ORDER BY kr.id_kurir");

$pageTitle  = 'Kurir';
$activePage = 'kurir';
$basePath   = '../';
require __DIR__ . '/../includes/header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2>🛵 Daftar Kurir</h2>
        <button class="btn btn-primary" onclick="bukaModal('modalKurir')">+ Tambah Kurir</button>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>ID</th><th>Nama Kurir</th><th>No. HP</th><th>Total Pengiriman</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if ($kurirs->num_rows > 0): while($k=$kurirs->fetch_assoc()): ?>
                    <tr>
                        <td><?= $k['id_kurir'] ?></td>
                        <td><strong><?= $k['nama_kurir'] ?></strong></td>
                        <td><?= $k['no_hp'] ?? '-' ?></td>
                        <td><span class="status-badge status-antrean"><?= $k['total_antar'] ?> pesanan</span></td>
                        <td>
                            <a href="?action=delete&id=<?= $k['id_kurir'] ?>" class="btn btn-danger btn-sm"
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

<?php
require __DIR__ . '/../includes/footer.php';
if(isset($_GET['msg']) && $_GET['msg']=='added') echo "<script>alert('✅ Kurir berhasil ditambahkan!');</script>";
$conn->close();
?>