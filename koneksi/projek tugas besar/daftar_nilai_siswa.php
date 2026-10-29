<?php
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../navbar.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    empty($_SESSION['login'])
    || empty($_SESSION['id'])
    || strtolower((string) ($_SESSION['role'] ?? '')) !== 'siswa'
) {
    header('Location: ../login.php');
    exit;
}

$userId = (int) $_SESSION['id'];
$studentStmt = mysqli_prepare(
    $koneksi,
    'SELECT id, nis, nama, kelas FROM siswa WHERE user_id = ? LIMIT 1'
);
if (!$studentStmt) {
    die('Gagal menyiapkan data siswa: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}

mysqli_stmt_bind_param($studentStmt, 'i', $userId);
if (!mysqli_stmt_execute($studentStmt)) {
    die('Gagal memuat data siswa: ' . htmlspecialchars(mysqli_stmt_error($studentStmt), ENT_QUOTES, 'UTF-8'));
}

$studentResult = mysqli_stmt_get_result($studentStmt);
$siswa = mysqli_fetch_assoc($studentResult);
mysqli_stmt_close($studentStmt);

$nilai = [];
$totalRataRata = 0;
$jumlahRataRata = 0;

if ($siswa) {
    $nilaiStmt = mysqli_prepare(
        $koneksi,
        'SELECT mapel.nama_mapel, nilai_rapor.nilai_kkm,
                nilai_rapor.nilai_pengetahuan, nilai_rapor.nilai_keterampilan,
                nilai_rapor.Keterangan
         FROM nilai_rapor
         INNER JOIN mapel ON mapel.id = nilai_rapor.mapel_id
         WHERE nilai_rapor.siswa_id = ?
         ORDER BY mapel.nama_mapel'
    );
    if (!$nilaiStmt) {
        die('Gagal menyiapkan data nilai: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
    }

    $siswaId = (int) $siswa['id'];
    mysqli_stmt_bind_param($nilaiStmt, 'i', $siswaId);
    if (!mysqli_stmt_execute($nilaiStmt)) {
        die('Gagal memuat data nilai: ' . htmlspecialchars(mysqli_stmt_error($nilaiStmt), ENT_QUOTES, 'UTF-8'));
    }

    $nilaiResult = mysqli_stmt_get_result($nilaiStmt);
    while ($row = mysqli_fetch_assoc($nilaiResult)) {
        $row['rata_rata'] = null;
        if ($row['nilai_pengetahuan'] !== null && $row['nilai_keterampilan'] !== null) {
            $row['rata_rata'] = (
                (float) $row['nilai_pengetahuan'] + (float) $row['nilai_keterampilan']
            ) / 2;
            $totalRataRata += $row['rata_rata'];
            $jumlahRataRata++;
        }
        $nilai[] = $row;
    }
    mysqli_stmt_close($nilaiStmt);
}

$rataRataKeseluruhan = $jumlahRataRata > 0
    ? number_format($totalRataRata / $jumlahRataRata, 2)
    : '-';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nilai Siswa</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Konten Utama (Tanpa Navbar) -->
    <div class="container my-5">
        
        <!-- Header Identitas Siswa -->
        <div class="text-center mb-4">
            <h2 class="fw-bold">Nilai Siswa</h2>
            <?php if ($siswa): ?>
                <h4 class="text-dark"><?= htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8') ?></h4>
                <p class="text-muted mb-1">NIS: <strong><?= htmlspecialchars($siswa['nis'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                <p class="text-muted">Kelas: <strong><?= htmlspecialchars($siswa['kelas'], ENT_QUOTES, 'UTF-8') ?></strong></p>
            <?php else: ?>
                <p class="text-muted">Data siswa untuk akun ini tidak ditemukan.</p>
            <?php endif; ?>
        </div>

        <!-- Card Rata-rata Nilai -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-5">
                <div class="card text-center shadow-sm border">
                    <div class="card-body py-4">
                        <h6 class="text-muted mb-2">Rata-rata Nilai</h6>
                        <h1 class="display-4 fw-normal text-dark mb-0"><?= htmlspecialchars($rataRataKeseluruhan, ENT_QUOTES, 'UTF-8') ?></h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Nilai -->
        <div class="card shadow-sm border">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-4">Daftar Nilai</h5>
                
                <!-- Filter & Pencarian Tabel -->
                <div class="row justify-content-between align-items-center mb-3">
                    <div class="col-auto d-flex align-items-center">
                        <span class="me-2 text-muted">Tampilkan</span>
                        <select class="form-select form-select-sm d-inline-block w-auto">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="ms-2 text-muted">data per halaman</span>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <label class="me-2 text-muted">Cari:</label>
                        <input type="text" class="form-control form-control-sm" placeholder="">
                    </div>
                </div>

                <!-- Tabel Data -->
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">No ↕</th>
                                <th>Mata Pelajaran ↕</th>
                                <th style="width: 12%;">KKM ↕</th>
                                <th style="width: 15%;">Pengetahuan ↕</th>
                                <th style="width: 15%;">Keterampilan ↕</th>
                                <th style="width: 12%;">Rata-rata ↕</th>
                                <th style="width: 15%;">Keterangan ↕</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($nilai): ?>
                                <?php foreach ($nilai as $nomor => $row): ?>
                                    <tr>
                                        <td><?= $nomor + 1 ?></td>
                                        <td><?= htmlspecialchars($row['nama_mapel'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= $row['nilai_kkm'] !== null ? htmlspecialchars((string) $row['nilai_kkm'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                        <td><?= $row['nilai_pengetahuan'] !== null ? htmlspecialchars((string) $row['nilai_pengetahuan'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                        <td><?= $row['nilai_keterampilan'] !== null ? htmlspecialchars((string) $row['nilai_keterampilan'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                        <td class="fw-bold"><?= $row['rata_rata'] !== null ? number_format($row['rata_rata'], 2) : '-' ?></td>
                                        <td><?= htmlspecialchars((string) ($row['Keterangan'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada data nilai untuk siswa ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginasi & Info -->
                <div class="row justify-content-between align-items-center mt-3">
                    <div class="col-auto text-muted small">
                        Menampilkan <?= count($nilai) ?> data nilai
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>