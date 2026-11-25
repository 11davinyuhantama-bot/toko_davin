<?php
require_once __DIR__ . '/../config/database.php';

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    redirect('login.php');
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_kasir'] ?? '', 'csrf_kasir')) {
        $error = 'Sesi formulir berakhir. Muat ulang halaman lalu coba kembali.';
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $konfirmasi = $_POST['konfirmasi_password'] ?? '';

        if ($nama === '' || mb_strlen($nama) > 100) {
            $error = 'Nama wajib diisi dan maksimal 100 karakter.';
        } elseif (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $username)) {
            $error = 'Username harus 4 sampai 50 karakter, hanya huruf, angka, titik, garis bawah, atau tanda hubung.';
        } elseif (strlen($password) < 8) {
            $error = 'Kata sandi harus minimal 8 karakter.';
        } elseif ($password !== $konfirmasi) {
            $error = 'Konfirmasi kata sandi tidak sama.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'kasir';
                $stmt = $conn->prepare('INSERT INTO tbl_user (nama, username, password, role) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssss', $nama, $username, $hash, $role);
                $stmt->execute();
                $stmt->close();
                $success = 'Akun kasir berhasil ditambahkan.';
                $_POST = [];
            } catch (mysqli_sql_exception $exception) {
                $error = $exception->getCode() === 1062
                    ? 'Username sudah digunakan. Silakan pilih username lain.'
                    : 'Akun kasir belum bisa dibuat. Periksa kembali database.';
            }
        }
    }
}

$cashiers = $conn->query("SELECT id_user, nama, username FROM tbl_user WHERE role = 'kasir' ORDER BY nama ASC");
$pageTitle = 'Akun Kasir';
include __DIR__ . '/../includes/header.php';
?>

