<?php
// 1. Hubungkan ke database dan tampilkan navbar
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/navbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Tes apakah tombol mengirim data
    var_dump($_POST); 
    
    // 2. Tes eksekusi query
    $query = "INSERT INTO mapel (kode_mapel, nama_mapel, guru_id) VALUES ('".$_POST['kode_mapel']."', '".$_POST['nama_mapel']."', '".$_POST['guru_id']."')";
    
    $eksekusi = mysqli_query($koneksi, $query);
    
    if (!$eksekusi) {
        die("MySQL Error: " . mysqli_error($koneksi));
    } else {
        echo "BERHASIL MASUK KE DATABASE!";
    }
    exit();
}

// 2. Query untuk mengambil data mapel gabung (JOIN) dengan tabel guru
$query = mysqli_query($koneksi, "SELECT mapel.*, guru.nama AS nama_guru FROM mapel LEFT JOIN guru ON mapel.guru_id = guru.id ORDER BY mapel.id DESC");
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">Data Mata Pelajaran (Mapel)</h2>
        <!-- Tombol Tambah Mapel -->
        <a href="mapel.php" class="btn btn-primary">+ Tambah Mapel Baru</a>
    </div>

    <!-- Tabel Data Mapel -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th style="width: 8%;">ID</th>
                    <th style="width: 20%;">Kode Mapel</th>
                    <th>Nama Mapel</th>
                    <th>Nama Guru</th>
                    <th style="width: 18%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($query) > 0) {
                    while ($mapel = mysqli_fetch_assoc($query)) {
                ?>
                        <tr>
                            <td class="text-center"><?= htmlspecialchars($mapel['id']); ?></td>
                            <td><?= htmlspecialchars($mapel['kode_mapel']); ?></td>
                            <td><?= htmlspecialchars($mapel['nama_mapel']); ?></td>
                            <td><?= htmlspecialchars($mapel['nama_guru'] ?? 'Belum ada guru'); ?></td>
                            <td class="text-center">
                                <!-- Tombol Edit -->
                                <a href="aksi_edit_mapel.php?id=<?= $mapel['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                
                                <!-- Tombol Hapus -->
                                <a href="delete_mapel.php?id=<?= $mapel['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus mapel ini?')">Hapus</a>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>Belum ada data mapel.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Tampilkan footer
require_once __DIR__ . '/footer.php';
?>