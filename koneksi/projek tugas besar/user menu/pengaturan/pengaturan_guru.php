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
$nip = '';
$nama = '';
$jenisKelamin = '';

try {
    $accountStmt = mysqli_prepare(
        $koneksi,
        'SELECT users.username, users.role, guru.id AS guru_id, guru.nip, guru.nama, guru.jenis_kelamin
         FROM users
         LEFT JOIN guru ON guru.user_id = users.id
         WHERE users.id = ?
         LIMIT 1'
    );
    mysqli_stmt_bind_param($accountStmt, 'i', $userId);
    mysqli_stmt_execute($accountStmt);
    $accountResult = mysqli_stmt_get_result($accountStmt);
    $account = $accountResult ? mysqli_fetch_assoc($accountResult) : null;
    mysqli_stmt_close($accountStmt);

    if (!$account || strtolower((string) $account['role']) !== 'guru') {
        header('Location: ../profile.php');
        exit;
    }

    $username = (string) $account['username'];
    $nip = (string) ($account['nip'] ?? '');
    $nama = (string) ($account['nama'] ?? '');
    $jenisKelamin = (string) ($account['jenis_kelamin'] ?? '');
    $profileExists = $account['guru_id'] !== null;

    if (!$profileExists) {
        $error = 'Data guru untuk akun ini tidak ditemukan.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (
            !is_string($csrfToken)
            || empty($_SESSION['csrf_settings_guru'])
            || !hash_equals((string) $_SESSION['csrf_settings_guru'], $csrfToken)
        ) {
            $error = 'Sesi formulir tidak valid. Silakan coba lagi.';
        } else {
            unset($_SESSION['csrf_settings_guru']);
            $username = trim((string) ($_POST['username'] ?? ''));
            $nip = trim((string) ($_POST['nip'] ?? ''));
            $nama = trim((string) ($_POST['nama'] ?? ''));
            $jenisKelamin = (string) ($_POST['jenis_kelamin'] ?? '');
            $password = (string) ($_POST['password'] ?? '');

            if ($username === '' || $nip === '' || $nama === '') {
                $error = 'Username, NIP, dan nama wajib diisi.';
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
                $nipStmt = mysqli_prepare(
                    $koneksi,
                    'SELECT user_id FROM guru WHERE nip = ? AND user_id <> ? LIMIT 1'
                );
                mysqli_stmt_bind_param($nipStmt, 'si', $nip, $userId);
                mysqli_stmt_execute($nipStmt);
                $nipResult = mysqli_stmt_get_result($nipStmt);
                $nipTaken = $nipResult && mysqli_fetch_assoc($nipResult);
                mysqli_stmt_close($nipStmt);

                if ($nipTaken) {
                    $error = 'NIP sudah digunakan oleh guru lain.';
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

                $updateGuru = mysqli_prepare(
                    $koneksi,
                    'UPDATE guru SET nip = ?, nama = ?, jenis_kelamin = ? WHERE user_id = ?'
                );
                mysqli_stmt_bind_param($updateGuru, 'sssi', $nip, $nama, $jenisKelamin, $userId);
                mysqli_stmt_execute($updateGuru);
                mysqli_stmt_close($updateGuru);

                mysqli_commit($koneksi);
                $transactionStarted = false;
                $_SESSION['username'] = $username;
                $_SESSION['nama'] = $nama;
                $success = 'Data guru berhasil diperbarui.';
            }
        }
    }
} catch (mysqli_sql_exception $exception) {
    if ($transactionStarted) {
        mysqli_rollback($koneksi);
    }
    error_log('Pengaturan guru gagal disimpan: ' . $exception->getMessage());
    $error = 'Data guru gagal disimpan. Periksa kembali data yang dimasukkan.';
}

$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_settings_guru'] = $csrfToken;
$esc = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">    }
</head>
<body class="bg-light py-5">
    <main class="container" style="max-width: 700px;">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h1 class="h5 mb-0">Form Edit User (Guru)</h1>
            </div>
            <div class="card-body">
                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= $esc($error) ?></div>
                <?php endif; ?>
                <?php if ($success !== ''): ?>
                    <div class="alert alert-success" role="status"><?= $esc($success) ?></div>
                <?php endif; ?>

                <?php if ($profileExists): ?>
                    <form action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken) ?>">
                        <h2 class="h5 fw-bold mb-3">Data Profil Guru</h2>
                        <div class="mb-3">
                            <label for="nip" class="form-label">NIP:</label>
                            <input type="text" class="form-control" id="nip" name="nip"
                                   value="<?= $esc($nip) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap:</label>
                            <input type="text" class="form-control" id="nama" name="nama"
                                   value="<?= $esc($nama) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label d-block">Jenis Kelamin:</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="lakiLaki"
                                       value="L" <?= $jenisKelamin === 'L' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="lakiLaki">Laki-laki</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="perempuan"
                                       value="P" <?= $jenisKelamin === 'P' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="perempuan">Perempuan</label>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h2 class="h5 fw-bold mb-3">Data Kredensial Akun</h2>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" class="form-control" id="username" name="username"
                                   value="<?= $esc($username) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password Baru:</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="8"
                                   placeholder="Kosongkan jika tidak ingin mengubah password">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Simpan Data</button>
                            <a href="../../daftar_guru2.php" class="btn btn-secondary">Kembali</a>

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
