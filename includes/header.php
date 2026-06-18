<?php
// header.php — include di setiap halaman
// $pageTitle dan $activePage harus di-set sebelum include
if (!isset($pageTitle)) $pageTitle = 'White Clean';
if (!isset($activePage)) $activePage = '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> — White Clean</title>
    <link rel="stylesheet" href="<?= $basePath ?>style/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🧺 White Clean</h1>
        <span><?= date('d F Y') ?></span>
    </div>
    <nav class="nav">
        <a href="<?= $basePath ?>index.php" class="<?= $activePage=='dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
        <a href="<?= $basePath ?>process/pesanan.php" class="<?= $activePage=='pesanan' ? 'active' : '' ?>">📋 Pesanan</a>
        <a href="<?= $basePath ?>process/customer.php" class="<?= $activePage=='customer' ? 'active' : '' ?>">👤 Customer</a>
        <a href="<?= $basePath ?>process/kurir.php" class="<?= $activePage=='kurir' ? 'active' : '' ?>">🛵 Kurir</a>
        <a href="<?= $basePath ?>process/layanan.php" class="<?= $activePage=='layanan' ? 'active' : '' ?>">🧺 Layanan</a>
    </nav>