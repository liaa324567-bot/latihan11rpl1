<?php
require_once 'koneksi.php';

session_start();

$username = trim($_POST['username'] ?? '');
$pass = $_POST['password'] ?? '';

if ($username === '' || $pass === '') {
    $_SESSION['error_login'] = 'Username dan password wajib diisi.';
    header('Location: login.php');
    exit;
}

$sql = "SELECT users.*, COALESCE(siswa.nama, guru.nama) AS nama, siswa.kelas
        FROM users
        LEFT JOIN siswa ON users.id = siswa.user_id
        LEFT JOIN guru ON users.id = guru.user_id
        WHERE users.username = ?";

$stmt = mysqli_prepare($koneksi, $sql);
if (!$stmt) {
    $_SESSION['error_login'] = 'Terjadi kesalahan saat login.';
    header('Location: login.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($query);

$passwordValid = $user && password_verify($pass, $user['password']);

if ($user && !$passwordValid && password_get_info($user['password'])['algoName'] === 'unknown' && hash_equals($user['password'], $pass)) {
    $passwordHash = password_hash($pass, PASSWORD_DEFAULT);
    $userId = (int) $user['id'];
    $storedPassword = $user['password'];
    $migrationStmt = mysqli_prepare(
        $koneksi,
        'UPDATE users SET password = ? WHERE id = ? AND password = ?'
    );

    if (!$migrationStmt) {
        $_SESSION['error_login'] = 'Terjadi kesalahan saat login. Silakan coba lagi.';
        header('Location: login.php');
        exit;
    }

    mysqli_stmt_bind_param($migrationStmt, 'sis', $passwordHash, $userId, $storedPassword);
    mysqli_stmt_execute($migrationStmt);

    if (mysqli_stmt_affected_rows($migrationStmt) !== 1) {
        $_SESSION['error_login'] = 'Terjadi kesalahan saat login. Silakan coba lagi.';
        header('Location: login.php');
        exit;
    }

    $passwordValid = true;
}

if ($user && $passwordValid) {
    session_regenerate_id(true);
    $_SESSION['id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nama'] = $user['nama'] ?? '';
    $role = strtolower(trim((string)($user['role'] ?? '')));
    $_SESSION['role'] = $role;
    $_SESSION['kelas'] = $user['kelas'] ?? null;
    $_SESSION['login'] = true;
    unset($_SESSION['error_login']);

    switch ($role) {
        case 'admin':
            header('Location: table.php');
            exit;
        case 'guru':
            header('Location: projek%20tugas%20besar/daftar_guru2.php');
            exit;
        case 'siswa':
            header('Location: projek%20tugas%20besar/daftar_siswa2.php');
            exit;
        default:
            session_unset();
            session_destroy();
            header('Location: login.php');
            exit;
    }
}

$_SESSION['error_login'] = 'Username atau password salah!';
header('Location: login.php');
exit;