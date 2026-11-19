<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/csrf.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function login_h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function login_next($value): string {
    return in_array($value, ['checkout.php', 'pesanan_saya.php'], true) ? $value : 'pesanan_saya.php';
}

if (!empty($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header('Location: admin/dashboard.php'); exit;
    }
    if (($_SESSION['role'] ?? '') === 'pembeli') {
        $alreadyNext = login_next($_GET['next'] ?? 'pesanan_saya.php');
        header('Location: ' . $alreadyNext); exit;
    }
}

$next = login_next($_POST['next'] ?? $_GET['next'] ?? 'pesanan_saya.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT id, name, password, role FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php'); exit;
        }
        if ($user['role'] === 'pembeli') {
            header('Location: ' . $next); exit;
        }
    }
    $error = 'Email atau password tidak cocok. Periksa kembali data akunmu.';
}

$pageTitle = 'Masuk - Vanguard Outdoor';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-shell mx-auto">
  <div class="card auth-card shadow-sm border-0">
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <img src="assets/images/vanguard-mountain.svg" class="brand-logo brand-logo-auth mx-auto mb-3" alt="Logo Vanguard Outdoor">
        <div class="brand-eyebrow">VANGUARD OUTDOOR</div>
        <h1 class="h3 mb-2">Selamat datang kembali</h1>
        <p class="text-secondary mb-0">Masuk dengan email dan password akunmu.</p>
      </div>
      <?php if ($error !== ''): ?><div class="alert alert-danger"><?= login_h($error) ?></div><?php endif; ?>
      <form method="post" action="login.php">
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= login_h($next) ?>">
        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input class="form-control form-control-lg" id="email" name="email" type="email" required autocomplete="username" value="<?= login_h($_POST['email'] ?? '') ?>" placeholder="nama@email.com">
        </div>
        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <input class="form-control form-control-lg" id="password" name="password" type="password" required autocomplete="current-password" placeholder="Masukkan password">
        </div>
        <button class="btn btn-market btn-lg w-100" type="submit">Masuk</button>
      </form>
      <div class="auth-divider"><span>Belum punya akun?</span></div>
      <a class="btn btn-outline-primary w-100" href="register.php?next=<?= rawurlencode($next) ?>">Daftar Akun</a>
      <p class="small text-secondary text-center mt-3 mb-0">Sistem akan mengarahkan akun admin ke dashboard admin dan akun pembeli ke halaman toko.</p>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
