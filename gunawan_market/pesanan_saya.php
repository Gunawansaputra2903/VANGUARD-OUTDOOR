<?php
require_once __DIR__ . '/config/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: login.php?next=pesanan_saya.php'); exit;
}
$userId = (int)$_SESSION['user_id'];
$stmt = $conn->prepare('SELECT id, order_code, total_price, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 100');
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result();
function myo_h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
$labels = ['menunggu'=>'Menunggu','diproses'=>'Diproses','dikirim'=>'Dikirim','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'];
$badges = ['menunggu'=>'bg-warning text-dark','diproses'=>'bg-info text-dark','dikirim'=>'bg-primary','selesai'=>'bg-success','dibatalkan'=>'bg-danger'];
$pageTitle = 'Pesanan Saya - Vanguard Outdoor';
require_once __DIR__ . '/includes/header.php';
?>
<main class="vo-transaction-page"><div class="vo-page-heading"><div><span class="vo-page-kicker">AKUN PEMBELI</span><h1>Pesanan Saya</h1><p>Pantau perkembangan pesananmu di Vanguard Outdoor.</p></div><a class="btn btn-outline-primary" href="index.php">Belanja Lagi</a></div><div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 d-none">
    <div><h1 class="h3 mb-1">Pesanan Saya</h1><p class="text-secondary mb-0">Pantau proses pesananmu di Vanguard Outdoor.</p></div>
    <a class="btn btn-outline-primary" href="index.php">Belanja Lagi</a>
</div>
<div class="card shadow-sm vo-transaction-card"><div class="card-body table-responsive p-3 p-md-4">
    <table class="table table-hover align-middle mb-0 vo-modern-table"><thead><tr><th>Kode Pesanan</th><th>Tanggal</th><th>Total</th><th>Status Pesanan</th><th>Aksi</th></tr></thead><tbody>
    <?php if ($orders->num_rows > 0): ?>
        <?php while ($order = $orders->fetch_assoc()): ?>
            <tr>
                <td class="fw-semibold"><?= myo_h($order['order_code']) ?></td>
                <td><?= myo_h(date('d-m-Y H:i', strtotime($order['created_at']))) ?></td>
                <td class="price">Rp <?= number_format((float)$order['total_price'], 0, ',', '.') ?></td>
                <td><span class="badge <?= $badges[$order['status']] ?? 'bg-secondary' ?>"><?= myo_h($labels[$order['status']] ?? $order['status']) ?></span></td>
                <td><a class="btn btn-sm btn-outline-primary" href="pesanan_detail.php?id=<?= (int)$order['id'] ?>">Lihat Detail</a></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5" class="text-center text-secondary py-4">Kamu belum memiliki pesanan.</td></tr>
    <?php endif; ?>
    </tbody></table>
</div></div>
<p class="small text-secondary mt-2">Menampilkan maksimal 100 pesanan terbaru.</p>
</main><?php $stmt->close(); require_once __DIR__ . '/includes/footer.php'; ?>
