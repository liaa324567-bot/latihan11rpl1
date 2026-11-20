<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['login']) || empty($_SESSION['id'])) {
    header('Location: ../../../login.php');
    exit;
}

require_once __DIR__ . '/../../../koneksi.php';
require_once __DIR__ . '/../../../navbar.php';


$userId = (int) $_SESSION['id'];
$stmt = mysqli_prepare($koneksi, 'SELECT username, role FROM users WHERE id = ? LIMIT 1');
if (!$stmt) {
    die('Gagal menyiapkan data akun: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}
mysqli_stmt_bind_param($stmt, 'i', $userId);
if (!mysqli_stmt_execute($stmt)) {
    die('Gagal memuat data akun: ' . htmlspecialchars(mysqli_stmt_error($stmt), ENT_QUOTES, 'UTF-8'));
}
$result = mysqli_stmt_get_result($stmt);
$account = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($stmt);

if (!$account || strtolower((string) $account['role']) !== 'admin') {
    header('Location: ../profile.php');
    exit;
}

$error = '';
$success = '';
$username = (string) $account['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (
        !is_string($csrfToken)
        || empty($_SESSION['csrf_admin_settings'])
        || !hash_equals((string) $_SESSION['csrf_admin_settings'], $csrfToken)
    ) {
        $error = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } else {
        unset($_SESSION['csrf_admin_settings']);
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '') {
            $error = 'Username wajib diisi.';
        } elseif ($password !== '' && strlen($password) < 8) {
            $error = 'Password baru minimal 8 karakter.';
        } else {
            $usernameStmt = mysqli_prepare(
                $koneksi,
                'SELECT id FROM users WHERE username = ? AND id <> ? LIMIT 1'
            );
            if (!$usernameStmt) {
                die('Gagal memeriksa username: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
            }
            mysqli_stmt_bind_param($usernameStmt, 'si', $username, $userId);
            if (!mysqli_stmt_execute($usernameStmt)) {
                die('Gagal memeriksa username: ' . htmlspecialchars(mysqli_stmt_error($usernameStmt), ENT_QUOTES, 'UTF-8'));
            }
            $usernameResult = mysqli_stmt_get_result($usernameStmt);
            $usernameTaken = $usernameResult && mysqli_fetch_assoc($usernameResult);
            mysqli_stmt_close($usernameStmt);

            if ($usernameTaken) {
                $error = 'Username sudah digunakan oleh akun lain.';
            } else {
                if ($password !== '') {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = mysqli_prepare(
                        $koneksi,
                        'UPDATE users SET username = ?, password = ? WHERE id = ?'
                    );
                    if (!$updateStmt) {
                        die('Gagal menyiapkan perubahan akun: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
                    }
                    mysqli_stmt_bind_param($updateStmt, 'ssi', $username, $passwordHash, $userId);
                } else {
                    $updateStmt = mysqli_prepare($koneksi, 'UPDATE users SET username = ? WHERE id = ?');
                    if (!$updateStmt) {
                        die('Gagal menyiapkan perubahan akun: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
                    }
                    mysqli_stmt_bind_param($updateStmt, 'si', $username, $userId);
                }

                if (!mysqli_stmt_execute($updateStmt)) {
                    die('Gagal menyimpan perubahan akun: ' . htmlspecialchars(mysqli_stmt_error($updateStmt), ENT_QUOTES, 'UTF-8'));
                }
                mysqli_stmt_close($updateStmt);

                $_SESSION['username'] = $username;
                $success = 'Pengaturan akun berhasil disimpan.';
            }
        }
    }
}

$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_admin_settings'] = $csrfToken;
$esc = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaturan Akun Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body class="bg-light">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm border">
                    <div class="card-header bg-white py-3">
                        <h1 class="h5 mb-0 fw-normal">Form Edit User (Admin) </h1>
                    </div>
                    <div class="card-body p-4">
                        <h2 class="h4 mb-4 fw-bold">Data Kredensial Akun</h2>

                        <?php if ($error !== ''): ?>
                            <div class="alert alert-danger" role="alert"><?= $esc($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success !== ''): ?>
                            <div class="alert alert-success" role="status"><?= $esc($success) ?></div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken) ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username"
                                    value="<?= $esc($username) ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="password" name="password" minlength="8">
                                <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-3">Simpan Data</button>
                                <a href="../../../table.php" class="btn btn-secondary px-3">Kembali</a>

                             <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
                             integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
                             
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>