<?php
require __DIR__ . '/../databases/config.php';

// TAMBAH LAYANAN
if (isset($_POST['tambah_layanan'])) {
    $paket    = $conn->real_escape_string($_POST['paket']);
    $nama     = $conn->real_escape_string($_POST['nama_layanan']);
    $harga    = (int)$_POST['harga_kg'];
    $satuan   = $conn->real_escape_string($_POST['satuan']);
    
    $conn->query("INSERT INTO layanan (paket, nama_layanan, harga_kg, satuan) 
                  VALUES ('$paket', '$nama', $harga, '$satuan')");
    header("Location: layanan.php?msg=added");
    exit;
}

// EDIT LAYANAN
if (isset($_POST['edit_layanan'])) {
    $id       = (int)$_POST['id_layanan_hidden'];
    $harga    = (int)$_POST['harga_kg_edit'];
    
    $conn->query("UPDATE layanan SET harga_kg=$harga WHERE id_layanan=$id");
    header("Location: layanan.php?msg=edited");
    exit;
}

$pageTitle  = 'Layanan';
$activePage = 'layanan';
$basePath   = '../';
require __DIR__ . '/../includes/header.php';
?>

<!-- HALAMAN UTAMA -->
<div class="panel" style="text-align: center; padding: 50px 20px;">
    <h2 style="color: #0b2a4a; margin-bottom: 15px; font-weight: 600;">Layanan</h2>
    <p style="color: #6b7f8f; margin-bottom: 30px; font-size: 15px;">
        Klik tombol Lihat untuk melihat daftar layanan.
    </p>
    <div style="display: flex; gap: 12px; justify-content: center;">
        <button onclick="bukaModal('modalDaftarLayanan')" style="
            padding: 10px 28px;
            font-size: 14px;
            font-weight: 500;
            background: #1e6fc7;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        ">
            Lihat
        </button>
        <button onclick="bukaModal('modalTambahLayanan')" style="
            padding: 10px 28px;
            font-size: 14px;
            font-weight: 500;
            background: #22c55e;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        ">
            Tambah
        </button>
    </div>
</div>

<!-- MODAL DAFTAR LAYANAN (PERSIS GAMBAR) -->
<div class="modal-overlay" id="modalDaftarLayanan">
    <div class="modal-box" style="max-width: 650px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #dce8f5; padding-bottom: 15px;">
            <h3 style="margin: 0; font-weight: 600; color: #0b2a4a; font-size: 18px;">Daftar Layanan</h3>
            <button onclick="tutupModal('modalDaftarLayanan')" style="
                background: none;
                border: none;
                font-size: 22px;
                color: #6b7f8f;
                cursor: pointer;
                padding: 0 5px;
                line-height: 1;
            ">
                ×
            </button>
        </div>
        
        <div class="table-responsive">
            <table style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 25%; padding: 12px 15px; text-align: left; background: #dbeafe; color: #0b2a4a; font-weight: 600;">Paket</th>
                        <th style="width: 40%; padding: 12px 15px; text-align: left; background: #dbeafe; color: #0b2a4a; font-weight: 600;">Layanan</th>
                        <th style="width: 35%; padding: 12px 15px; text-align: left; background: #dbeafe; color: #0b2a4a; font-weight: 600;">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Ambil data dan kelompokkan berdasarkan paket
                    $query = $conn->query("SELECT * FROM layanan ORDER BY paket, nama_layanan");
                    $groups = [];
                    while ($row = $query->fetch_assoc()) {
                        $groups[$row['paket']][] = $row;
                    }
                    
                    if (count($groups) > 0) {
                        foreach ($groups as $paket => $items) {
                            $total = count($items);
                            $first = true;
                            foreach ($items as $item) {
                    ?>
                    <tr>
                        <?php if ($first): ?>
                        <td rowspan="<?= $total ?>" style="
                            vertical-align: middle;
                            background: #f8faff;
                            padding: 14px 15px;
                            border-bottom: 1px solid #e9eff6;
                            font-weight: 400;
                            color: #0b2a4a;
                        ">
                            <?= htmlspecialchars($paket) ?>
                        </td>
                        <?php $first = false; endif; ?>
                        <td style="padding: 14px 15px; border-bottom: 1px solid #e9eff6; background: #fff;">
                            <?= htmlspecialchars($item['nama_layanan']) ?>
                        </td>
                        <td style="padding: 14px 15px; border-bottom: 1px solid #e9eff6; background: #fff;">
                            Rp <?= number_format($item['harga_kg'], 0, ',', '.') ?>
                            <span style="color: #6b7f8f;">/ <?= htmlspecialchars($item['satuan']) ?></span>
                        </td>
                    </tr>
                    <?php 
                            }
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 30px; color: #6b7f8f;">
                            Belum ada layanan terdaftar.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        
        <!-- TOMBOL EDIT & TUTUP -->
        <div style="display: flex; gap: 12px; margin-top: 22px; justify-content: flex-end; border-top: 1px solid #dce8f5; padding-top: 18px;">
            <button onclick="tutupModal('modalDaftarLayanan'); bukaModal('modalEditLayanan')" style="
                padding: 8px 28px;
                background: #f59e0b;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
            ">
                Edit
            </button>
            <button onclick="tutupModal('modalDaftarLayanan')" style="
                padding: 8px 28px;
                background: #ef4444;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
            ">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH LAYANAN -->
<div class="modal-overlay" id="modalTambahLayanan">
    <div class="modal-box" style="max-width: 450px;">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-weight: 600; color: #0b2a4a; font-size: 18px;">Tambah Layanan</h3>
        <form method="POST">
            <label style="display: block; font-weight: 500; color: #0b2a4a; margin-bottom: 5px; font-size: 14px;">Paket</label>
            <select name="paket" required style="
                width: 100%; 
                padding: 8px 12px; 
                border: 1px solid #dce8f5; 
                border-radius: 4px; 
                margin-bottom: 15px; 
                font-size: 14px;
                background: white;
            ">
                <option value="Paket Kiloan">Paket Kiloan</option>
                <option value="Paket Satuan">Paket Satuan</option>
            </select>
            
            <label style="display: block; font-weight: 500; color: #0b2a4a; margin-bottom: 5px; font-size: 14px;">Nama Layanan</label>
            <input type="text" name="nama_layanan" placeholder="Contoh: Cuci Kering Setrika" required style="
                width: 100%; 
                padding: 8px 12px; 
                border: 1px solid #dce8f5; 
                border-radius: 4px; 
                margin-bottom: 15px; 
                font-size: 14px;
            ">
            
            <label style="display: block; font-weight: 500; color: #0b2a4a; margin-bottom: 5px; font-size: 14px;">Harga</label>
            <input type="number" name="harga_kg" placeholder="Contoh: 10000" required min="0" step="500" style="
                width: 100%; 
                padding: 8px 12px; 
                border: 1px solid #dce8f5; 
                border-radius: 4px; 
                margin-bottom: 15px; 
                font-size: 14px;
            ">
            
            <label style="display: block; font-weight: 500; color: #0b2a4a; margin-bottom: 5px; font-size: 14px;">Satuan</label>
            <select name="satuan" required style="
                width: 100%; 
                padding: 8px 12px; 
                border: 1px solid #dce8f5; 
                border-radius: 4px; 
                margin-bottom: 20px; 
                font-size: 14px;
                background: white;
            ">
                <option value="kg">kg</option>
                <option value="potong">potong</option>
                <option value="pasang">pasang</option>
            </select>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid #e9eff6; padding-top: 20px;">
                <button type="button" onclick="tutupModal('modalTambahLayanan')" style="
                    padding: 8px 20px;
                    font-size: 13px;
                    background: #ef4444;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                ">
                    Batal
                </button>
                <button type="submit" name="tambah_layanan" style="
                    padding: 8px 20px;
                    font-size: 13px;
                    background: #22c55e;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                ">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT LAYANAN -->
<div class="modal-overlay" id="modalEditLayanan">
    <div class="modal-box" style="max-width: 400px;">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-weight: 600; color: #0b2a4a; font-size: 18px;">Edit Harga</h3>
        <form method="POST">
            <label style="display: block; font-weight: 500; color: #0b2a4a; margin-bottom: 5px; font-size: 14px;">Pilih Layanan</label>
            <select name="id_layanan_hidden" id="editLayananSelect" required onchange="loadLayananData()" style="
                width: 100%;
                padding: 8px 12px;
                border: 1px solid #dce8f5;
                border-radius: 4px;
                margin-bottom: 15px;
                font-size: 14px;
                background: white;
            ">
                <option value="">-- Pilih Layanan --</option>
                <?php 
                $layanan_list = $conn->query("SELECT * FROM layanan ORDER BY paket, nama_layanan");
                while($l = $layanan_list->fetch_assoc()):
                ?>
                    <option value="<?= $l['id_layanan'] ?>" 
                            data-nama="<?= htmlspecialchars($l['nama_layanan']) ?>"
                            data-harga="<?= $l['harga_kg'] ?>"
                            data-satuan="<?= htmlspecialchars($l['satuan']) ?>">
                        <?= htmlspecialchars($l['paket']) ?> — <?= htmlspecialchars($l['nama_layanan']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <div style="background: #f8faff; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: #6b7f8f;">Layanan</span>
                    <span id="displayNama" style="font-weight: 500; color: #0b2a4a;">-</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #6b7f8f;">Harga saat ini</span>
                    <span id="displayHarga" style="font-weight: 500; color: #0b2a4a;">-</span>
                </div>
            </div>
            
            <label style="display: block; font-weight: 500; color: #0b2a4a; margin-bottom: 5px; font-size: 14px;">Harga Baru (Rp)</label>
            <input type="number" name="harga_kg_edit" id="editHarga" required min="0" step="500" style="
                width: 100%;
                padding: 8px 12px;
                border: 1px solid #dce8f5;
                border-radius: 4px;
                margin-bottom: 20px;
                font-size: 14px;
            ">
            
            <input type="hidden" name="id_layanan_hidden" id="editId">
            
            <div style="display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid #e9eff6; padding-top: 20px;">
                <button type="button" onclick="tutupModal('modalEditLayanan')" style="
                    padding: 8px 20px;
                    font-size: 13px;
                    background: #ef4444;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                ">
                    Batal
                </button>
                <button type="submit" name="edit_layanan" style="
                    padding: 8px 20px;
                    font-size: 13px;
                    background: #22c55e;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                ">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function loadLayananData() {
    const select = document.getElementById('editLayananSelect');
    const selected = select.options[select.selectedIndex];
    
    if (selected.value) {
        document.getElementById('displayNama').textContent = selected.dataset.nama;
        document.getElementById('displayHarga').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(selected.dataset.harga);
        document.getElementById('editHarga').value = selected.dataset.harga;
        document.getElementById('editId').value = selected.value;
    } else {
        document.getElementById('displayNama').textContent = '-';
        document.getElementById('displayHarga').textContent = '-';
        document.getElementById('editHarga').value = '';
        document.getElementById('editId').value = '';
    }
}

<?php
if(isset($_GET['msg'])) {
    if($_GET['msg']=='added') echo "alert('Layanan berhasil ditambahkan!');";
    if($_GET['msg']=='edited') echo "alert('Harga layanan berhasil diperbarui!');";
}
?>
</script>

<?php
require __DIR__ . '/../includes/footer.php';
$conn->close();
?>