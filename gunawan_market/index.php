<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/product_image.php';

$pageTitle = 'Beranda - Vanguard Outdoor';
require_once __DIR__ . '/includes/header.php';

$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $conn->prepare(
        "SELECT id, product_name, description, price, stock, image
         FROM products
         WHERE product_name LIKE ?
         ORDER BY id DESC"
    );
    $stmt->bind_param('s', $like);
} else {
    $stmt = $conn->prepare(
        "SELECT id, product_name, description, price, stock, image
         FROM products
         ORDER BY id DESC"
    );
}

$stmt->execute();
$result = $stmt->get_result();

$categories = [
    [
        'name' => 'Tas Outdoor',
        'image' => 'assets/images/products/tas-outdoor.jpg',
        'alt' => 'Tas untuk kegiatan outdoor'
    ],
    [
        'name' => 'Sepatu Hiking',
        'image' => 'assets/images/products/sepatu-hiking.jpg',
        'alt' => 'Sepatu untuk aktivitas luar ruangan'
    ],
    [
        'name' => 'Peralatan Camping',
        'image' => 'assets/images/products/tenda-camping.jpg',
        'alt' => 'Peralatan camping'
    ],
    [
        'name' => 'Pakaian Outdoor',
        'image' => 'assets/images/products/jaket-outdoor.jpg',
        'alt' => 'Pakaian untuk kegiatan outdoor'
    ],
];
?>
<section class="vo-hero mb-5">
    <div class="vo-hero-shade"></div>
    <div class="vo-hero-content">
        <span class="vo-eyebrow">VANGUARD OUTDOOR</span>
        <h1>Explore Beyond Limits.</h1>
        <p>Perlengkapan outdoor untuk menemani setiap petualanganmu.</p>
        <a href="#produk" class="btn btn-market btn-lg vo-hero-button">Jelajahi Produk <span aria-hidden="true">→</span></a>
    </div>
</section>

<section class="vo-category-section mb-5" aria-labelledby="kategori-title">
    <div class="vo-section-heading">
        <div>
            <span class="vo-section-kicker">PILIH SESUAI KEBUTUHAN</span>
            <h2 id="kategori-title">Kategori Produk</h2>
        </div>
        <a href="#produk" class="vo-text-link">Lihat produk <span aria-hidden="true">→</span></a>
    </div>
    <div class="row g-3">
        <?php foreach ($categories as $category): ?>
            <div class="col-6 col-lg-3">
                <a class="vo-category-card" href="#produk">
                    <img src="<?= htmlspecialchars($category['image'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($category['alt'], ENT_QUOTES, 'UTF-8') ?>"
                         loading="lazy">
                    <span><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section id="produk" class="vo-products-section">
    <div class="vo-section-heading align-items-end mb-3">
        <div>
            <span class="vo-section-kicker">TEMUKAN PERLENGKAPAN FAVORITMU</span>
            <h2><?= $q !== '' ? 'Hasil Pencarian' : 'Produk Unggulan' ?></h2>
            <p class="text-secondary mb-0">
                <?= $q !== '' ? 'Produk yang sesuai dengan pencarianmu.' : 'Pilihan perlengkapan untuk menemani aktivitas luar ruangan.' ?>
            </p>
        </div>
        <form method="get" action="index.php" class="vo-search-form">
            <input class="form-control" type="search" name="q"
                   placeholder="Cari produk..." value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
                   aria-label="Cari produk">
            <button class="btn btn-market" type="submit">Cari</button>
        </form>
    </div>

    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($p = $result->fetch_assoc()): ?>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="card product-card vo-product-card h-100">
                        <?php $productImage = vanguard_product_image($p, __DIR__); ?>
                        <img src="<?= htmlspecialchars($productImage, ENT_QUOTES, 'UTF-8') ?>"
                             class="card-img-top vo-product-image"
                             alt="<?= htmlspecialchars($p['product_name'], ENT_QUOTES, 'UTF-8') ?>"
                             loading="lazy">
                        <div class="card-body d-flex flex-column">
                            <h3 class="h6 vo-product-name"><?= htmlspecialchars($p['product_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="price mb-2">Rp <?= number_format((float) $p['price'], 0, ',', '.') ?></p>
                            <span class="vo-stock mb-3">
                                <span aria-hidden="true">●</span>
                                <?= (int) $p['stock'] > 0 ? 'Stok tersedia: ' . (int) $p['stock'] : 'Stok habis' ?>
                            </span>
                            <a class="btn btn-outline-primary mt-auto" href="detail.php?id=<?= (int) $p['id'] ?>">Lihat Detail</a>
                        </div>
                    </article>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="vo-empty-state">
                    <div class="fs-1 mb-2">⌕</div>
                    <h3 class="h5">Produk tidak ditemukan</h3>
                    <p class="text-secondary mb-3">Coba gunakan kata kunci pencarian yang berbeda.</p>
                    <a href="index.php#produk" class="btn btn-outline-primary">Tampilkan Semua Produk</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$stmt->close();
require_once __DIR__ . '/includes/footer.php';
?>
