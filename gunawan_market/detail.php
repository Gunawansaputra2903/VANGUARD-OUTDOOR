<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/product_image.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(404);
    exit('Produk tidak ditemukan.');
}

$stmt = $conn->prepare("SELECT id, product_name, description, price, stock, image FROM products WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    http_response_code(404);
    exit('Produk tidak ditemukan.');
}

$pageTitle = $product['product_name'] . ' - Vanguard Outdoor';
$productImage = vanguard_product_image($product, __DIR__);
$inStock = (int) $product['stock'] > 0;
require_once __DIR__ . '/includes/header.php';
?>

<section class="vo-detail-page">
    <div class="vo-detail-topline">
        <a class="vo-back-link" href="index.php#produk">
            <span aria-hidden="true">←</span> Kembali ke produk
        </a>
        <span class="vo-detail-breadcrumb">Vanguard Outdoor <span>/</span> Detail Produk</span>
    </div>

    <div class="row g-4 g-xl-5 align-items-start vo-detail-grid">
        <div class="col-lg-6">
            <div class="vo-detail-gallery">
                <span class="vo-detail-image-tag">OUTDOOR EQUIPMENT</span>
                <img
                    class="vo-detail-image"
                    src="<?= htmlspecialchars($productImage, ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>
            <div class="vo-detail-image-caption">
                <span class="vo-caption-icon" aria-hidden="true">⌁</span>
                Perlengkapan pilihan untuk menemani aktivitas luar ruangan Anda.
            </div>
        </div>

        <div class="col-lg-6">
            <div class="vo-detail-info">
                <p class="vo-detail-kicker">VANGUARD OUTDOOR COLLECTION</p>
                <h1 class="vo-detail-title"><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="vo-detail-price">Rp <?= number_format((float) $product['price'], 0, ',', '.') ?></p>

                <div class="vo-detail-divider"></div>

                <h2 class="vo-detail-section-title">Deskripsi Produk</h2>
                <p class="vo-detail-description">
                    <?= nl2br(htmlspecialchars($product['description'] ?? 'Perlengkapan outdoor untuk menemani aktivitas dan perjalanan Anda.', ENT_QUOTES, 'UTF-8')) ?>
                </p>

                <div class="vo-detail-stock-row">
                    <span class="vo-detail-stock-label">Ketersediaan</span>
                    <?php if ($inStock): ?>
                        <span class="vo-stock-badge"><span aria-hidden="true">●</span> Tersedia · <?= (int) $product['stock'] ?> stok
                        </span>
                    <?php else: ?>
                        <span class="vo-stock-badge vo-stock-empty">Stok habis</span>
                    <?php endif; ?>
                </div>

                <?php if ($inStock): ?>
                    <form action="keranjang.php" method="post" class="vo-detail-form">
                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                        <label class="vo-quantity-label" for="qty">Jumlah pembelian</label>
                        <div class="vo-detail-actions">
                            <input
                                id="qty"
                                class="form-control vo-quantity-input"
                                type="number"
                                name="quantity"
                                min="1"
                                max="<?= (int) $product['stock'] ?>"
                                value="1"
                                inputmode="numeric"
                                required
                            >
                            <button class="btn btn-market vo-add-cart-button" type="submit" name="add_to_cart">
                                <span aria-hidden="true">＋</span> Tambah ke Keranjang
                            </button>
                        </div>
                        <p class="vo-detail-hint">Pastikan jumlah pembelian tidak melebihi stok yang tersedia.</p>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning mt-4 mb-0">Maaf, produk ini sedang tidak tersedia.</div>
                <?php endif; ?>

                <div class="vo-detail-benefits">
                    <div><span aria-hidden="true">✓</span> Informasi produk jelas</div>
                    <div><span aria-hidden="true">✓</span> Stok ditampilkan langsung</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
