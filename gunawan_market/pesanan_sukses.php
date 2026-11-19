<?php
require_once __DIR__ . '/config/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: login.php?next=pesanan_saya.php'); exit;
}
$orderCode = $_SESSION['last_order_code'] ?? '';
if (!$orderCode) { header('Location: pesanan_saya.php'); exit; }
$userId = (int)$_SESSION['user_id'];
$stmt = $conn->prepare('SELECT o.id, o.order_code, o.total_price, o.shipping_address, o.status, o.created_at, u.name, u.email FROM orders o JOIN users u ON u.id=o.user_id WHERE o.order_code=? AND o.user_id=? LIMIT 1');
$stmt->bind_param('si', $orderCode, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) { unset($_SESSION['last_order_code']); header('Location: pesanan_saya.php'); exit; }
$detail = $conn->prepare('SELECT product_name, price, quantity, subtotal FROM order_details WHERE order_id=? ORDER BY id ASC');
$detail->bind_param('i', $order['id']);
$detail->execute();
$items = $detail->get_result()->fetch_all(MYSQLI_ASSOC);
$detail->close();
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$pageTitle = 'Pesanan Berhasil - Vanguard Outdoor';
require_once __DIR__ . '/includes/header.php';
?>
<main class="vo-transaction-page vo-success-page"><div class="card shadow-sm mx-auto vo-transaction-card vo-success-card" style="max-width:820px"><div class="card-body p-4 p-md-5">
  <div class="text-center mb-4"><div class="vo-success-icon">✓</div><span class="vo-page-kicker">VANGUARD OUTDOOR</span><h1 class="h3">Pesanan Berhasil Dibuat!</h1><p class="text-secondary">Terima kasih telah berbelanja di Vanguard Outdoor.</p></div>
  <div class="alert alert-success vo-order-code"><strong>Kode pesanan:</strong> <?= h($order['order_code']) ?><br><strong>Status:</strong> <?= h(ucfirst($order['status'])) ?></div>
  <h2 class="h5">Data Penerima</h2><p class="mb-1"><?= h($order['name']) ?></p><p class="mb-1"><?= h($order['email']) ?></p><p><?= nl2br(h($order['shipping_address'])) ?></p>
  <h2 class="h5 mt-4">Rincian Pesanan</h2><div class="table-responsive"><table class="table"><thead><tr><th>Produk</th><th>Jumlah</th><th>Subtotal</th></tr></thead><tbody>
  <?php foreach ($items as $item): ?><tr><td><?= h($item['product_name']) ?><div class="small text-secondary">Rp <?= number_format((float)$item['price'],0,',','.') ?></div></td><td><?= (int)$item['quantity'] ?></td><td>Rp <?= number_format((float)$item['subtotal'],0,',','.') ?></td></tr><?php endforeach; ?>
  </tbody></table></div><div class="text-end"><strong>Total: <span class="price">Rp <?= number_format((float)$order['total_price'],0,',','.') ?></span></strong></div>
  <div class="text-center mt-4"><a class="btn btn-market" href="pesanan_detail.php?id=<?= (int)$order['id'] ?>">Lihat Status Pesanan</a> <a class="btn btn-outline-secondary" href="index.php">Kembali Belanja</a></div>
</div></div></main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
