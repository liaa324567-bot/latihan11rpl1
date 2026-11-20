<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    empty($_SESSION['login'])
    || empty($_SESSION['id'])
    || strtolower((string) ($_SESSION['role'] ?? '')) !== 'guru'
) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode permintaan tidak diizinkan.');
}

require_once __DIR__ . '/../koneksi.php';

$token = isset($_POST['token']) && is_string($_POST['token']) ? $_POST['token'] : '';
if (
    empty($_SESSION['token_hapus_nilai'])
    || !hash_equals((string) $_SESSION['token_hapus_nilai'], $token)
) {
    http_response_code(400);
    exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
}

$nilaiId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$mapelId = filter_input(INPUT_POST, 'mapel_id', FILTER_VALIDATE_INT);
$kelas = isset($_POST['kelas']) && is_string($_POST['kelas'])
    ? trim($_POST['kelas'])
    : '';

if (!$nilaiId || !$mapelId || $kelas === '') {
    http_response_code(400);
    exit('Data penghapusan nilai tidak valid.');
}

$stmt = mysqli_prepare(
    $koneksi,
    'DELETE nilai_rapor
     FROM nilai_rapor
     INNER JOIN siswa ON siswa.id = nilai_rapor.siswa_id
     INNER JOIN mapel ON mapel.id = nilai_rapor.mapel_id
     INNER JOIN guru ON guru.id = mapel.guru_id
     WHERE nilai_rapor.id = ?
        AND nilai_rapor.mapel_id = ?
        AND siswa.kelas = ?
        AND guru.user_id = ?'
);
if (!$stmt) {
    die('Gagal menyiapkan penghapusan nilai: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}

$userId = (int) $_SESSION['id'];
if (!mysqli_stmt_bind_param($stmt, 'iisi', $nilaiId, $mapelId, $kelas, $userId) || !mysqli_stmt_execute($stmt)) {
    die('Gagal menghapus nilai: ' . htmlspecialchars(mysqli_stmt_error($stmt), ENT_QUOTES, 'UTF-8'));
}

$deleted = mysqli_stmt_affected_rows($stmt) > 0;
mysqli_stmt_close($stmt);
if (!$deleted) {
    http_response_code(404);
    exit('Data nilai tidak ditemukan atau bukan milik mata pelajaran Anda.');
}

header('Location: detail_nilai.php?mapel_id=' . $mapelId . '&kelas=' . rawurlencode($kelas));
exit;