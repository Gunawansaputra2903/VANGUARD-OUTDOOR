<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/csrf.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') { header('Location: admin/dashboard.php'); exit; }
    if (($_SESSION['role'] ?? '') === 'pembeli') { header('Location: pesanan_saya.php'); exit; }
}
function reg_h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function reg_next($value): string {
    return in_array($value, ['checkout.php', 'pesanan_saya.php'], true) ? $value : 'pesanan_saya.php';
}
$next = reg_next($_POST['next'] ?? $_GET['next'] ?? 'pesanan_saya.php');
$error = '';
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $legacyCode = strtoupper(trim($_POST['order_code'] ?? ''));

    if ($name === '' || mb_strlen($name) > 100) {
        $error = 'Nama wajib diisi dan maksimal 100 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
        $error = 'Masukkan alamat email yang valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        try {
            $find = $conn->prepare('SELECT id, role FROM users WHERE email = ? LIMIT 1');
            $find->bind_param('s', $email);
            $find->execute();
            $existing = $find->get_result()->fetch_assoc();
            $find->close();

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            if ($existing) {
                if ($existing['role'] !== 'pembeli') {
                    throw new RuntimeException('Email tersebut digunakan oleh akun admin. Gunakan email lain.');
                }
                // Akun pembeli dari checkout versi lama dapat diaktifkan dengan kode pesanan milik pembeli.
                if ($legacyCode === '') {
                    throw new RuntimeException('Email ini sudah tercatat dari checkout sebelumnya. Masukkan salah satu kode pesanan lama untuk mengaktifkan akun, atau gunakan email lain.');
                }
                $uid = (int)$existing['id'];
                $verify = $conn->prepare('SELECT id FROM orders WHERE user_id = ? AND order_code = ? LIMIT 1');
                $verify->bind_param('is', $uid, $legacyCode);
                $verify->execute();
                $legacyOrder = $verify->get_result()->fetch_assoc();
                $verify->close();
                if (!$legacyOrder) {
                    throw new RuntimeException('Kode pesanan tidak cocok dengan email tersebut. Periksa kembali kode pesanan.');
                }
                $update = $conn->prepare('UPDATE users SET name = ?, password = ? WHERE id = ? AND role = \'pembeli\'');
                $update->bind_param('ssi', $name, $passwordHash, $uid);
                $update->execute();
                $update->close();
            } else {
                $insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'pembeli')");
                $insert->bind_param('sss', $name, $email, $passwordHash);
                $insert->execute();
                $uid = (int)$conn->insert_id;
                $insert->close();
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = $uid;
            $_SESSION['user_name'] = $name;
            $_SESSION['role'] = 'pembeli';
            header('Location: ' . $next); exit;
        } catch (Throwable $e) {
            $error = $e instanceof RuntimeException ? $e->getMessage() : 'Registrasi belum berhasil. Periksa database dan coba kembali.';
        }
    }
}
$pageTitle = 'Daftar Akun - Vanguard Outdoor';
require_once __DIR__ . '/includes/header.php';
?>
<div class="card shadow-sm mx-auto" style="max-width:560px"><div class="card-body p-4 p-md-5">
    <div class="text-center mb-3"><img src="assets/images/vanguard-mountain.svg" class="brand-logo brand-logo-auth" alt="Logo Vanguard Outdoor"><div class="brand-eyebrow mt-2">VANGUARD OUTDOOR</div></div><h1 class="h3 mb-2">Buat Akun Pembeli</h1>
    <p class="text-secondary">Buat akun agar kamu bisa melihat riwayat dan status pesanan.</p>
    <?php if ($error !== ''): ?><div class="alert alert-danger"><?= reg_h($error) ?></div><?php endif; ?>
    <form method="post" action="register.php">
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= reg_h($next) ?>">
        <div class="mb-3"><label class="form-label" for="name">Nama lengkap</label><input class="form-control" id="name" name="name" maxlength="100" required value="<?= reg_h($_POST['name'] ?? '') ?>"></div>
        <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" maxlength="100" required value="<?= reg_h($_POST['email'] ?? '') ?>"></div>
        <div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" minlength="8" required autocomplete="new-password"><div class="form-text">Minimal 8 karakter.</div></div>
        <div class="mb-3"><label class="form-label" for="confirm_password">Konfirmasi password</label><input class="form-control" id="confirm_password" name="confirm_password" type="password" minlength="8" required autocomplete="new-password"></div>
        <div class="mb-3"><label class="form-label" for="order_code">Kode pesanan lama (opsional)</label><input class="form-control" id="order_code" name="order_code" maxlength="50" value="<?= reg_h($_POST['order_code'] ?? '') ?>"><div class="form-text">Isi hanya jika email ini pernah digunakan checkout sebelum fitur akun tersedia. Kode ini digunakan untuk menghubungkan riwayat pesanan lama.</div></div>
        <button class="btn btn-market w-100" type="submit">Daftar</button>
    </form>
    <p class="text-center mt-3 mb-0">Sudah punya akun? <a href="login.php?next=<?= rawurlencode($next) ?>">Login di sini</a></p>
</div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
