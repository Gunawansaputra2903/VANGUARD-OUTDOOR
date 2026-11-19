<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/admin_auth.php';

function gm_h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$allowedStatuses = ['menunggu', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
$statusFilter = $_GET['status'] ?? '';
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}
$search = trim($_GET['q'] ?? '');
if (mb_strlen($search) > 100) {
    $search = mb_substr($search, 0, 100);
}

$sql = "SELECT o.id, o.order_code, o.total_price, o.status, o.created_at,
               u.name AS buyer_name, u.email AS buyer_email
        FROM orders o
        INNER JOIN users u ON u.id = o.user_id
        WHERE 1=1";
$types = '';
$params = [];
if ($statusFilter !== '') {
    $sql .= ' AND o.status = ?';
    $types .= 's';
    $params[] = $statusFilter;
}
if ($search !== '') {
    $sql .= ' AND (o.order_code LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
    $like = '%' . $search . '%';
    $types .= 'sss';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$sql .= ' ORDER BY o.created_at DESC, o.id DESC LIMIT 200';
$stmt = $conn->prepare($sql);
if ($params) {
    $bindArgs = [&$types];
    foreach ($params as $key => $value) {
        $bindArgs[] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $bindArgs);
}
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();

$statusCounts = array_fill_keys($allowedStatuses, 0);
$countResult = $conn->query('SELECT status, COUNT(*) AS total FROM orders GROUP BY status');
while ($countRow = $countResult->fetch_assoc()) {
    if (isset($statusCounts[$countRow['status']])) {
        $statusCounts[$countRow['status']] = (int)$countRow['total'];
    }
}
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

$pageTitle = 'Pesanan Masuk - Vanguard Outdoor';
require_once __DIR__ . '/layout.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">Pesanan Masuk</h1>
        <p class="text-secondary mb-0">Lihat pembeli dan transaksi yang masuk ke Vanguard Outdoor.</p>
    </div>
    <span class="badge text-bg-dark fs-6">Total: <?= array_sum($statusCounts) ?> pesanan</span>
</div>

<?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-info"><?= gm_h($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<div class="row g-2 mb-3">
    <?php foreach ($statusLabels as $key => $label): ?>
        <div class="col-6 col-md">
            <a class="card h-100 text-decoration-none" href="pesanan.php?status=<?= rawurlencode($key) ?>">
                <div class="card-body py-3">
                    <div class="small text-secondary"><?= gm_h($label) ?></div>
                    <div class="h4 mb-0"><?= (int)$statusCounts[$key] ?></div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label" for="q">Cari pesanan atau pembeli</label>
                <input class="form-control" id="q" name="q" value="<?= gm_h($search) ?>" placeholder="Kode pesanan, nama, atau email">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="status">Filter status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua status</option>
                    <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?= gm_h($key) ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= gm_h($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-market" type="submit">Cari</button></div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th>Kode Pesanan</th><th>Pembeli</th><th>Tanggal</th><th>Total Belanja</th><th>Status</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php if ($orders->num_rows > 0): ?>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-semibold"><?= gm_h($order['order_code']) ?></td>
                        <td><?= gm_h($order['buyer_name']) ?><div class="small text-secondary"><?= gm_h($order['buyer_email']) ?></div></td>
                        <td><?= gm_h(date('d-m-Y H:i', strtotime($order['created_at']))) ?></td>
                        <td class="price">Rp <?= number_format((float)$order['total_price'], 0, ',', '.') ?></td>
                        <td><span class="badge <?= $statusBadges[$order['status']] ?? 'bg-secondary' ?>"><?= gm_h($statusLabels[$order['status']] ?? $order['status']) ?></span></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="detail_pesanan.php?id=<?= (int)$order['id'] ?>">Detail</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada pesanan yang sesuai dengan pencarian.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<p class="small text-secondary mt-2 mb-0">Daftar menampilkan maksimal 200 pesanan terbaru.</p>
<?php require_once __DIR__ . '/layout_footer.php'; ?>
