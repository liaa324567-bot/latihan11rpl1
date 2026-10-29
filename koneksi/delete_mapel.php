<?php
require_once 'koneksi.php';

// Cek apakah parameter id tersedia di URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Menghindari SQL Injection
    $id = mysqli_real_escape_string($koneksi, $id);

    // Query untuk menghapus data mata pelajaran berdasarkan ID
    $query = "DELETE FROM mapel WHERE id = '$id'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        // Redirect kembali ke halaman mapel.php jika berhasil
        header("Location: tambah_mapel.php?status=success_delete");
        exit();
    } else {
        // Tampilkan pesan error jika gagal
        echo "Gagal menghapus data: " . mysqli_error($koneksi);
    }
} else {
    // Jika tidak ada ID di URL, kembalikan ke mapel.php
    header("Location: tambah_mapel.php");
    exit();
}
?><?php
require_once 'koneksi.php';

// Cek apakah parameter id tersedia di URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Menghindari SQL Injection
    $id = mysqli_real_escape_string($koneksi, $id);

    // Query untuk menghapus data mata pelajaran berdasarkan ID
    $query = "DELETE FROM mapel WHERE id = '$id'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        // Redirect kembali ke halaman mapel.php jika berhasil
        header("Location: tambah_mapel.php?status=success_delete");
        exit();
    } else {
        // Tampilkan pesan error jika gagal
        echo "Gagal menghapus data: " . mysqli_error($koneksi);
    }
} else {
    // Jika tidak ada ID di URL, kembalikan ke mapel.php
    header("Location: tambah_mapel.php");
    exit();
}
?>