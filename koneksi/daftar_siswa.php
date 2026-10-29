<?php
/** @var mysqli $koneksi */
require_once('navbar.php');
require_once('koneksi.php');

// QUERY DATABASE
$sql = "SELECT * FROM siswa ORDER BY id DESC";

// JALANKAN QUERY
$hasil = mysqli_query($koneksi, $sql);

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
        <h1 class="fw-normal display-4">Data Siswa</h1>
        <hr class="border border-dark border-2 opacity-100">
    </div>

    <!-- Tabel Data Siswa -->
    <table class="table">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">NIS</th>
                <th scope="col">Nama Siswa</th>
                <th scope="col">Kelas</th>
                <th scope="col">Jenis Kelamin</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($data as $siswa) { ?>
                <tr>

                    <td>
                        <?php echo $siswa['id']; ?>
                    </td>

                    <td>
                        <?php echo $siswa['nis']; ?>
                    </td>

                    <td>
                        <?php echo $siswa['nama']; ?>
                    </td>

                    <td>
                        <?php echo $siswa['kelas']; ?>
                    </td>

                    <td>
                        <?php echo $siswa['jenis_kelamin']; ?>
                    </td>

                    <td>
                        <a href="form_edit.php?id=<?php echo $siswa['user_id']; ?>" 
                           class="btn btn-warning btn-sm">
                            Edit
                        </a>

                        <a href="aksi_hapus.php?id=<?php echo $siswa['user_id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                            Delete
                        </a>
                    </td>

                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>

<!-- FOOTER -->
<?php require "footer.php"; ?>