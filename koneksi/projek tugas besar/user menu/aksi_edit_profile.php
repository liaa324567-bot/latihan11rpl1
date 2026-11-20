<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['login']) || empty($_SESSION['id'])) {
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: edit_profile.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (
    !is_string($csrfToken)
    || empty($_SESSION['csrf_profile'])
    || !hash_equals((string) $_SESSION['csrf_profile'], $csrfToken)
) {
    $_SESSION['profile_error'] = 'Sesi formulir tidak valid. Silakan coba lagi.';
    header('Location: edit_profile.php');
    exit;
}
unset($_SESSION['csrf_profile']);

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$userId = (int) $_SESSION['id'];
$error = '';

if ($username === '') {
    $error = 'Username wajib diisi.';
} elseif ($password !== '' && strlen($password) < 8) {
    $error = 'Password baru minimal 8 karakter.';
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$transactionStarted = false;

try {
    if ($error === '') {
        $roleStmt = mysqli_prepare($koneksi, 'SELECT role FROM users WHERE id = ? LIMIT 1');
        mysqli_stmt_bind_param($roleStmt, 'i', $userId);
        mysqli_stmt_execute($roleStmt);
        $roleResult = mysqli_stmt_get_result($roleStmt);
        $user = $roleResult ? mysqli_fetch_assoc($roleResult) : null;
        mysqli_stmt_close($roleStmt);

        if (!$user) {
            $error = 'Akun tidak ditemukan. Silakan login kembali.';
        } else {
            $role = strtolower((string) $user['role']);
        }
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

    $isSiswa = ($role ?? '') === 'siswa';
    $isGuru = ($role ?? '') === 'guru';
    $nama = trim((string) ($_POST['nama'] ?? ''));
    $jenisKelamin = (string) ($_POST['jenis_kelamin'] ?? '');

    if ($error === '' && !$isSiswa && !$isGuru && $role !== 'admin') {
        $error = 'Role akun tidak dikenal.';
    }

    if ($error === '' && ($isSiswa || $isGuru)) {
        if ($nama === '') {
            $error = 'Nama wajib diisi.';
        } elseif (!in_array($jenisKelamin, ['L', 'P'], true)) {
            $error = 'Pilih jenis kelamin yang valid.';
        }
    }

    if ($error === '' && $isSiswa) {
        $nis = trim((string) ($_POST['nis'] ?? ''));
        $kelas = trim((string) ($_POST['kelas'] ?? ''));
        if ($nis === '' || $kelas === '') {
            $error = 'NIS dan kelas wajib diisi.';
        } else {
            $profileStmt = mysqli_prepare($koneksi, 'SELECT user_id FROM siswa WHERE user_id = ? LIMIT 1');
            mysqli_stmt_bind_param($profileStmt, 'i', $userId);
            mysqli_stmt_execute($profileStmt);
            $profileResult = mysqli_stmt_get_result($profileStmt);
            $profileExists = $profileResult && mysqli_fetch_assoc($profileResult);
            mysqli_stmt_close($profileStmt);
            if (!$profileExists) {
                $error = 'Data siswa untuk akun ini tidak ditemukan.';
            }
        }
    } elseif ($error === '' && $isGuru) {
        $nip = trim((string) ($_POST['nip'] ?? ''));
        if ($nip === '') {
            $error = 'NIP wajib diisi.';
        } else {
            $profileStmt = mysqli_prepare($koneksi, 'SELECT user_id FROM guru WHERE user_id = ? LIMIT 1');
            mysqli_stmt_bind_param($profileStmt, 'i', $userId);
            mysqli_stmt_execute($profileStmt);
            $profileResult = mysqli_stmt_get_result($profileStmt);
            $profileExists = $profileResult && mysqli_fetch_assoc($profileResult);
            mysqli_stmt_close($profileStmt);
            if (!$profileExists) {
                $error = 'Data guru untuk akun ini tidak ditemukan.';
            }
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
            $updateUser = mysqli_prepare($koneksi, 'UPDATE users SET username = ? WHERE id = ?');
            mysqli_stmt_bind_param($updateUser, 'si', $username, $userId);
        }
        mysqli_stmt_execute($updateUser);
        mysqli_stmt_close($updateUser);

        if ($isSiswa) {
            $updateProfile = mysqli_prepare(
                $koneksi,
                'UPDATE siswa SET nis = ?, nama = ?, kelas = ?, jenis_kelamin = ? WHERE user_id = ?'
            );
            mysqli_stmt_bind_param($updateProfile, 'ssssi', $nis, $nama, $kelas, $jenisKelamin, $userId);
            mysqli_stmt_execute($updateProfile);
            mysqli_stmt_close($updateProfile);
        } elseif ($isGuru) {
            $updateProfile = mysqli_prepare(
                $koneksi,
                'UPDATE guru SET nip = ?, nama = ?, jenis_kelamin = ? WHERE user_id = ?'
            );
            mysqli_stmt_bind_param($updateProfile, 'sssi', $nip, $nama, $jenisKelamin, $userId);
            mysqli_stmt_execute($updateProfile);
            mysqli_stmt_close($updateProfile);
        }

        mysqli_commit($koneksi);
        $transactionStarted = false;
        $_SESSION['username'] = $username;
        $_SESSION['nama'] = ($isSiswa || $isGuru) ? $nama : $username;
        $_SESSION['profile_success'] = 'Profil berhasil diperbarui.';
        header('Location: profile.php');
        exit;
    }
} catch (mysqli_sql_exception $exception) {
    if ($transactionStarted) {
        mysqli_rollback($koneksi);
    }
    $error = 'Profil gagal disimpan. Periksa kembali data yang dimasukkan.';
}

$_SESSION['profile_error'] = $error;
header('Location: edit_profile.php');
exit;
