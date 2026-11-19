<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/admin_auth.php';

function gm_h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$search = trim($_GET['q'] ?? '');
if (mb_strlen($search) > 100) {
    $search = mb_substr($search, 0, 100);
}

$sql = "SELECT u.id, u.name, u.email, u.created_at,
               COUNT(o.id) AS total_orders,
               COALESCE(SUM(CASE WHEN o.status <> 'dibatalkan' THEN o.total_price ELSE 0 END), 0) AS total_spent,
               MAX(o.created_at) AS last_order_at
        FROM users u
        LEFT JOIN orders o ON o.user_id = u.id
        WHERE u.role = 'pembeli'";
$params = [];
if ($search !== '') {
    $sql .= ' AND (u.name LIKE ? OR u.email LIKE ?)';
    $like = '%' . $search . '%';
    $params = [$like, $like];
}
$sql .= ' GROUP BY u.id, u.name, u.email, u.created_at ORDER BY last_order_at DESC, u.created_at DESC LIMIT 200';
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param('ss', $params[0], $params[1]);
}
$stmt->execute();
$users = $stmt->get_result();

$buyerCount = (int)$conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'pembeli'")->fetch_assoc()['total'];
$orderCount = (int)$conn->query('SELECT COUNT(*) AS total FROM orders')->fetch_assoc()['total'];
$pageTitle = 'Manajemen Pengguna - Vanguard Outdoor';
require_once __DIR__ . '/layout.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">Manajemen Pengguna</h1>
        <p class="text-secondary mb-0">Daftar akun pembeli yang mendaftar dan melakukan pembelian.</p>
    </div>
    <span class="badge text-bg-dark fs-6">Total pembeli: <?= $buyerCount ?></span>
</div>

<div class="alert alert-info">
    Pembeli dapat membuat akun, login, melihat riwayat pesanan, dan memantau status pesanan dari halaman Pesanan Saya.
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm h-100"><div class="card-body">
            <div class="small text-secondary">Jumlah akun pembeli</div>
            <div class="display-6"><?= $buyerCount ?></div>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100"><div class="card-body">
            <div class="small text-secondary">Jumlah seluruh pesanan</div>
            <div class="display-6"><?= $orderCount ?></div>
        </div></div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-9">
                <label class="form-label" for="q">Cari nama atau email pembeli</label>
                <input class="form-control" id="q" name="q" value="<?= gm_h($search) ?>" placeholder="Masukkan nama atau email">
            </div>
            <div class="col-md-3 d-grid"><button class="btn btn-market" type="submit">Cari Pengguna</button></div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th>Nama Pembeli</th><th>Email</th><th>Bergabung</th><th>Jumlah Pesanan</th><th>Total Belanja</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php if ($users->num_rows > 0): ?>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-semibold"><?= gm_h($user['name']) ?></td>
                        <td><?= gm_h($user['email']) ?></td>
                        <td><?= gm_h(date('d-m-Y', strtotime($user['created_at']))) ?></td>
                        <td><?= (int)$user['total_orders'] ?></td>
                        <td class="price">Rp <?= number_format((float)$user['total_spent'], 0, ',', '.') ?></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="detail_pengguna.php?id=<?= (int)$user['id'] ?>">Detail</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada data pembeli. Data akan muncul setelah checkout berhasil.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<p class="small text-secondary mt-2 mb-0">Daftar menampilkan maksimal 200 pembeli terbaru. Total belanja tidak menghitung pesanan yang dibatalkan.</p>
<?php
$stmt->close();
require_once __DIR__ . '/layout_footer.php';
?>
