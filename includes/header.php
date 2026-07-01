<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if (!isset($pageTitle)) $pageTitle = 'White Clean';
if (!isset($activePage)) $activePage = '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — White Clean</title>
    <link rel="stylesheet" href="<?= $basePath ?>style/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- ===== BACKGROUND FULL ===== -->
<div class="background-wrapper">
    <div class="background-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
</div>

<div class="container">
    <!-- ===== HEADER ===== -->
    <div class="header-wrapper" id="headerWrapper">
        <div class="header-content">
            <div class="header-left">
                <span class="icon">🧺</span>
                <div>
                    <h1>White Clean</h1>
                    <span>★ Laundry Handal &amp; Terpercaya</span>
                </div>
            </div>
            <div class="header-right">
                <div class="user-badge">
                    <div class="avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?></div>
                    <span class="name">admin</span>
                </div>
                <a href="<?= $basePath ?>logout.php" class="btn-logout" onclick="return confirm('Yakin logout?')">Keluar</a>
            </div>
        </div>

        <!-- NAV -->
        <nav class="nav" id="mainNav">
            <a href="#hero-section" class="nav-link active" data-section="hero">Dashboard</a>
            <a href="#pesanan-section" class="nav-link" data-section="pesanan">Pesanan</a>
            <a href="#customer-section" class="nav-link" data-section="customer">Customer</a>
            <a href="#kurir-section" class="nav-link" data-section="kurir">Kurir</a>
            <a href="#layanan-section" class="nav-link" data-section="layanan">Layanan</a>
        </nav>
    </div>