<?php

session_start();
require_once('koneksi.php');
require_once('navbar.php');
/** @var mysqli $koneksi */

// QUERY DATABASE
$sql = "SELECT g.*, u.username,
        (SELECT GROUP_CONCAT(DISTINCT m.nama_mapel ORDER BY m.nama_mapel SEPARATOR ', ')
        FROM mapel m
        WHERE m.guru_id = g.id) AS mapel
        FROM guru g
        LEFT JOIN users u ON g.user_id = u.id
        ORDER BY g.id DESC";

// JALANKAN QUERY
$hasil = mysqli_query($koneksi, $sql);
if (!$hasil) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}

// Siapkan array data
$data = [];

// CEK DATA
if (mysqli_num_rows($hasil) > 0) {
    while ($row = mysqli_fetch_assoc($hasil)) {
        $data[] = $row;
    }
} else {
    echo "Tidak ada data";
}
?>

<!-- TAMPILAN HALAMAN -->
<div class="container">

    <!-- Judul -->
    <div class="container center">
        <h1 class="fw-normal display-4">Data Guru</h1>
        <hr class="border border-dark border-2 opacity-100">
    </div>

    <!-- Tabel Data Guru -->
    <table class="table">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Username</th>
                <th scope="col">Nama Guru</th>
                <th scope="col">Mata Pelajaran</th>
                <th scope="col">Jenis Kelamin</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php $no = 1; foreach ($data as $guru) { ?>
                <tr>

                    <td>
                        <?php echo $no++; ?>
                    </td>

                    <td>
                        <?php echo $guru['username']; ?>
                    </td>

                    <td>
                        <?php echo $guru['nama']; ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($guru['mapel'] ?? ''); ?>
                    </td>

                    <td>
                        <?php echo $guru['jenis_kelamin']; ?>
                    </td>

                    <td>
                        <a href="form_edit.php?id=<?php echo $guru['user_id']; ?>" 
                           class="btn btn-warning btn-sm">
                            Edit
                        </a>

                        <a href="aksi_hapus.php?id=<?php echo $guru['id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                            Hapus
                        </a>
                    </td>

                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>

<!-- FOOTER -->
<?php require "footer.php"; ?>