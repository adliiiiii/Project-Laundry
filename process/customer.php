<?php
require __DIR__ . '/../databases/config.php';

// TAMBAH CUSTOMER
if (isset($_POST['tambah_customer'])) {
    $nama   = $conn->real_escape_string($_POST['nama_customer']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $telp   = $conn->real_escape_string($_POST['no_telp']);
    $conn->query("INSERT INTO customer (nama_customer, alamat, no_telp) VALUES ('$nama','$alamat','$telp')");
    header("Location: customer.php?msg=added");
    exit;
}

// HAPUS CUSTOMER
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM transaksi WHERE id_customer=$id");
    $conn->query("DELETE FROM customer WHERE id_customer=$id");
    header("Location: customer.php");
    exit;
}

$customers = $conn->query("SELECT c.*, COUNT(t.id_transaksi) AS total_order 
    FROM customer c 
    LEFT JOIN transaksi t ON c.id_customer=t.id_customer 
    GROUP BY c.id_customer 
    ORDER BY c.id_customer");

$pageTitle  = 'Customer';
$activePage = 'customer';
$basePath   = '../';
require __DIR__ . '/../includes/header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2>👤 Daftar Customer</h2>
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
                        <td><strong><?= $c['nama_customer'] ?></strong></td>
                        <td><?= $c['no_telp'] ?? '-' ?></td>
                        <td><?= $c['alamat'] ?? '-' ?></td>
                        <td><span class="status-badge status-antrean"><?= $c['total_order'] ?> order</span></td>
                        <td>
                            <a href="?action=delete&id=<?= $c['id_customer'] ?>" class="btn btn-danger btn-sm" 
                               onclick="return confirm('Hapus customer ini beserta semua transaksinya?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6" class="empty-msg">Belum ada customer terdaftar.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
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
            <input type="text" name="alamat" placeholder="Contoh: Jl. Merdeka No. 5, Jakarta">
            <div class="modal-actions">
                <button type="button" class="btn btn-danger" onclick="tutupModal('modalCustomer')">Batal</button>
                <button type="submit" name="tambah_customer" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php
require __DIR__ . '/../includes/footer.php';
if(isset($_GET['msg']) && $_GET['msg']=='added') echo "<script>alert('✅ Customer berhasil ditambahkan!');</script>";
$conn->close();
?>