<?php
/** @var mysqli $koneksi */

require_once(__DIR__ . '/../navbar.php');
require_once(__DIR__ . '/../koneksi.php');

// QUERY DATABASE
$sql = "SELECT siswa.id, siswa.nama, siswa.kelas, users.username, users.role
        FROM siswa
        LEFT JOIN users ON siswa.user_id = users.id
        WHERE REPLACE(REPLACE(REPLACE(UPPER(TRIM(siswa.kelas)), ' ', ''), '-', ''), '_', '') = 'XIRPL1'
        ORDER BY siswa.id DESC";

// JALANKAN QUERY
$hasil = mysqli_query($koneksi, $sql);

// Siapkan array data
$data = [];

// CEK DATA
if (mysqli_num_rows($hasil) > 0) {
    while ($row = mysqli_fetch_assoc($hasil)) {
        $data[] = $row;
    }
}
?>

<!-- TAMPILAN HALAMAN -->
<div class="container mt-4">

    <!-- Judul -->
    <div class="text-center mb-4">
        <h1>DATA SISWA KELAS XI RPL 1</h1>
        <hr class="border border-dark border-2 opacity-100">
    </div>

    <!-- Tabel Data Siswa -->
    <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th scope="col">No</th>
                <th scope="col">Username</th>
                <th scope="col">Role</th>
                <th scope="col">Nama</th>
                <th scope="col">Kelas</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($data)): ?>
                <?php $no = 1; ?>
                <?php foreach ($data as $siswa) { ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars((string)($siswa['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)($siswa['role'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($siswa['kelas'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php } ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data siswa kelas XI RPL 1.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

</div>

<!-- FOOTER -->

<?php require __DIR__ . '/../footer.php'; ?>