<?php
require_once __DIR__ . '/../includes/csrf.php';
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'dashboard.php');
function admin_nav_class($files, $currentPage) {
    return in_array($currentPage, (array)$files, true) ? 'admin-nav-link active' : 'admin-nav-link';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Vanguard Outdoor', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css?v=vanguard-mountain-20261118b" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="../assets/images/vanguard-mountain.svg">
</head>
<body class="admin-page">
<div class="admin-topbar">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <a class="admin-top-brand" href="dashboard.php"><img src="../assets/images/vanguard-mountain.svg" class="brand-logo brand-logo-nav" alt="Logo Vanguard Outdoor"><span>VANGUARD <small>OUTDOOR</small></span></a>
    <div class="d-flex align-items-center gap-2">
      <span class="admin-top-user d-none d-sm-inline">Hai, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></span>
      <a class="btn btn-sm btn-outline-light" href="../index.php">Lihat Toko</a>
      <form method="post" action="../logout.php" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-light" type="submit">Keluar</button></form>
    </div>
  </div>
</div>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-sidebar-heading">MENU UTAMA</div>
    <a class="<?= admin_nav_class('dashboard.php', $currentPage) ?>" href="dashboard.php"><span class="admin-nav-icon">▦</span>Ringkasan</a>
    <div class="admin-sidebar-heading mt-4">TOKO</div>
    <a class="<?= admin_nav_class(['produk.php','tambah_produk.php','edit_produk.php'], $currentPage) ?>" href="produk.php"><span class="admin-nav-icon">▣</span>Kelola Produk</a>
    <div class="admin-sidebar-heading mt-4">PENJUALAN</div>
    <a class="<?= admin_nav_class(['pesanan.php','detail_pesanan.php'], $currentPage) ?>" href="pesanan.php"><span class="admin-nav-icon">▤</span>Pesanan Masuk</a>
    <a class="<?= admin_nav_class(['pengguna.php','detail_pengguna.php'], $currentPage) ?>" href="pengguna.php"><span class="admin-nav-icon">♙</span>Data Pembeli</a>
    <div class="admin-sidebar-note mt-auto"><span class="status-dot"></span>Panel administrasi<br><small>Vanguard Outdoor</small></div>
  </aside>
  <main class="admin-main">
    <div class="admin-content">
