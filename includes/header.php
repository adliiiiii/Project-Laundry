<?php
// header.php — include di setiap halaman
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// $pageTitle dan $activePage harus di-set sebelum include
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
    <style>
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-left: auto;
        }
        
        .user-info .user-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.15);
            padding: 6px 16px 6px 12px;
            border-radius: 30px;
            backdrop-filter: blur(4px);
        }
        
        .user-info .user-badge .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }
        
        .user-info .user-badge .user-detail {
            color: #fff;
            line-height: 1.2;
        }
        
        .user-info .user-badge .user-detail .name {
            font-size: 14px;
            font-weight: 600;
        }
        
        .user-info .btn-logout {
            background: rgba(255,255,255,0.15);
            color: #fff;
            padding: 6px 16px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-info .btn-logout:hover {
            background: rgba(255,255,255,0.25);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🧺 White Clean</h1>
        <div class="user-info">
            <div class="user-badge">
                <div class="avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?></div>
                <div class="user-detail">
                    <div class="name"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
                </div>
            </div>
            <a href="<?= $basePath ?>logout.php" class="btn-logout" onclick="return confirm('Yakin ingin logout?')">🚪 Keluar</a>
        </div>
    </div>
    <nav class="nav">
        <a href="<?= $basePath ?>index.php" class="<?= $activePage=='dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
        <a href="<?= $basePath ?>process/pesanan.php" class="<?= $activePage=='pesanan' ? 'active' : '' ?>">📋 Pesanan</a>
        <a href="<?= $basePath ?>process/customer.php" class="<?= $activePage=='customer' ? 'active' : '' ?>">👤 Customer</a>
        <a href="<?= $basePath ?>process/kurir.php" class="<?= $activePage=='kurir' ? 'active' : '' ?>">🛵 Kurir</a>
        <a href="<?= $basePath ?>process/layanan.php" class="<?= $activePage=='layanan' ? 'active' : '' ?>">🧺 Layanan</a>
    </nav>