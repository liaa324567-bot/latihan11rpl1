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

if (empty($_SESSION['token_edit_nilai'])) {
    $_SESSION['token_edit_nilai'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token_edit_nilai'];
$userId = (int) $_SESSION['id'];
$error = '';
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

$nilaiIdValue = $isPost ? ($_POST['id'] ?? '') : ($_GET['id'] ?? '');
$nilaiId = $nilaiIdValue === '' ? null : filter_var($nilaiIdValue, FILTER_VALIDATE_INT);
$siswaIdValue = $isPost ? ($_POST['siswa_id'] ?? '') : ($_GET['siswa_id'] ?? '');
$siswaId = filter_var($siswaIdValue, FILTER_VALIDATE_INT);
$mapelIdValue = $isPost ? ($_POST['mapel_id'] ?? '') : ($_GET['mapel_id'] ?? '');
$mapelId = filter_var($mapelIdValue, FILTER_VALIDATE_INT);
$kelas = isset($_POST['kelas']) && is_string($_POST['kelas'])
    ? trim($_POST['kelas'])
    : (isset($_GET['kelas']) && is_string($_GET['kelas']) ? trim($_GET['kelas']) : '');

if (($nilaiId !== null && (!$nilaiId || $nilaiId < 1)) || !$siswaId || !$mapelId || $kelas === '') {
    http_response_code(400);
    exit('Data siswa atau nilai tidak valid.');
}

$stmt = mysqli_prepare(
    $koneksi,
    'SELECT siswa.nama, siswa.kelas, mapel.nama_mapel,
            nilai_rapor.id AS nilai_id, nilai_rapor.nilai_kkm,
            nilai_rapor.nilai_pengetahuan, nilai_rapor.nilai_keterampilan,
            nilai_rapor.Keterangan
     FROM mengajar
     INNER JOIN siswa ON siswa.id = mengajar.siswa_id
     INNER JOIN mapel ON mapel.id = mengajar.mapel_id
     INNER JOIN guru ON guru.id = mapel.guru_id
     LEFT JOIN nilai_rapor
        ON nilai_rapor.siswa_id = siswa.id
        AND nilai_rapor.mapel_id = mapel.id
     WHERE siswa.id = ? AND mapel.id = ? AND siswa.kelas = ?
        AND guru.user_id = ?
     LIMIT 1'
);
if (!$stmt) {
    die('Gagal menyiapkan data siswa: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}
if (!mysqli_stmt_bind_param($stmt, 'iisi', $siswaId, $mapelId, $kelas, $userId) || !mysqli_stmt_execute($stmt)) {
    die('Gagal memuat data siswa: ' . htmlspecialchars(mysqli_stmt_error($stmt), ENT_QUOTES, 'UTF-8'));
}
$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    die('Gagal membaca data siswa: ' . htmlspecialchars(mysqli_stmt_error($stmt), ENT_QUOTES, 'UTF-8'));
}
$nilai = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$nilai || ($nilaiId !== null && (int) $nilai['nilai_id'] !== $nilaiId)) {
    http_response_code(404);
    exit('Data nilai tidak ditemukan atau bukan milik mata pelajaran Anda.');
}

$nilaiKkm = $nilai['nilai_kkm'] ?? 75;
$nilaiPengetahuan = $nilai['nilai_pengetahuan'] ?? '';
$nilaiKeterampilan = $nilai['nilai_keterampilan'] ?? '';
$keterangan = $nilai['Keterangan'] ?? '';

if ($isPost) {
    $postedToken = isset($_POST['token']) && is_string($_POST['token']) ? $_POST['token'] : '';
    if (!hash_equals($token, $postedToken)) {
        http_response_code(400);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
    }

    $nilaiKkm = filter_input(INPUT_POST, 'nilai_kkm', FILTER_VALIDATE_INT);
    $nilaiPengetahuan = filter_input(INPUT_POST, 'nilai_pengetahuan', FILTER_VALIDATE_INT);
    $nilaiKeterampilan = filter_input(INPUT_POST, 'nilai_keterampilan', FILTER_VALIDATE_INT);
    $keterangan = isset($_POST['keterangan']) && is_string($_POST['keterangan'])
        ? trim($_POST['keterangan'])
        : '';

    if (
        $nilaiKkm === false || $nilaiKkm === null || $nilaiKkm < 0 || $nilaiKkm > 100
        || $nilaiPengetahuan === false || $nilaiPengetahuan === null || $nilaiPengetahuan < 0 || $nilaiPengetahuan > 100
        || $nilaiKeterampilan === false || $nilaiKeterampilan === null || $nilaiKeterampilan < 0 || $nilaiKeterampilan > 100
        || strlen($keterangan) > 50
    ) {
        $error = 'Nilai harus antara 0 sampai 100 dan keterangan maksimal 50 karakter.';
    } elseif ($nilai['nilai_id'] !== null) {
        $saveStmt = mysqli_prepare(
            $koneksi,
            'UPDATE nilai_rapor
             SET nilai_kkm = ?, nilai_pengetahuan = ?, nilai_keterampilan = ?, Keterangan = ?
             WHERE id = ? AND siswa_id = ? AND mapel_id = ?'
        );
        if (!$saveStmt) {
            die('Gagal menyiapkan perubahan nilai: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
        }
        $existingId = (int) $nilai['nilai_id'];
        if (!mysqli_stmt_bind_param(
            $saveStmt,
            'iiisiii',
            $nilaiKkm,
            $nilaiPengetahuan,
            $nilaiKeterampilan,
            $keterangan,
            $existingId,
            $siswaId,
            $mapelId
        ) || !mysqli_stmt_execute($saveStmt)) {
            die('Gagal memperbarui nilai: ' . htmlspecialchars(mysqli_stmt_error($saveStmt), ENT_QUOTES, 'UTF-8'));
        }
        mysqli_stmt_close($saveStmt);
        header('Location: detail_nilai.php?mapel_id=' . $mapelId . '&kelas=' . rawurlencode($kelas));
        exit;
    } else {
        $saveStmt = mysqli_prepare(
            $koneksi,
            'INSERT INTO nilai_rapor
                (siswa_id, mapel_id, nilai_kkm, nilai_pengetahuan, nilai_keterampilan, Keterangan)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        if (!$saveStmt) {
            die('Gagal menyiapkan penyimpanan nilai: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
        }
        if (!mysqli_stmt_bind_param(
            $saveStmt,
            'iiiiis',
            $siswaId,
            $mapelId,
            $nilaiKkm,
            $nilaiPengetahuan,
            $nilaiKeterampilan,
            $keterangan
        ) || !mysqli_stmt_execute($saveStmt)) {
            die('Gagal menyimpan nilai: ' . htmlspecialchars(mysqli_stmt_error($saveStmt), ENT_QUOTES, 'UTF-8'));
        }
        mysqli_stmt_close($saveStmt);
        header('Location: detail_nilai.php?mapel_id=' . $mapelId . '&kelas=' . rawurlencode($kelas));
        exit;
    }
}

$esc = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Nilai Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5" style="max-width: 700px;">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="card-title mb-4">Edit Nilai Siswa</h4>
                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= $esc($error) ?></div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="token" value="<?= $esc($token) ?>">
                    <input type="hidden" name="id" value="<?= $nilai['nilai_id'] !== null ? (int) $nilai['nilai_id'] : '' ?>">
                    <input type="hidden" name="siswa_id" value="<?= (int) $siswaId ?>">
                    <input type="hidden" name="mapel_id" value="<?= (int) $mapelId ?>">
                    <input type="hidden" name="kelas" value="<?= $esc($kelas) ?>">
                    <div class="mb-3">
                        <label for="nama_siswa" class="form-label">Nama Siswa</label>
                        <input type="text" class="form-control" id="nama_siswa" value="<?= $esc($nilai['nama']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="kelas_tampil" class="form-label">Kelas</label>
                        <input type="text" class="form-control" id="kelas_tampil" value="<?= $esc($nilai['kelas']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
                        <input type="text" class="form-control" id="mata_pelajaran" value="<?= $esc($nilai['nama_mapel']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="nilai_kkm" class="form-label">Nilai KKM</label>
                        <input type="number" class="form-control" id="nilai_kkm" name="nilai_kkm" min="0" max="100" value="<?= $esc($nilaiKkm) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="nilai_pengetahuan" class="form-label">Nilai Pengetahuan</label>
                        <input type="number" class="form-control" id="nilai_pengetahuan" name="nilai_pengetahuan" min="0" max="100" value="<?= $esc($nilaiPengetahuan) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="nilai_keterampilan" class="form-label">Nilai Keterampilan</label>
                        <input type="number" class="form-control" id="nilai_keterampilan" name="nilai_keterampilan" min="0" max="100" value="<?= $esc($nilaiKeterampilan) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" maxlength="50"><?= $esc($keterangan) ?></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="detail_nilai.php?mapel_id=<?= (int) $mapelId ?>&amp;kelas=<?= rawurlencode($kelas) ?>" class="btn btn-secondary px-4">Kembali</a>
                        <button type="submit" class="btn btn-primary px-4">Update Nilai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
