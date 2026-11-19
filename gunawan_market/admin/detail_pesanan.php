<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/csrf.php';

function gm_h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(404);
    exit('Pesanan tidak ditemukan.');
}

$stmt = $conn->prepare("SELECT o.*, u.name AS buyer_name, u.email AS buyer_email
                        FROM orders o
                        INNER JOIN users u ON u.id = o.user_id
                        WHERE o.id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) {
    http_response_code(404);
    exit('Pesanan tidak ditemukan.');
}

$detailStmt = $conn->prepare('SELECT product_name, price, quantity, subtotal FROM order_details WHERE order_id = ? ORDER BY id ASC');
$detailStmt->bind_param('i', $id);
$detailStmt->execute();
$items = $detailStmt->get_result();
$detailStmt->close();

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
$transitions = [
    'menunggu' => ['diproses', 'dibatalkan'],
    'diproses' => ['dikirim', 'dibatalkan'],
    'dikirim' => ['selesai'],
    'selesai' => [],
    'dibatalkan' => [],
];
$nextStatuses = $transitions[$order['status']] ?? [];

$pageTitle = 'Detail Pesanan - Vanguard Outdoor';
require_once __DIR__ . '/layout.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div><h1 class="h3 mb-1">Detail Pesanan</h1><div class="text-secondary">Kode: <?= gm_h($order['order_code']) ?></div></div>
    <a class="btn btn-outline-primary" href="pesanan.php">&larr; Kembali ke Pesanan</a>
</div>

<?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-info"><?= gm_h($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h5 mb-3">Informasi Pembeli</h2>
                <div class="row g-3">
                    <div class="col-md-6"><div class="small text-secondary">Nama pembeli</div><div class="fw-semibold"><?= gm_h($order['buyer_name']) ?></div></div>
                    <div class="col-md-6"><div class="small text-secondary">Email</div><div class="fw-semibold"><?= gm_h($order['buyer_email']) ?></div></div>
                    <div class="col-12"><div class="small text-secondary">Alamat pengiriman</div><div><?= nl2br(gm_h($order['shipping_address'])) ?></div></div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Produk yang Dibeli</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
                        <tbody>
                        <?php if ($items->num_rows > 0): ?>
                            <?php while ($item = $items->fetch_assoc()): ?>
                                <tr>
                                    <td><?= gm_h($item['product_name']) ?></td>
                                    <td>Rp <?= number_format((float)$item['price'], 0, ',', '.') ?></td>
                                    <td><?= (int)$item['quantity'] ?></td>
                                    <td class="price">Rp <?= number_format((float)$item['subtotal'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-secondary">Rincian produk tidak ditemukan.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end"><div class="fs-5 fw-bold">Total: <span class="price">Rp <?= number_format((float)$order['total_price'], 0, ',', '.') ?></span></div></div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h5">Informasi Transaksi</h2>
                <div class="mb-2"><div class="small text-secondary">Tanggal pesanan</div><div><?= gm_h(date('d-m-Y H:i', strtotime($order['created_at']))) ?></div></div>
                <div class="mb-2"><div class="small text-secondary">Kode pesanan</div><div class="fw-semibold"><?= gm_h($order['order_code']) ?></div></div>
                <div><div class="small text-secondary">Status saat ini</div><span class="badge <?= $statusBadges[$order['status']] ?? 'bg-secondary' ?> fs-6"><?= gm_h($statusLabels[$order['status']] ?? $order['status']) ?></span></div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Perbarui Status Pesanan</h2>
                <?php if ($nextStatuses): ?>
                    <form method="post" action="update_status_pesanan.php" onsubmit="return confirm('Yakin ingin memperbarui status pesanan ini?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                        <label class="form-label" for="new_status">Status berikutnya</label>
                        <select class="form-select mb-3" name="new_status" id="new_status" required>
                            <?php foreach ($nextStatuses as $nextStatus): ?>
                                <option value="<?= gm_h($nextStatus) ?>"><?= gm_h($statusLabels[$nextStatus]) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-market" type="submit">Simpan Status</button>
                    </form>
                    <p class="small text-secondary mt-3 mb-0">Alur status: Menunggu → Diproses → Dikirim → Selesai. Pesanan dapat dibatalkan sebelum dikirim.</p>
                    <p class="small text-secondary mt-2 mb-0">Jika pesanan dibatalkan, stok produk yang masih tercatat akan dikembalikan otomatis.</p>
                <?php elseif ($order['status'] === 'dibatalkan'): ?>
                    <div class="alert alert-danger mb-0">Pesanan sudah dibatalkan. Status tidak dapat diubah kembali.</div>
                <?php else: ?>
                    <div class="alert alert-success mb-0">Pesanan telah selesai. Tidak ada perubahan status lanjutan.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/layout_footer.php'; ?>
