<?php
/**
 * Menghasilkan URL foto produk yang sesuai dengan nama produk.
 * Produk contoh Vanguard menggunakan foto lokal yang sudah disiapkan.
 * Produk lain tetap menggunakan foto unggahan admin jika tersedia.
 */
function vanguard_product_image(array $product, string $baseDir, string $baseUrl = ''): string
{
    // Prioritaskan foto yang diunggah admin. Pemetaan foto bawaan hanya
    // digunakan jika produk belum memiliki file unggahan yang valid.
    $uploaded = basename((string)($product['image'] ?? ''));
    if ($uploaded !== '' && is_file($baseDir . '/uploads/' . $uploaded)) {
        return $baseUrl . 'uploads/' . rawurlencode($uploaded);
    }

    $name = mb_strtolower((string)($product['product_name'] ?? ''), 'UTF-8');
    $imageMap = [
        '/daypack|tas|ransel/' => 'tas-outdoor.jpg',
        '/botol|minum/' => 'botol-minum.jpg',
        '/jaket|pakaian|baju|celana|hoodie/' => 'jaket-outdoor.jpg',
        '/headlamp|lampu|senter|lentera/' => 'headlamp-led.jpg',
        '/sepatu|boot|hiking|trekking/' => 'sepatu-hiking.jpg',
        '/tenda|camping|kemah/' => 'tenda-camping.jpg',
    ];

    foreach ($imageMap as $pattern => $filename) {
        if (preg_match($pattern, $name)) {
            return $baseUrl . 'assets/images/products/' . rawurlencode($filename);
        }
    }

    return $baseUrl . 'assets/images/products/tas-outdoor.jpg';
}
