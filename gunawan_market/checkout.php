<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/csrf.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Checkout wajib menggunakan akun pembeli agar riwayat dan status pesanan tersimpan pada akun tersebut.
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: login.php?next=checkout.php'); exit;
}
$userId = (int)$_SESSION['user_id'];
$userStmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ? AND role = 'pembeli' LIMIT 1");
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$buyer = $userStmt->get_result()->fetch_assoc();
$userStmt->close();
if (!$buyer) {
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['role']);
    header('Location: login.php?next=checkout.php'); exit;
}

$_SESSION['cart'] ??= [];
$cart = $_SESSION['cart'];
if (!$cart) { header('Location: keranjang.php'); exit; }
$ids = array_values(array_filter(array_map('intval', array_keys($cart)), fn($id) => $id > 0));
if (!$ids) { header('Location: keranjang.php'); exit; }
$marks = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));
$stmt = $conn->prepare("SELECT id, product_name, price, stock FROM products WHERE id IN ($marks)");
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$result = $stmt->get_result();
$items = []; $total = 0;
while ($product = $result->fetch_assoc()) {
    $qty = max(0, (int)($cart[$product['id']] ?? 0));
    if ($qty < 1) continue;
    $product['quantity'] = $qty;
    $product['subtotal'] = (float)$product['price'] * $qty;
    $total += $product['subtotal'];
    $items[] = $product;
}
$stmt->close();
if (!$items) { header('Location: keranjang.php'); exit; }
$error = '';
function checkout_h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $address = trim($_POST['shipping_address'] ?? '');
    if ($address === '' || mb_strlen($address) > 2000) {
        $error = 'Alamat pengiriman wajib diisi (maksimal 2.000 karakter).';
    } else {
        try {
            $conn->begin_transaction();
            // Kunci akun pembeli dan produk untuk menjaga konsistensi transaksi.
            $lockUser = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'pembeli' LIMIT 1 FOR UPDATE");
            $lockUser->bind_param('i', $userId);
            $lockUser->execute();
            $lockedUser = $lockUser->get_result()->fetch_assoc();
            $lockUser->close();
            if (!$lockedUser) throw new RuntimeException('Akun pembeli tidak ditemukan. Silakan login kembali.');

            $orderItems = []; $grandTotal = 0;
            $lockProduct = $conn->prepare('SELECT product_name, price, stock FROM products WHERE id = ? FOR UPDATE');
            foreach ($cart as $productId => $quantity) {
                $productId = (int)$productId; $quantity = (int)$quantity;
                if ($productId < 1 || $quantity < 1) continue;
                $lockProduct->bind_param('i', $productId);
                $lockProduct->execute();
                $product = $lockProduct->get_result()->fetch_assoc();
                if (!$product) throw new RuntimeException('Salah satu produk di keranjang sudah tidak tersedia.');
                if ($quantity > (int)$product['stock']) throw new RuntimeException('Stok produk "' . $product['product_name'] . '" tidak mencukupi. Silakan kembali ke keranjang.');
                $price = (float)$product['price'];
                $subtotal = $price * $quantity;
                $grandTotal += $subtotal;
                $orderItems[] = ['id'=>$productId, 'name'=>$product['product_name'], 'price'=>$price, 'quantity'=>$quantity, 'subtotal'=>$subtotal];
            }
            $lockProduct->close();
            if (!$orderItems) throw new RuntimeException('Keranjang belanja kosong.');

            $orderCode = 'GM-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $insertOrder = $conn->prepare("INSERT INTO orders (user_id, order_code, total_price, shipping_address, status) VALUES (?, ?, ?, ?, 'menunggu')");
            $insertOrder->bind_param('isds', $userId, $orderCode, $grandTotal, $address);
            $insertOrder->execute();
            $orderId = (int)$conn->insert_id;
            $insertOrder->close();

            $insertDetail = $conn->prepare('INSERT INTO order_details (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
            $reduceStock = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
            foreach ($orderItems as $item) {
                $insertDetail->bind_param('iisdid', $orderId, $item['id'], $item['name'], $item['price'], $item['quantity'], $item['subtotal']);
                $insertDetail->execute();
                $qty = $item['quantity']; $pid = $item['id'];
                $reduceStock->bind_param('iii', $qty, $pid, $qty);
                $reduceStock->execute();
                if ($reduceStock->affected_rows !== 1) throw new RuntimeException('Stok berubah saat transaksi diproses. Silakan coba lagi.');
            }
            $insertDetail->close(); $reduceStock->close();
            $conn->commit();
            $_SESSION['cart'] = [];
            $_SESSION['last_order_code'] = $orderCode;
            header('Location: pesanan_sukses.php'); exit;
        } catch (Throwable $e) {
            $conn->rollback();
            $error = $e instanceof RuntimeException ? $e->getMessage() : 'Pesanan belum dapat diproses. Periksa koneksi database lalu coba kembali.';
        }
    }
}

$pageTitle = 'Checkout - Vanguard Outdoor';
require_once __DIR__ . '/includes/header.php';
?>
<main class="vo-transaction-page"><div class="vo-page-heading"><div><span class="vo-page-kicker">LANGKAH TERAKHIR</span><h1>Checkout</h1><p>Pastikan alamat penerima sudah benar sebelum membuat pesanan.</p></div><a class="vo-text-link" href="keranjang.php">← Kembali ke keranjang</a></div><div class="row g-4 vo-checkout-grid">
  <div class="col-lg-7">
    <div class="card shadow-sm vo-transaction-card"><div class="card-body p-4">
      <h1 class="h3 mb-3">Checkout</h1>
      <p class="text-secondary">Periksa data akun dan lengkapi alamat pengiriman.</p>
      <?php if ($error): ?><div class="alert alert-danger"><?= checkout_h($error) ?></div><?php endif; ?>
      <div class="vo-account-box">
        <div class="small text-secondary">Akun pembeli</div>
        <div class="fw-semibold"><?= checkout_h($buyer['name']) ?></div>
        <div><?= checkout_h($buyer['email']) ?></div>
        <a class="small" href="pesanan_saya.php">Lihat Pesanan Saya</a>
      </div>
      <form method="post" action="checkout.php">
        <?= csrf_field() ?>
        <div class="mb-3 vo-address-field"><label class="form-label" for="shipping_address">Alamat pengiriman</label><textarea class="form-control" id="shipping_address" name="shipping_address" rows="4" maxlength="2000" required><?= checkout_h($_POST['shipping_address'] ?? '') ?></textarea></div>
        <button class="btn btn-market" type="submit">Buat Pesanan</button> <a class="btn btn-outline-secondary" href="keranjang.php">Kembali</a>
      </form>
    </div></div>
  </div>
  <div class="col-lg-5"><div class="card shadow-sm vo-transaction-card vo-summary-card"><div class="card-body p-4">
    <h2 class="h5">Ringkasan Belanja</h2>
    <?php foreach ($items as $item): ?><div class="d-flex justify-content-between gap-3 py-2 border-bottom"><div><?= checkout_h($item['product_name']) ?><div class="small text-secondary"><?= (int)$item['quantity'] ?> × Rp <?= number_format((float)$item['price'],0,',','.') ?></div></div><strong>Rp <?= number_format((float)$item['subtotal'],0,',','.') ?></strong></div><?php endforeach; ?>
    <div class="d-flex justify-content-between pt-3"><strong>Total</strong><strong class="price">Rp <?= number_format($total,0,',','.') ?></strong></div>
    <p class="small text-secondary mt-3 mb-0">Total dan stok akan diperiksa kembali sebelum pesanan disimpan.</p>
  </div></div></div>
</div></main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
