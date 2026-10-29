<?php
require_once '../koneksi.php';
require_once '../navbar.php';

$query = mysqli_query(
    $koneksi,
    'SELECT mengajar.mapel_id, siswa.kelas, mapel.nama_mapel,
    guru.nama AS nama_guru, COUNT(DISTINCT siswa.id) AS jumlah_siswa
    FROM mengajar
    INNER JOIN siswa ON siswa.id = mengajar.siswa_id
    INNER JOIN mapel ON mapel.id = mengajar.mapel_id
    LEFT JOIN guru ON guru.id = mapel.guru_id
    GROUP BY mengajar.mapel_id, siswa.kelas, mapel.nama_mapel, guru.nama
    ORDER BY siswa.kelas, mapel.nama_mapel'
);

if (!$query) {
    die('Gagal memuat daftar mengajar: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}
?>

<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-center">Daftar Guru Mengajar</h1>
        <a href="form_tambah_mengajar.php" class="btn btn-primary">Tambah Mengajar</a>
    </div>

    <?php if (($_GET['status'] ?? '') === 'added') { ?>
        <div class="alert alert-success" role="alert">Data mengajar berhasil ditambahkan.</div>
    <?php } elseif (($_GET['status'] ?? '') === 'updated') { ?>
        <div class="alert alert-success" role="alert">Data mengajar berhasil diperbarui.</div>
    <?php } elseif (($_GET['status'] ?? '') === 'deleted') { ?>
        <div class="alert alert-success" role="alert">Data mengajar berhasil dihapus.</div>
    <?php } ?>

    <!-- Kontrol Atas (Tampilkan data per halaman & Cari) -->
    <div class="row align-items-center mb-3">
        <div class="col-md-6 d-flex align-items-center gap-2 mb-2 mb-md-0">
            <span>Tampilkan</span>
            <select class="form-select form-select-sm w-auto" aria-label="Data per halaman">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span>data per halaman</span>
        </div>
        <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-2">
            <label for="cariInput" class="form-label mb-0">Cari:</label>
            <input type="text" id="cariInput" class="form-control form-control-sm w-auto" placeholder="">
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="width: 5%;">No <span class="text-muted">↕</span></th>
                    <th scope="col">Nama Guru <span class="text-muted">↕</span></th>
                    <th scope="col">Kelas <span class="text-muted">↕</span></th>
                    <th scope="col">Nama Mata Pelajaran <span class="text-muted">↕</span></th>
                    <th scope="col">Jumlah Siswa <span class="text-muted">↕</span></th>
                    <th scope="col" style="width: 15%;">Aksi <span class="text-muted">↕</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($query) === 0) { ?>
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data mengajar.</td>
                    </tr>
                <?php } else { ?>
                    <?php $nomor = 1; ?>
                    <?php while ($mengajar = mysqli_fetch_assoc($query)) { ?>
                        <tr>
                            <td><?= $nomor++ ?></td>
                            <td><?= htmlspecialchars($mengajar['nama_guru'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($mengajar['kelas'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($mengajar['nama_mapel'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) $mengajar['jumlah_siswa'] ?></td>
                            <td class="text-nowrap">
                                <a href="form_edit_mengajar.php?mapel_id=<?= (int) $mengajar['mapel_id'] ?>&amp;kelas=<?= rawurlencode($mengajar['kelas']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                <form method="post" action="hapus_mengajar.php" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus data mengajar ini?');">
                                    <input type="hidden" name="mapel_id" value="<?= (int) $mengajar['mapel_id'] ?>">
                                    <input type="hidden" name="kelas" value="<?= htmlspecialchars($mengajar['kelas'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Bagian Bawah (Info & Pagination) -->
    <div class="row align-items-center mt-3">
        <div class="col-md-6 text-muted mb-2 mb-md-0">
            Menampilkan halaman 1 dari 1
        </div>
        <div class="col-md-6 d-flex justify-content-md-end">
            <nav aria-label="Navigasi Halaman">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled">
                        <span class="page-link">Sebelumnya</span>
                    </li>
                    <li class="page-item active" aria-current="page">
                        <span class="page-link">1</span>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">Selanjutnya</span>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</main>

<?php require_once '../footer.php'; ?>