<style>
.cashier-page-intro{padding:22px 24px;border:1px solid #dbeafe;border-radius:16px;background:linear-gradient(115deg,#eff6ff,#fff 70%);margin-bottom:22px}
.cashier-page-icon{width:48px;height:48px;display:grid;place-items:center;border-radius:14px;background:#dbeafe;color:#1d4ed8;font-size:1.35rem}
.cashier-form-card,.cashier-list-card{overflow:hidden;border:1px solid #e4eaf2;border-radius:16px;box-shadow:0 8px 28px rgba(15,23,42,.045)}
.cashier-form-heading{display:flex;align-items:center;gap:14px;padding:20px 24px;background:linear-gradient(120deg,#172554,#2563eb);color:#fff}
.cashier-form-heading p{font-size:.86rem;color:#dbeafe}.cashier-eyebrow{display:block;font-size:.66rem;font-weight:750;letter-spacing:.12em;color:#bfdbfe;margin-bottom:5px}
.cashier-heading-icon{width:44px;height:44px;flex:0 0 44px;display:grid;place-items:center;border-radius:13px;background:#ffffff20;font-size:1.2rem}
.cashier-form-card .form-label{margin-bottom:7px}.cashier-form-card .form-text{font-size:.75rem;color:#7b8798;margin:6px 2px 0}
.cashier-input .input-group-text,.cashier-input .form-control,.cashier-input .btn{min-height:44px;border-color:#dbe3ed;background-color:#fff}
.cashier-input .input-group-text{color:#64748b;background:#f8fafc}.cashier-input .form-control{box-shadow:none}.cashier-input .form-control:focus{border-color:#2563eb;box-shadow:0 0 0 .18rem rgba(37,99,235,.1);z-index:3}
.cashier-submit{min-height:46px;font-weight:650;box-shadow:0 5px 12px rgba(37,99,235,.18)}.cashier-security-note{padding:10px 12px;border-radius:9px;background:#f8fafc;color:#64748b;font-size:.78rem}
.cashier-list-heading{padding:19px 22px;border-bottom:1px solid #edf0f4}.cashier-avatar{width:36px;height:36px;display:grid;place-items:center;border-radius:11px;background:#eff6ff;color:#2563eb;font-weight:700}
@media(max-width:575px){.cashier-page-intro{padding:17px}.cashier-form-heading{padding:18px}.cashier-form-card .card-body{padding:18px!important}.cashier-list-heading{padding:16px}}
</style>

<section class="cashier-page-intro d-flex align-items-center gap-3">
    <div class="cashier-page-icon"><i class="bi bi-people-fill"></i></div>
    <div><h4 class="fw-bold mb-1">Kelola Pengguna Kasir</h4><p class="text-secondary mb-0">Buat akun staf dan lihat daftar pengguna yang memiliki akses kasir.</p></div>
</section>

<div class="row g-4 align-items-start">
    <div class="col-xl-5">
        <section class="card cashier-form-card">
            <div class="cashier-form-heading"><div class="cashier-heading-icon"><i class="bi bi-person-plus-fill"></i></div><div><span class="cashier-eyebrow">PENGATURAN TIM</span><h2 class="h5 fw-bold mb-1">Tambah akun kasir</h2><p class="mb-0">Isi data staf untuk membuat akun baru.</p></div></div>
            <div class="card-body p-4">
                <?php if ($error): ?><div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-circle me-2"></i><?= e($error) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success" role="status"><i class="bi bi-check-circle me-2"></i><?= e($success) ?></div><?php endif; ?>
                <form method="post" autocomplete="on">
                    <input type="hidden" name="csrf_kasir" value="<?= e(csrf_token('csrf_kasir')) ?>">
                    <div class="mb-3"><label class="form-label" for="nama">Nama lengkap</label><div class="input-group cashier-input"><span class="input-group-text"><i class="bi bi-person"></i></span><input class="form-control" id="nama" name="nama" maxlength="100" placeholder="Contoh: Rina Putri" autocomplete="name" required value="<?= e($_POST['nama'] ?? '') ?>"></div></div>
                    <div class="mb-3"><label class="form-label" for="username">Username</label><div class="input-group cashier-input"><span class="input-group-text">@</span><input class="form-control" id="username" name="username" minlength="4" maxlength="50" pattern="[A-Za-z0-9._-]+" placeholder="rina.kasir" autocomplete="username" required value="<?= e($_POST['username'] ?? '') ?>"></div><div class="form-text">4 sampai 50 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.</div></div>
                    <div class="mb-3"><label class="form-label" for="password">Kata sandi</label><div class="input-group cashier-input"><span class="input-group-text"><i class="bi bi-lock"></i></span><input class="form-control" type="password" id="password" name="password" minlength="8" placeholder="Minimal 8 karakter" autocomplete="new-password" required><button class="btn btn-outline-secondary toggle-password" type="button" data-target="password" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button></div></div>
                    <div class="mb-4"><label class="form-label" for="konfirmasi_password">Ulangi kata sandi</label><div class="input-group cashier-input"><span class="input-group-text"><i class="bi bi-shield-lock"></i></span><input class="form-control" type="password" id="konfirmasi_password" name="konfirmasi_password" minlength="8" placeholder="Ketik ulang kata sandi" autocomplete="new-password" required><button class="btn btn-outline-secondary toggle-password" type="button" data-target="konfirmasi_password" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button></div></div>
                    <button class="btn btn-primary cashier-submit w-100" type="submit"><i class="bi bi-person-check me-2"></i>Buat akun kasir</button>
                </form>
                <div class="cashier-security-note mt-3"><i class="bi bi-shield-check me-2"></i>Kata sandi disimpan dengan aman.</div>
            </div>
        </section>
    </div>
    <div class="col-xl-7">
        <section class="card cashier-list-card">
            <div class="cashier-list-heading d-flex justify-content-between align-items-center gap-3"><div><h2 class="h5 fw-bold mb-1">Daftar pengguna kasir</h2><div class="small text-secondary">Akun yang memiliki akses ke area kasir.</div></div><span class="badge rounded-pill text-bg-primary px-3 py-2"><?= $cashiers->num_rows ?> akun</span></div>
            <div class="table-responsive"><table class="table table-hover mb-0 align-middle"><thead><tr><th class="ps-4">No.</th><th>Nama</th><th>Username</th></tr></thead><tbody>
                <?php if ($cashiers->num_rows): $number = 1; while ($cashier = $cashiers->fetch_assoc()): ?>
                    <tr><td class="ps-4 text-secondary"><?= $number ?></td><td><div class="d-flex align-items-center gap-2"><span class="cashier-avatar"><?= e(mb_strtoupper(mb_substr($cashier['nama'], 0, 1))) ?></span><span class="fw-semibold"><?= e($cashier['nama']) ?></span></div></td><td><span class="text-secondary">@</span><?= e($cashier['username']) ?></td></tr>
                    <?php $number++; ?>
                <?php endwhile; else: ?><tr><td colspan="3" class="text-center text-secondary py-5"><i class="bi bi-person-plus fs-3 d-block mb-2"></i>Belum ada akun kasir.</td></tr><?php endif; ?>
            </tbody></table></div>
        </section>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-password').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.target);
        const icon = button.querySelector('i');
        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        icon.className = reveal ? 'bi bi-eye-slash' : 'bi bi-eye';
        button.setAttribute('aria-label', reveal ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
