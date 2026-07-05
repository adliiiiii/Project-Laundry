<?php
require __DIR__ . '/databases/config.php';

$layanan = [
    ['Paket Kiloan', 'Cuci Kering Setrika', 10000, 'kg'],
    ['Paket Kiloan', 'Cuci Kering Saja', 7000, 'kg'],
    ['Paket Satuan', 'Bed Cover', 45000, 'potong'],
    ['Paket Satuan', 'Jas', 35000, 'potong'],
    ['Paket Satuan', 'Sepatu', 60000, 'pasang'],
    ['Paket Satuan', 'Boneka', 15000, 'potong'],
];

foreach ($layanan as $l) {
    $conn->query("INSERT INTO layanan (paket, nama_layanan, harga_kg, satuan) 
                  VALUES ('$l[0]', '$l[1]', $l[2], '$l[3]')");
}

echo "✅ Data layanan berhasil ditambahkan!";
$conn->close();
?>