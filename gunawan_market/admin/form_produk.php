<?php
// Partial form produk; variabel $p, $categories, $actionLabel disiapkan oleh halaman pemanggil.
?>
<div class="mb-3"><label class="form-label">Nama Produk</label><input class="form-control" name="product_name" maxlength="150" required value="<?= htmlspecialchars($p['product_name']??'') ?>"></div>
<div class="mb-3"><label class="form-label">Kategori</label><select class="form-select" name="category_id" required><option value="">Pilih kategori</option><?php while($c=$categories->fetch_assoc()):?><option value="<?= (int)$c['id'] ?>" <?= (int)($p['category_id']??0)===(int)$c['id']?'selected':'' ?>><?= htmlspecialchars($c['category_name']) ?></option><?php endwhile;?></select></div>
<div class="mb-3"><label class="form-label">Deskripsi</label><textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($p['description']??'') ?></textarea></div>
<div class="row"><div class="col-md-6 mb-3"><label class="form-label">Harga (Rp)</label><input class="form-control" type="number" name="price" min="0" step="0.01" required value="<?= htmlspecialchars((string)($p['price']??'')) ?>"></div><div class="col-md-6 mb-3"><label class="form-label">Stok</label><input class="form-control" type="number" name="stock" min="0" step="1" required value="<?= htmlspecialchars((string)($p['stock']??'')) ?>"></div></div>
<div class="mb-3">
    <label class="form-label">Foto Produk (JPG, PNG, WEBP maks. 2 MB)</label>
    <input class="form-control" type="file" name="image" id="productImageInput" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
    <div class="mt-3" id="productImagePreviewWrap" <?= empty($p['image']) || !is_file(__DIR__.'/../uploads/'.basename((string)$p['image'])) ? 'hidden' : '' ?>>
        <img id="productImagePreview" src="<?= !empty($p['image']) && is_file(__DIR__.'/../uploads/'.basename((string)$p['image'])) ? '../uploads/'.rawurlencode(basename((string)$p['image'])) : '' ?>" alt="Pratinjau foto produk" style="width:140px;height:140px;object-fit:cover;border-radius:10px;border:1px solid #dee2e6">
        <div class="form-text">Pratinjau foto produk. Pilih foto baru untuk menggantinya.</div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('productImageInput');
    const preview = document.getElementById('productImagePreview');
    const wrap = document.getElementById('productImagePreviewWrap');
    if (!input || !preview || !wrap) return;
    let currentObjectUrl = null;
    input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            alert('Format gambar harus JPG, PNG, atau WEBP.');
            this.value = '';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran gambar maksimal 2 MB.');
            this.value = '';
            return;
        }
        if (currentObjectUrl) URL.revokeObjectURL(currentObjectUrl);
        currentObjectUrl = URL.createObjectURL(file);
        preview.src = currentObjectUrl;
        wrap.hidden = false;
    });
});
</script>
