<?php
require __DIR__ . '/databases/config.php';

$layanan = [
    ['Paket Kiloan', 'Cuci Kering Setrika', 10000, 'per kg', 2],
    ['Paket Kiloan', 'Cuci Kering Saja', 7000, 'per kg', 2],
    ['Paket Satuan', 'Bed Cover', 45000, 'per potong', 3],
    ['Paket Satuan', 'Jas', 35000, 'per potong', 3],
    ['Paket Satuan', 'Sepatu', 60000, 'per pasang', 3],
    ['Paket Satuan', 'Boneka', 15000, 'per potong', 2],
];

foreach ($layanan as $l) {
    $conn->query("INSERT INTO layanan (paket, nama_layanan, harga_kg, satuan, estimasi_perhari) 
                  VALUES ('$l[0]', '$l[1]', $l[2], '$l[3]', $l[4])");
}

echo "✅ Data layanan berhasil ditambahkan!";
$conn->close();
?>