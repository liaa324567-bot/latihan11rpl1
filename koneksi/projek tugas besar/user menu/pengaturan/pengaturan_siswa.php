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


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$userId = (int) $_SESSION['id'];
$error = '';
$success = '';
$transactionStarted = false;
$profileExists = false;
$username = '';
$nis = '';
$nama = '';
$kelas = '';
$jenisKelamin = '';

try {
    $accountStmt = mysqli_prepare(
        $koneksi,
        'SELECT users.username, users.role, siswa.nis, siswa.nama, siswa.kelas, siswa.jenis_kelamin
         FROM users
         LEFT JOIN siswa ON siswa.user_id = users.id
         WHERE users.id = ?
         LIMIT 1'
    );
    mysqli_stmt_bind_param($accountStmt, 'i', $userId);
    mysqli_stmt_execute($accountStmt);
    $accountResult = mysqli_stmt_get_result($accountStmt);
    $account = $accountResult ? mysqli_fetch_assoc($accountResult) : null;
    mysqli_stmt_close($accountStmt);

    if (!$account || strtolower((string) $account['role']) !== 'siswa') {
        header('Location: ../profile.php');
        exit;
    }

    $username = (string) $account['username'];
    $nis = (string) ($account['nis'] ?? '');
    $nama = (string) ($account['nama'] ?? '');
    $kelas = (string) ($account['kelas'] ?? '');
    $jenisKelamin = (string) ($account['jenis_kelamin'] ?? '');
    $profileExists = $account['nis'] !== null;

    if (!$profileExists) {
        $error = 'Data siswa untuk akun ini tidak ditemukan.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (
            !is_string($csrfToken)
            || empty($_SESSION['csrf_settings_siswa'])
            || !hash_equals((string) $_SESSION['csrf_settings_siswa'], $csrfToken)
        ) {
            $error = 'Sesi formulir tidak valid. Silakan coba lagi.';
        } else {
            unset($_SESSION['csrf_settings_siswa']);
            $username = trim((string) ($_POST['username'] ?? ''));
            $nis = trim((string) ($_POST['nis'] ?? ''));
            $nama = trim((string) ($_POST['nama'] ?? ''));
            $kelas = trim((string) ($_POST['kelas'] ?? ''));
            $jenisKelamin = (string) ($_POST['jenis_kelamin'] ?? '');
            $password = (string) ($_POST['password'] ?? '');

            if ($username === '' || $nis === '' || $nama === '' || $kelas === '') {
                $error = 'Username, NIS, nama, dan kelas wajib diisi.';
            } elseif (!in_array($jenisKelamin, ['L', 'P'], true)) {
                $error = 'Pilih jenis kelamin yang valid.';
            } elseif ($password !== '' && strlen($password) < 8) {
                $error = 'Password baru minimal 8 karakter.';
            }

            if ($error === '') {
                $usernameStmt = mysqli_prepare(
                    $koneksi,
                    'SELECT id FROM users WHERE username = ? AND id <> ? LIMIT 1'
                );
                mysqli_stmt_bind_param($usernameStmt, 'si', $username, $userId);
                mysqli_stmt_execute($usernameStmt);
                $usernameResult = mysqli_stmt_get_result($usernameStmt);
                $usernameTaken = $usernameResult && mysqli_fetch_assoc($usernameResult);
                mysqli_stmt_close($usernameStmt);

                if ($usernameTaken) {
                    $error = 'Username sudah digunakan oleh akun lain.';
                }
            }

            if ($error === '') {
                $nisStmt = mysqli_prepare(
                    $koneksi,
                    'SELECT user_id FROM siswa WHERE nis = ? AND user_id <> ? LIMIT 1'
                );
                mysqli_stmt_bind_param($nisStmt, 'si', $nis, $userId);
                mysqli_stmt_execute($nisStmt);
                $nisResult = mysqli_stmt_get_result($nisStmt);
                $nisTaken = $nisResult && mysqli_fetch_assoc($nisResult);
                mysqli_stmt_close($nisStmt);

                if ($nisTaken) {
                    $error = 'NIS sudah digunakan oleh siswa lain.';
                }
            }

            if ($error === '') {
                mysqli_begin_transaction($koneksi);
                $transactionStarted = true;

                if ($password !== '') {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateUser = mysqli_prepare(
                        $koneksi,
                        'UPDATE users SET username = ?, password = ? WHERE id = ?'
                    );
                    mysqli_stmt_bind_param($updateUser, 'ssi', $username, $passwordHash, $userId);
                } else {
                    $updateUser = mysqli_prepare(
                        $koneksi,
                        'UPDATE users SET username = ? WHERE id = ?'
                    );
                    mysqli_stmt_bind_param($updateUser, 'si', $username, $userId);
                }
                mysqli_stmt_execute($updateUser);
                mysqli_stmt_close($updateUser);

                $updateSiswa = mysqli_prepare(
                    $koneksi,
                    'UPDATE siswa
                     SET nis = ?, nama = ?, kelas = ?, jenis_kelamin = ?
                     WHERE user_id = ?'
                );
                mysqli_stmt_bind_param(
                    $updateSiswa,
                    'ssssi',
                    $nis,
                    $nama,
                    $kelas,
                    $jenisKelamin,
                    $userId
                );
                mysqli_stmt_execute($updateSiswa);
                mysqli_stmt_close($updateSiswa);

                mysqli_commit($koneksi);
                $transactionStarted = false;
                $_SESSION['username'] = $username;
                $_SESSION['nama'] = $nama;
                $success = 'Data siswa berhasil diperbarui.';
            }
        }
    }
} catch (mysqli_sql_exception $exception) {
    if ($transactionStarted) {
        mysqli_rollback($koneksi);
    }
    error_log('Pengaturan siswa gagal disimpan: ' . $exception->getMessage());
    $error = 'Data siswa gagal disimpan. Periksa kembali data yang dimasukkan.';
}

