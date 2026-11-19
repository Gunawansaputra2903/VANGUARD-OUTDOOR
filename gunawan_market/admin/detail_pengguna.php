<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/admin_auth.php';

function gm_h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(404);
    exit('Pengguna tidak ditemukan.');
}

$userStmt = $conn->prepare("SELECT u.id, u.name, u.email, u.created_at,
                                   COUNT(o.id) AS total_orders,
                                   COALESCE(SUM(CASE WHEN o.status <> 'dibatalkan' THEN o.total_price ELSE 0 END), 0) AS total_spent
                            FROM users u
                            LEFT JOIN orders o ON o.user_id = u.id
                            WHERE u.id = ? AND u.role = 'pembeli'
                            GROUP BY u.id, u.name, u.email, u.created_at
                            LIMIT 1");
$userStmt->bind_param('i', $id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();
if (!$user) {
    http_response_code(404);
    exit('Data pembeli tidak ditemukan.');
}

$orderStmt = $conn->prepare('SELECT id, order_code, total_price, shipping_address, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 100');
$orderStmt->bind_param('i', $id);
$orderStmt->execute();
$orders = $orderStmt->get_result();
$statusLabels = [
    'menunggu' => 'Menunggu',
    'diproses' => 'Diproses',
    'dikirim' => 'Dikirim',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan',
];
$statusBadges = [
    'menunggu' => 'bg-warning text-dark',
    'diproses' => 'bg-info text-dark',
    'dikirim' => 'bg-primary',
    'selesai' => 'bg-success',
    'dibatalkan' => 'bg-danger',
];
$pageTitle = 'Detail Pembeli - Vanguard Outdoor';
require_once __DIR__ . '/layout.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div><h1 class="h3 mb-1">Detail Pembeli</h1><p class="text-secondary mb-0">Informasi akun dan riwayat transaksi pembeli.</p></div>
    <a class="btn btn-outline-primary" href="pengguna.php">&larr; Kembali ke Pengguna</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100"><div class="card-body">
            <h2 class="h5 mb-3">Informasi Pengguna</h2>
            <div class="mb-3"><div class="small text-secondary">Nama pembeli</div><div class="fw-semibold"><?= gm_h($user['name']) ?></div></div>
            <div class="mb-3"><div class="small text-secondary">Email</div><div><?= gm_h($user['email']) ?></div></div>
            <div><div class="small text-secondary">Tanggal tercatat</div><div><?= gm_h(date('d-m-Y H:i', strtotime($user['created_at']))) ?></div></div>
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm h-100"><div class="card-body">
            <h2 class="h5 mb-3">Ringkasan Transaksi</h2>
            <div class="mb-2"><div class="small text-secondary">Jumlah pesanan</div><div class="h4 mb-0"><?= (int)$user['total_orders'] ?></div></div>
            <div><div class="small text-secondary">Total belanja (tidak termasuk pesanan dibatalkan)</div><div class="h4 mb-0 price">Rp <?= number_format((float)$user['total_spent'], 0, ',', '.') ?></div></div>
        </div></div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">Riwayat Pesanan</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Kode Pesanan</th><th>Tanggal</th><th>Alamat Pengiriman</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php if ($orders->num_rows > 0): ?>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-semibold"><?= gm_h($order['order_code']) ?></td>
                            <td><?= gm_h(date('d-m-Y H:i', strtotime($order['created_at']))) ?></td>
                            <td style="min-width:220px"><?= nl2br(gm_h($order['shipping_address'])) ?></td>
                            <td class="price">Rp <?= number_format((float)$order['total_price'], 0, ',', '.') ?></td>
                            <td><span class="badge <?= $statusBadges[$order['status']] ?? 'bg-secondary' ?>"><?= gm_h($statusLabels[$order['status']] ?? $order['status']) ?></span></td>
                            <td><a class="btn btn-sm btn-outline-primary" href="detail_pesanan.php?id=<?= (int)$order['id'] ?>">Lihat Pesanan</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-secondary py-4">Pembeli ini belum memiliki pesanan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <p class="small text-secondary mb-0">Menampilkan maksimal 100 pesanan terbaru dari pembeli ini.</p>
    </div>
</div>
<?php
$orderStmt->close();
require_once __DIR__ . '/layout_footer.php';
?>
