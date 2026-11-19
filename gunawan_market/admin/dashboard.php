<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/admin_auth.php';

function admin_h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$products = (int)$conn->query('SELECT COUNT(*) AS total FROM products')->fetch_assoc()['total'];
$categories = (int)$conn->query('SELECT COUNT(*) AS total FROM categories')->fetch_assoc()['total'];
$orders = (int)$conn->query('SELECT COUNT(*) AS total FROM orders')->fetch_assoc()['total'];
$pendingOrders = (int)$conn->query("SELECT COUNT(*) AS total FROM orders WHERE status = 'menunggu'")->fetch_assoc()['total'];
$buyers = (int)$conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'pembeli'")->fetch_assoc()['total'];

$recentOrders = $conn->query(
    "SELECT o.id, o.order_code, o.total_price, o.status, o.created_at,
            u.name AS buyer_name
     FROM orders o
     INNER JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC, o.id DESC
     LIMIT 5"
);

$statusLabels = [
    'menunggu' => 'Menunggu',
    'diproses' => 'Diproses',
    'dikirim' => 'Dikirim',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan',
];
$statusClasses = [
    'menunggu' => 'vo-admin-status-waiting',
    'diproses' => 'vo-admin-status-processing',
    'dikirim' => 'vo-admin-status-shipped',
    'selesai' => 'vo-admin-status-done',
    'dibatalkan' => 'vo-admin-status-cancelled',
];

$pageTitle = 'Dashboard Admin - Vanguard Outdoor';
require_once __DIR__ . '/layout.php';
?>
<section class="vo-admin-welcome mb-4">
    <div class="vo-admin-welcome-copy">
        <span class="vo-admin-kicker">VANGUARD OUTDOOR · ADMIN PANEL</span>
        <h1>Dashboard</h1>
        <p>Selamat datang kembali, <strong><?= admin_h($_SESSION['user_name'] ?? 'Admin') ?></strong>. Pantau aktivitas toko dari satu tempat.</p>
    </div>
    <div class="vo-admin-welcome-mark" aria-hidden="true">
        <img src="../assets/images/vanguard-mountain.svg" alt="">
    </div>
</section>

<?php if ($pendingOrders > 0): ?>
    <a class="vo-admin-alert mb-4" href="pesanan.php?status=menunggu">
        <span class="vo-admin-alert-icon">!</span>
        <span><strong><?= $pendingOrders ?> pesanan menunggu</strong><small>Ada pesanan yang perlu diperiksa dan diproses.</small></span>
        <span class="vo-admin-alert-action">Periksa pesanan <b>→</b></span>
    </a>
<?php else: ?>
    <div class="vo-admin-calm mb-4"><span>✓</span> Tidak ada pesanan yang menunggu untuk saat ini.</div>
<?php endif; ?>

<div class="vo-admin-section-heading">
    <div><span class="vo-admin-section-kicker">RINGKASAN TOKO</span><h2>Statistik utama</h2></div>
    <span class="vo-admin-updated">Data diperbarui saat halaman dibuka</span>
</div>

