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
    $check = $conn->query("SELECT COUNT(*) as total FROM transaksi WHERE id_kurir = $id");
    $row = $check->fetch_assoc();
    if ($row['total'] > 0) {
        header("Location: kurir.php?error=Kurir tidak dapat dihapus karena masih memiliki transaksi terkait!");
        exit;
    } else {
        if ($conn->query("DELETE FROM kurir WHERE id_kurir=$id")) {
            header("Location: kurir.php?msg=deleted");
        } else {
            header("Location: kurir.php?error=Gagal menghapus kurir!");
        }
        exit;
    }
}

// EDIT KURIR
if (isset($_POST['edit_kurir'])) {
    $id = (int)$_POST['id_kurir'];
    $nama = $conn->real_escape_string($_POST['nama_kurir']);
    $hp = $conn->real_escape_string($_POST['no_hp']);
    $conn->query("UPDATE kurir SET nama_kurir='$nama', no_hp='$hp' WHERE id_kurir=$id");
    header("Location: kurir.php?msg=updated");
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

<!-- ===== HERO SECTION ===== -->
<div class="hero-section scroll-animate">
    <div class="badge">🛵 Kurir</div>
    <h2>Kelola Kurir</h2>
    <p>Kelola data kurir laundry White Clean dengan mudah</p>
</div>

<!-- ===== PANEL KURIR ===== -->
<div class="panel scroll-animate">
    <div class="panel-header">
        <h3>🛵 Daftar Kurir</h3>
        <button class="btn btn-primary" onclick="bukaModal('modalKurir')">+ Tambah Kurir</button>
    </div>
    
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'added'): ?>
            <div class="alert alert-success alert-dismissible" style="margin: 15px 0;">
                ✅ Kurir berhasil ditambahkan!
                <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">×</button>
            </div>
        <?php elseif ($_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible" style="margin: 15px 0;">
                ✅ Kurir berhasil dihapus!
                <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">×</button>
            </div>
        <?php elseif ($_GET['msg'] == 'updated'): ?>
            <div class="alert alert-success alert-dismissible" style="margin: 15px 0;">
                ✅ Kurir berhasil diperbarui!
                <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">×</button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible" style="margin: 15px 0;">
            ❌ <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">×</button>
        </div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Kurir</th>
                    <th>No. HP</th>
                    <th>Total Pengiriman</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($kurirs->num_rows > 0): while($k = $kurirs->fetch_assoc()): ?>
                    <tr>
                        <td><?= $k['id_kurir'] ?></td>
                        <td><strong><?= htmlspecialchars($k['nama_kurir']) ?></strong></td>
                        <td><?= htmlspecialchars($k['no_hp'] ?? '-') ?></td>
                        <td><span class="status-badge status-antrean"><?= $k['total_antar'] ?> pesanan</span></td>
                        <td>
                            <button class="btn btn-warning btn-sm" 
                                    onclick="editKurir(<?= $k['id_kurir'] ?>, '<?= addslashes($k['nama_kurir']) ?>', '<?= addslashes($k['no_hp'] ?? '') ?>')">
                                Edit
                            </button>
                            <a href="?action=delete&id=<?= $k['id_kurir'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Apakah Anda yakin ingin menghapus kurir <?= htmlspecialchars($k['nama_kurir']) ?>?')">
                                Hapus
                            </a>
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
        <form method="POST" onsubmit="return validateForm(this)">
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
            <input type="hidden" name="id_kurir" id="edit_id">
            <label>Nama Kurir</label>
            <input type="text" name="nama_kurir" id="edit_nama" placeholder="Contoh: Andi" required>
            <label>No. HP</label>
            <input type="text" name="no_hp" id="edit_hp" placeholder="Contoh: 08987654321">
            <div class="modal-actions">
                <button type="button" class="btn btn-danger" onclick="tutupModal('modalEditKurir')">Batal</button>
                <button type="submit" name="edit_kurir" class="btn btn-warning">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function editKurir(id, nama, hp) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_hp').value = hp;
    bukaModal('modalEditKurir');
}

function validateForm(form) {
    var nama = form.nama_kurir.value.trim();
    if (nama === '') {
        alert('Nama kurir harus diisi!');
        return false;
    }
    return true;
}

// ===== NAVBAR SCROLL EFFECT =====
const headerWrapper = document.getElementById('headerWrapper');
window.addEventListener('scroll', function() {
    const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    if (currentScroll > 50) {
        headerWrapper.classList.add('scrolled');
    } else {
        headerWrapper.classList.remove('scrolled');
    }
});

// ===== SCROLL ANIMATION =====
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('.scroll-animate');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });
    elements.forEach(el => observer.observe(el));
});

// Hapus parameter URL setelah alert
if (window.history && window.history.replaceState) {
    var url = window.location.href.split('?')[0];
    window.history.replaceState(null, null, url);
}
</script>

<?php
require __DIR__ . '/../includes/footer.php';
$conn->close();
?>