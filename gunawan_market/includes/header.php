<?php
require_once __DIR__ . '/csrf.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$cartCount = array_sum($_SESSION['cart'] ?? []);
$isBuyer = !empty($_SESSION['user_id']) && (($_SESSION['role'] ?? '') === 'pembeli');
$isAdmin = !empty($_SESSION['user_id']) && (($_SESSION['role'] ?? '') === 'admin');
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'Vanguard Outdoor', ENT_QUOTES, 'UTF-8') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css?v=vanguard-detail-modern-20261118d" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="assets/images/vanguard-mountain.svg"></head><body>
<nav class="navbar navbar-expand-lg navbar-dark market-navbar"><div class="container">
<a class="navbar-brand d-flex align-items-center gap-2" href="index.php"><img src="assets/images/vanguard-mountain.svg" class="brand-logo brand-logo-nav" alt="Logo Vanguard Outdoor"><span><strong>VANGUARD</strong><small class="d-block brand-nav-sub">OUTDOOR EQUIPMENT</small></span></a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="nav"><ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
<li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
<li class="nav-item"><a class="nav-link" href="index.php#produk">Produk</a></li>
<li class="nav-item"><a class="nav-link" href="keranjang.php">Keranjang (<?= (int)$cartCount ?>)</a></li>
<?php if ($isBuyer): ?>
<li class="nav-item"><a class="nav-link" href="pesanan_saya.php">Pesanan Saya</a></li>
<li class="nav-item"><span class="navbar-text text-white-50 px-lg-2">Hai, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Pembeli', ENT_QUOTES, 'UTF-8') ?></span></li>
<li class="nav-item"><form method="post" action="logout.php" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-light my-1" type="submit">Keluar</button></form></li>
<?php elseif ($isAdmin): ?>
<li class="nav-item"><a class="nav-link" href="admin/dashboard.php">Dashboard Admin</a></li>
<li class="nav-item"><span class="navbar-text text-white-50 px-lg-2">Admin: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></span></li>
<li class="nav-item"><form method="post" action="logout.php" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-light my-1" type="submit">Keluar</button></form></li>
<?php else: ?>
<li class="nav-item"><a class="nav-link" href="login.php">Masuk</a></li>
<li class="nav-item"><a class="btn btn-sm btn-light ms-lg-2 my-1" href="register.php">Daftar</a></li>
<?php endif; ?>
</ul></div></div></nav><main class="container py-4">