<div class="row g-3 vo-admin-stat-grid mb-4">
    <div class="col-6 col-xl">
        <article class="vo-admin-stat-card">
            <div class="vo-admin-stat-top"><span class="vo-admin-stat-icon vo-icon-orange">▣</span><span class="vo-admin-stat-label">KATALOG</span></div>
            <div class="vo-admin-stat-number"><?= $products ?></div>
            <div class="vo-admin-stat-name">Jumlah Produk</div>
            <a href="produk.php">Kelola produk <b>→</b></a>
        </article>
    </div>
    <div class="col-6 col-xl">
        <article class="vo-admin-stat-card">
            <div class="vo-admin-stat-top"><span class="vo-admin-stat-icon vo-icon-cream">▤</span><span class="vo-admin-stat-label">KATALOG</span></div>
            <div class="vo-admin-stat-number"><?= $categories ?></div>
            <div class="vo-admin-stat-name">Kategori Produk</div>
            <span class="vo-admin-stat-muted">Kelompok produk toko</span>
        </article>
    </div>
    <div class="col-6 col-xl">
        <article class="vo-admin-stat-card">
            <div class="vo-admin-stat-top"><span class="vo-admin-stat-icon vo-icon-brown">▧</span><span class="vo-admin-stat-label">PENJUALAN</span></div>
            <div class="vo-admin-stat-number"><?= $orders ?></div>
            <div class="vo-admin-stat-name">Total Pesanan</div>
            <a href="pesanan.php">Lihat pesanan <b>→</b></a>
        </article>
    </div>
    <div class="col-6 col-xl">
        <article class="vo-admin-stat-card vo-admin-stat-highlight">
            <div class="vo-admin-stat-top"><span class="vo-admin-stat-icon vo-icon-red">◷</span><span class="vo-admin-stat-label">PERLU TINDAKAN</span></div>
            <div class="vo-admin-stat-number"><?= $pendingOrders ?></div>
            <div class="vo-admin-stat-name">Pesanan Menunggu</div>
            <a href="pesanan.php?status=menunggu">Tinjau sekarang <b>→</b></a>
        </article>
    </div>
    <div class="col-6 col-xl">
        <article class="vo-admin-stat-card">
            <div class="vo-admin-stat-top"><span class="vo-admin-stat-icon vo-icon-green">♙</span><span class="vo-admin-stat-label">PELANGGAN</span></div>
            <div class="vo-admin-stat-number"><?= $buyers ?></div>
            <div class="vo-admin-stat-name">Jumlah Pembeli</div>
            <a href="pengguna.php">Lihat pembeli <b>→</b></a>
        </article>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <section class="vo-admin-panel h-100">
            <div class="vo-admin-panel-heading">
                <div><span class="vo-admin-section-kicker">AKTIVITAS TERBARU</span><h2>Pesanan masuk</h2></div>
                <a href="pesanan.php">Lihat semua <b>→</b></a>
            </div>
            <div class="table-responsive">
                <table class="table vo-admin-recent-table align-middle mb-0">
                    <thead><tr><th>Kode Pesanan</th><th>Pembeli</th><th>Tanggal</th><th>Total</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                        <?php while ($order = $recentOrders->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= admin_h($order['order_code']) ?></strong></td>
                                <td><?= admin_h($order['buyer_name']) ?></td>
                                <td><?= admin_h(date('d/m/Y', strtotime($order['created_at']))) ?><small><?= admin_h(date('H:i', strtotime($order['created_at']))) ?></small></td>
                                <td class="price">Rp <?= number_format((float)$order['total_price'], 0, ',', '.') ?></td>
                                <td><span class="vo-admin-status <?= $statusClasses[$order['status']] ?? 'vo-admin-status-other' ?>"><?= admin_h($statusLabels[$order['status']] ?? $order['status']) ?></span></td>
                                <td><a class="vo-admin-detail-link" href="detail_pesanan.php?id=<?= (int)$order['id'] ?>" aria-label="Lihat detail pesanan <?= admin_h($order['order_code']) ?>">›</a></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6"><div class="vo-admin-empty"><span>▤</span><strong>Belum ada pesanan</strong><small>Pesanan baru dari pelanggan akan muncul di sini.</small></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="vo-admin-panel vo-admin-quick-panel h-100">
            <div class="vo-admin-panel-heading"><div><span class="vo-admin-section-kicker">AKSES CEPAT</span><h2>Kelola toko</h2></div></div>
            <a class="vo-admin-quick-link" href="tambah_produk.php"><span class="vo-quick-icon">＋</span><span><strong>Tambah Produk</strong><small>Masukkan produk baru ke katalog</small></span><b>→</b></a>
            <a class="vo-admin-quick-link" href="produk.php"><span class="vo-quick-icon">▣</span><span><strong>Kelola Produk</strong><small>Perbarui informasi dan stok produk</small></span><b>→</b></a>
            <a class="vo-admin-quick-link" href="pesanan.php"><span class="vo-quick-icon">▤</span><span><strong>Kelola Pesanan</strong><small>Periksa dan perbarui status pesanan</small></span><b>→</b></a>
            <a class="vo-admin-quick-link" href="pengguna.php"><span class="vo-quick-icon">♙</span><span><strong>Data Pembeli</strong><small>Lihat akun pelanggan terdaftar</small></span><b>→</b></a>
            <div class="vo-admin-panel-note"><img src="../assets/images/vanguard-mountain.svg" alt=""><span><strong>Vanguard Outdoor</strong><small>Outdoor gear for everyday adventure.</small></span></div>
        </section>
    </div>
</div>
<?php require_once __DIR__ . '/layout_footer.php'; ?>
