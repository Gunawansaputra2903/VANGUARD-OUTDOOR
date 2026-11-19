<?php
require_once __DIR__ . '/config/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: login.php?next=pesanan_saya.php'); exit;
}
$userId = (int)$_SESSION['user_id'];
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId || $orderId < 1) { http_response_code(404); exit('Pesanan tidak ditemukan.'); }
$stmt = $conn->prepare('SELECT id, order_code, total_price, shipping_address, status, created_at FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->bind_param('ii', $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) { http_response_code(404); exit('Pesanan tidak ditemukan.'); }
$itemsStmt = $conn->prepare('SELECT product_name, price, quantity, subtotal FROM order_details WHERE order_id = ? ORDER BY id ASC');
$itemsStmt->bind_param('i', $orderId);
$itemsStmt->execute();
$items = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$itemsStmt->close();
function od_h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
$labels = ['menunggu'=>'Menunggu konfirmasi','diproses'=>'Sedang diproses','dikirim'=>'Pesanan telah dikirim','selesai'=>'Pesanan selesai','dibatalkan'=>'Pesanan dibatalkan'];
$badges = ['menunggu'=>'bg-warning text-dark','diproses'=>'bg-info text-dark','dikirim'=>'bg-primary','selesai'=>'bg-success','dibatalkan'=>'bg-danger'];
$steps = ['menunggu'=>'Menunggu','diproses'=>'Diproses','dikirim'=>'Dikirim','selesai'=>'Selesai'];
$stepOrder = array_keys($steps);
$currentIndex = array_search($order['status'], $stepOrder, true);
$pageTitle = 'Detail Pesanan - Vanguard Outdoor';
require_once __DIR__ . '/includes/header.php';
?>
<main class="vo-transaction-page"><div class="vo-page-heading"><div><span class="vo-page-kicker">RIWAYAT TRANSAKSI</span><h1>Detail Pesanan</h1><p>Informasi lengkap pesananmu.</p></div><a class="btn btn-outline-primary" href="pesanan_saya.php">← Kembali</a></div><div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 d-none"><div><h1 class="h3 mb-1">Detail Pesanan</h1><p class="text-secondary mb-0"><?= od_h($order['order_code']) ?></p></div><a class="btn btn-outline-primary" href="pesanan_saya.php">&larr; Kembali ke Pesanan Saya</a></div>
<div class="card shadow-sm mb-3 vo-transaction-card"><div class="card-body p-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3"><h2 class="h5 mb-0">Status Pesanan</h2><span class="badge fs-6 <?= $badges[$order['status']] ?? 'bg-secondary' ?>"><?= od_h($labels[$order['status']] ?? ucfirst($order['status'])) ?></span></div>
    <?php if ($order['status'] === 'dibatalkan'): ?>
        <div class="alert alert-danger mb-0">Pesanan ini telah dibatalkan oleh admin. Silakan hubungi toko melalui kontak yang tersedia jika membutuhkan informasi lebih lanjut.</div>
    <?php else: ?>
        <div class="row g-2">
            <?php foreach ($steps as $key => $label): $idx = array_search($key, $stepOrder, true); $done = $currentIndex !== false && $currentIndex >= $idx; ?>
                <div class="col-6 col-md-3"><div class="border rounded-3 p-3 h-100 <?= $done ? 'border-success' : '' ?>"><div class="small text-secondary">Tahap <?= $idx + 1 ?></div><div class="fw-semibold <?= $done ? 'text-success' : '' ?>"><?= od_h($label) ?></div><div class="small"><?= $done ? 'Terlewati / aktif' : 'Belum' ?></div></div></div>
            <?php endforeach; ?>
        </div>
        <?php if ($order['status'] === 'dikirim'): ?><p class="small text-secondary mt-3 mb-0">Status menunjukkan pesanan telah dikirim oleh admin. Informasi nomor resi belum tersedia pada sistem ini.</p><?php endif; ?>
    <?php endif; ?>
</div></div>
<div class="row g-3">
    <div class="col-lg-7"><div class="card shadow-sm h-100 vo-transaction-card"><div class="card-body"><h2 class="h5 mb-3">Produk yang Dipesan</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr></thead><tbody>
        <?php foreach ($items as $item): ?><tr><td><?= od_h($item['product_name']) ?></td><td>Rp <?= number_format((float)$item['price'], 0, ',', '.') ?></td><td><?= (int)$item['quantity'] ?></td><td>Rp <?= number_format((float)$item['subtotal'], 0, ',', '.') ?></td></tr><?php endforeach; ?>
    </tbody></table></div><div class="text-end fw-bold">Total: <span class="price">Rp <?= number_format((float)$order['total_price'], 0, ',', '.') ?></span></div></div></div></div>
    <div class="col-lg-5"><div class="card shadow-sm h-100 vo-transaction-card"><div class="card-body"><h2 class="h5 mb-3">Informasi Pengiriman</h2><div class="small text-secondary">Tanggal pesanan</div><p><?= od_h(date('d-m-Y H:i', strtotime($order['created_at']))) ?></p><div class="small text-secondary">Alamat pengiriman</div><p class="mb-0"><?= nl2br(od_h($order['shipping_address'])) ?></p></div></div></div>
</div>
</main><?php require_once __DIR__ . '/includes/footer.php'; ?>
