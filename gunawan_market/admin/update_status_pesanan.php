<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}
verify_csrf();

$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$newStatus = $_POST['new_status'] ?? '';
$allowedTransitions = [
    'menunggu' => ['diproses', 'dibatalkan'],
    'diproses' => ['dikirim', 'dibatalkan'],
    'dikirim' => ['selesai'],
    'selesai' => [],
    'dibatalkan' => [],
];

if (!$orderId || $orderId < 1 || !is_string($newStatus)) {
    $_SESSION['flash'] = 'Data perubahan status tidak valid.';
    header('Location: pesanan.php');
    exit;
}

try {
    $conn->begin_transaction();

    $getOrder = $conn->prepare('SELECT id, status FROM orders WHERE id = ? LIMIT 1 FOR UPDATE');
    $getOrder->bind_param('i', $orderId);
    $getOrder->execute();
    $order = $getOrder->get_result()->fetch_assoc();
    $getOrder->close();

    if (!$order) {
        throw new RuntimeException('Pesanan tidak ditemukan.');
    }
    $currentStatus = $order['status'];
    $validNext = $allowedTransitions[$currentStatus] ?? [];
    if (!in_array($newStatus, $validNext, true)) {
        throw new RuntimeException('Perubahan status tidak sesuai dengan alur pesanan.');
    }

    if ($newStatus === 'dibatalkan') {
        $getItems = $conn->prepare('SELECT product_id, quantity FROM order_details WHERE order_id = ? AND product_id IS NOT NULL');
        $getItems->bind_param('i', $orderId);
        $getItems->execute();
        $itemsResult = $getItems->get_result();
        $restoreStock = $conn->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
        while ($item = $itemsResult->fetch_assoc()) {
            $quantity = (int)$item['quantity'];
            $productId = (int)$item['product_id'];
            if ($quantity > 0 && $productId > 0) {
                $restoreStock->bind_param('ii', $quantity, $productId);
                $restoreStock->execute();
            }
        }
        $restoreStock->close();
        $getItems->close();
    }

    $update = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $update->bind_param('si', $newStatus, $orderId);
    $update->execute();
    $update->close();

    $conn->commit();
    $_SESSION['flash'] = 'Status pesanan berhasil diperbarui menjadi ' . ucfirst($newStatus) . '.';
} catch (Throwable $e) {
    $conn->rollback();
    $_SESSION['flash'] = $e instanceof RuntimeException ? $e->getMessage() : 'Status pesanan gagal diperbarui. Silakan coba lagi.';
}

header('Location: detail_pesanan.php?id=' . (int)$orderId);
exit;
