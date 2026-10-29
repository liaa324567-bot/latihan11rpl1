<?php
require_once __DIR__ . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah_mapel.php');
    exit();
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$guru_id = filter_var($_POST['id_guru'] ?? null, FILTER_VALIDATE_INT);
$kode_mapel = trim($_POST['kode_mapel'] ?? '');
$nama_mapel = trim($_POST['nama_mapel'] ?? '');

if (!$id || !$guru_id || $kode_mapel === '' || $nama_mapel === '') {
    exit('Data edit tidak lengkap atau tidak valid.');
}

$sql = 'UPDATE mapel SET kode_mapel = ?, nama_mapel = ?, guru_id = ? WHERE id = ?';
$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    exit('Gagal mempersiapkan query: ' . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, 'ssii', $kode_mapel, $nama_mapel, $guru_id, $id);

if (!mysqli_stmt_execute($stmt)) {
    exit('Gagal memperbarui data: ' . mysqli_stmt_error($stmt));
}

mysqli_stmt_close($stmt);
header('Location: tambah_mapel.php?status=sukses_edit');
exit();