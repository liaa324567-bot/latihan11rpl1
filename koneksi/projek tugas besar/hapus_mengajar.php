<?php
require_once '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: daftar_mengajar.php');
    exit;
}

$mapelId = filter_var($_POST['mapel_id'] ?? null, FILTER_VALIDATE_INT);
$kelas = trim($_POST['kelas'] ?? '');
if (!$mapelId || $mapelId < 1 || $kelas === '') {
    http_response_code(400);
    exit('Data mata pelajaran atau kelas tidak valid.');
}

$stmt = mysqli_prepare(
    $koneksi,
    'DELETE mengajar
     FROM mengajar
     INNER JOIN siswa ON siswa.id = mengajar.siswa_id
     WHERE mengajar.mapel_id = ? AND siswa.kelas = ?'
);
if (!$stmt) {
    die('Gagal menyiapkan penghapusan data mengajar: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}

mysqli_stmt_bind_param($stmt, 'is', $mapelId, $kelas);
if (!mysqli_stmt_execute($stmt)) {
    die('Gagal menghapus data mengajar: ' . htmlspecialchars(mysqli_stmt_error($stmt), ENT_QUOTES, 'UTF-8'));
}

if (mysqli_stmt_affected_rows($stmt) === 0) {
    mysqli_stmt_close($stmt);
    http_response_code(404);
    exit('Data mengajar tidak ditemukan.');
}

mysqli_stmt_close($stmt);
header('Location: daftar_mengajar.php?status=deleted');
exit;
