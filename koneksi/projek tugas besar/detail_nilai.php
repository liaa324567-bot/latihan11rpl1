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

require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../navbar.php';


$mapelId = filter_input(INPUT_GET, 'mapel_id', FILTER_VALIDATE_INT);
$kelas = isset($_GET['kelas']) && is_string($_GET['kelas']) ? trim($_GET['kelas']) : '';
if (!$mapelId || $kelas === '') {
    header('Location: daftar_nilai2.php');
    exit;
}

$userId = (int) $_SESSION['id'];
$mapelStmt = mysqli_prepare(
    $koneksi,
    'SELECT mapel.nama_mapel
     FROM mapel
     INNER JOIN guru ON guru.id = mapel.guru_id
     WHERE mapel.id = ? AND guru.user_id = ?
     LIMIT 1'
);
if (!$mapelStmt) {
    die('Gagal menyiapkan mata pelajaran: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}
if (!mysqli_stmt_bind_param($mapelStmt, 'ii', $mapelId, $userId) || !mysqli_stmt_execute($mapelStmt)) {
    die('Gagal memuat mata pelajaran: ' . htmlspecialchars(mysqli_stmt_error($mapelStmt), ENT_QUOTES, 'UTF-8'));
}
$mapelResult = mysqli_stmt_get_result($mapelStmt);
if (!$mapelResult) {
    die('Gagal membaca mata pelajaran: ' . htmlspecialchars(mysqli_stmt_error($mapelStmt), ENT_QUOTES, 'UTF-8'));
}
$mapel = mysqli_fetch_assoc($mapelResult);
mysqli_stmt_close($mapelStmt);
if (!$mapel) {
    header('Location: daftar_nilai2.php');
    exit;
}

$nilaiStmt = mysqli_prepare(
    $koneksi,
    'SELECT siswa.id AS siswa_id, siswa.nama, nilai_rapor.id AS nilai_id, nilai_rapor.nilai_kkm,
            nilai_rapor.nilai_pengetahuan, nilai_rapor.nilai_keterampilan,
            nilai_rapor.Keterangan
     FROM mengajar
     INNER JOIN siswa ON siswa.id = mengajar.siswa_id
     LEFT JOIN nilai_rapor
        ON nilai_rapor.siswa_id = siswa.id
        AND nilai_rapor.mapel_id = mengajar.mapel_id
     WHERE mengajar.mapel_id = ? AND siswa.kelas = ?
     ORDER BY siswa.nama'
);
if (!$nilaiStmt) {
    die('Gagal menyiapkan data nilai: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}
if (!mysqli_stmt_bind_param($nilaiStmt, 'is', $mapelId, $kelas) || !mysqli_stmt_execute($nilaiStmt)) {
    die('Gagal memuat data nilai: ' . htmlspecialchars(mysqli_stmt_error($nilaiStmt), ENT_QUOTES, 'UTF-8'));
}
$nilaiResult = mysqli_stmt_get_result($nilaiStmt);
if (!$nilaiResult) {
    die('Gagal membaca data nilai: ' . htmlspecialchars(mysqli_stmt_error($nilaiStmt), ENT_QUOTES, 'UTF-8'));
}
$nilai = [];
while ($row = mysqli_fetch_assoc($nilaiResult)) {
    $row['nilai_akhir'] = $row['nilai_pengetahuan'] !== null && $row['nilai_keterampilan'] !== null
        ? ((float) $row['nilai_pengetahuan'] + (float) $row['nilai_keterampilan']) / 2
        : null;
    $nilai[] = $row;
}
mysqli_stmt_close($nilaiStmt);

$kelasTampil = htmlspecialchars($kelas, ENT_QUOTES, 'UTF-8');
$mapelTampil = htmlspecialchars($mapel['nama_mapel'], ENT_QUOTES, 'UTF-8');
if (empty($_SESSION['token_hapus_nilai'])) {
    $_SESSION['token_hapus_nilai'] = bin2hex(random_bytes(32));
}
$tokenHapusNilai = $_SESSION['token_hapus_nilai'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Nilai Siswa</title>
    <!-- CSS Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">
        
        <!-- Judul dan Keterangan Kelas/Mapel -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h3 class="card-title mb-3">Daftar Nilai Siswa</h3>
                <p class="text-muted mb-1">Kelas: <?= $kelasTampil ?> | Mata Pelajaran: <?= $mapelTampil ?></p>
            </div>
        </div>

        <!-- Tabel Data Nilai -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <!-- Kotak Pencarian Sederhana -->
                    <div class="input-group" style="width: 250px;">
                        <input type="text" class="form-control form-control-sm" placeholder="Cari siswa...">
                        <button class="btn btn-outline-secondary btn-sm" type="button">Cari</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 25%;">Nama Siswa</th>
                                <th style="width: 10%;">KKM</th>
                                <th style="width: 12%;">Pengetahuan</th>
                                <th style="width: 12%;">Keterampilan</th>
                                <th style="width: 12%;">Nilai Akhir</th>
                                <th style="width: 14%;">Keterangan</th>
                                <th style="width: 15%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($nilai): ?>
                                <?php foreach ($nilai as $nomor => $row): ?>
                                    <tr>
                                        <td class="text-center"><?= $nomor + 1 ?></td>
                                        <td><?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-center"><?= $row['nilai_kkm'] !== null ? htmlspecialchars((string) $row['nilai_kkm'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                        <td class="text-center"><span class="badge bg-primary"><?= $row['nilai_pengetahuan'] !== null ? htmlspecialchars((string) $row['nilai_pengetahuan'], ENT_QUOTES, 'UTF-8') : '-' ?></span></td>
                                        <td class="text-center"><span class="badge bg-primary"><?= $row['nilai_keterampilan'] !== null ? htmlspecialchars((string) $row['nilai_keterampilan'], ENT_QUOTES, 'UTF-8') : '-' ?></span></td>
                                        <td class="text-center"><span class="badge bg-success"><?= $row['nilai_akhir'] !== null ? ((int) $row['nilai_akhir'] == $row['nilai_akhir'] ? (int) $row['nilai_akhir'] : number_format($row['nilai_akhir'], 2)) : '-' ?></span></td>
                                        <td class="text-center"><?= $row['nilai_id'] === null || $row['nilai_pengetahuan'] === null || $row['nilai_keterampilan'] === null ? 'Belum diisi' : htmlspecialchars((string) ($row['Keterangan'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-center">
                                            <?php if ($row['nilai_id'] !== null): ?>
                                                <a href="edit_detail_nilai.php?siswa_id=<?= (int) $row['siswa_id'] ?>&amp;mapel_id=<?= (int) $mapelId ?>&amp;kelas=<?= rawurlencode($kelas) ?>&amp;id=<?= (int) $row['nilai_id'] ?>" class="btn btn-warning btn-sm text-white px-2 py-0">Edit</a>
                                                <form method="post" action="hapus_detail_nilai.php" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                                    <input type="hidden" name="token" value="<?= htmlspecialchars($tokenHapusNilai, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="id" value="<?= (int) $row['nilai_id'] ?>">
                                                    <input type="hidden" name="mapel_id" value="<?= (int) $mapelId ?>">
                                                    <input type="hidden" name="kelas" value="<?= htmlspecialchars($kelas, ENT_QUOTES, 'UTF-8') ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm px-2 py-0">Hapus</button>
                                                </form>
                                            <?php else: ?>
                                                <a href="edit_detail_nilai.php?siswa_id=<?= (int) $row['siswa_id'] ?>&amp;mapel_id=<?= (int) $mapelId ?>&amp;kelas=<?= rawurlencode($kelas) ?>" class="btn btn-primary btn-sm px-2 py-0">Tambah</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada data nilai untuk kelas dan mata pelajaran ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>