$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_settings_siswa'] = $csrfToken;
$esc = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Edit User (Siswa)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="card shadow-sm border mx-auto" style="max-width: 720px;">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-normal">Form Edit User (Siswa)</h5>
            </div>
            <div class="card-body p-4">

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= $esc($error) ?></div>
                <?php endif; ?>
                <?php if ($success !== ''): ?>
                    <div class="alert alert-success" role="status"><?= $esc($success) ?></div>
                <?php endif; ?>

                <?php if ($profileExists): ?>
                    <form action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken) ?>">

                        <h4 class="mb-3 fw-bold">Data Profil User</h4>

                        <div class="mb-3">
                            <label for="nis" class="form-label">NIS:</label>
                            <input type="text" class="form-control" id="nis" name="nis"
                                   value="<?= $esc($nis) ?>" maxlength="10" required>
                        </div>

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap:</label>
                            <input type="text" class="form-control" id="nama" name="nama"
                                   value="<?= $esc($nama) ?>" maxlength="100" required>
                        </div>

                        <div class="mb-3">
                            <label for="kelas" class="form-label">Kelas:</label>
                            <input type="text" class="form-control" id="kelas" name="kelas"
                                   value="<?= $esc($kelas) ?>" maxlength="10" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-block">Jenis Kelamin:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="laki" value="L" <?= $jenisKelamin === 'L' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="laki">Laki-laki</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="perempuan" value="P" <?= $jenisKelamin === 'P' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="perempuan">Perempuan</label>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h4 class="mb-3 fw-bold">Data Kredensial Akun</h4>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" class="form-control" id="username" name="username"
                                   value="<?= $esc($username) ?>" maxlength="50" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password Akun:</label>
                            <input type="password" class="form-control text-muted" id="password" name="password" minlength="8" placeholder="Password baru | jika tidak diubah, kosongkan">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-3">Simpan Data</button>
                            <a href="../../daftar_siswa2.php" class="btn btn-secondary px-3">Kembali</a>

                        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
                        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>