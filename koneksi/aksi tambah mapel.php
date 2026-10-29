<?php
// 1. Hubungkan ke database
require_once 'koneksi.php';

// 2. Pastikan data dikirim menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ambil data dari form dan bersihkan spasi di awal/akhir
    $kode_mapel = isset($_POST['kode_mapel']) ? trim($_POST['kode_mapel']) : '';
    $nama_mapel = isset($_POST['nama_mapel']) ? trim($_POST['nama_mapel']) : '';
    $guru_id = isset($_POST['id_guru']) ? trim($_POST['id_guru']) : '';

    // Validasi: Pastikan semua bidang tidak kosong
    if (!empty($kode_mapel) && !empty($nama_mapel) && !empty($guru_id)) {
        
        // Gunakan Prepared Statement untuk keamanan dari SQL Injection
        $sql = "INSERT INTO mapel (kode_mapel, nama_mapel, guru_id) VALUES (?, ?, ?)";
        
        $stmt = mysqli_prepare($koneksi, $sql);

        if ($stmt) {
            // Tipe data parameter: s = string, i = integer
            // Jika id_guru bertipe integer di database, gunakan "ssi"
            mysqli_stmt_bind_param($stmt, "ssi", $kode_mapel, $nama_mapel, $guru_id);

            // Eksekusi query
            if (mysqli_stmt_execute($stmt)) {
                // Berhasil disimpan -> Redirect kembali ke halaman daftar mata pelajaran
                header("Location: tambah_mapel.php?status=sukses");
                exit();
            } else {
                // Gagal eksekusi query (misal: kode_mapel sudah ada / DUPLICATE KEY)
                echo "Gagal menyimpan data: " . mysqli_error($koneksi);
            }

            // Tutup statement
            mysqli_stmt_close($stmt);
        } else {
            echo "Gagal mempersiapkan query: " . mysqli_error($koneksi);
        }

    } else {
        // Jika ada bidang yang belum terisi
        echo "Harap lengkapi semua data form terlebih dahulu!";
    }

} else {
    // Jika file ini diakses langsung tanpa submit form -> Redirect ke form tambah
    header("Location: mapel.php");
    exit();
}
?>