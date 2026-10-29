<?php
require_once '../koneksi.php';

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$mapelLamaId = filter_var(
    $isPost ? ($_POST['mapel_id_lama'] ?? null) : ($_GET['mapel_id'] ?? null),
    FILTER_VALIDATE_INT
);
$kelasLama = trim($isPost ? ($_POST['kelas_lama'] ?? '') : ($_GET['kelas'] ?? ''));
$mapelId = filter_var(
    $isPost ? ($_POST['mapel_id'] ?? null) : $mapelLamaId,
    FILTER_VALIDATE_INT
);
$kelas = trim($isPost ? ($_POST['kelas'] ?? '') : $kelasLama);

if (!$mapelLamaId || $mapelLamaId < 1 || $kelasLama === '' || !$mapelId || $mapelId < 1 || $kelas === '') {
    http_response_code(400);
    exit('Data mata pelajaran atau kelas tidak valid.');
}

$pesanError = '';

if ($isPost) {
    if (!$mapelId || $mapelId < 1 || $kelas === '') {
        $pesanError = 'Silakan pilih kelas dan mata pelajaran yang valid.';
    } else {
        $stmtValidasi = mysqli_prepare(
            $koneksi,
            'SELECT
                EXISTS(SELECT 1 FROM mapel WHERE id = ?) AS mapel_valid,
                EXISTS(SELECT 1 FROM siswa WHERE kelas = ?) AS kelas_valid'
        );
        if (!$stmtValidasi) {
            die('Gagal menyiapkan validasi data: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
        }
        mysqli_stmt_bind_param($stmtValidasi, 'is', $mapelId, $kelas);
        mysqli_stmt_execute($stmtValidasi);
        $dataValidasi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtValidasi));
        mysqli_stmt_close($stmtValidasi);

        if (!$dataValidasi['mapel_valid'] || !$dataValidasi['kelas_valid']) {
            $pesanError = 'Kelas atau mata pelajaran tidak ditemukan.';
        } else {
            mysqli_begin_transaction($koneksi);

            $stmtDelete = mysqli_prepare(
                $koneksi,
                'DELETE mengajar
                 FROM mengajar
                 INNER JOIN siswa ON siswa.id = mengajar.siswa_id
                 WHERE mengajar.mapel_id = ? AND siswa.kelas = ?'
            );
            if (!$stmtDelete) {
                mysqli_rollback($koneksi);
                die('Gagal menyiapkan perubahan data: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
            }
            mysqli_stmt_bind_param($stmtDelete, 'is', $mapelLamaId, $kelasLama);
            if (!mysqli_stmt_execute($stmtDelete)) {
                mysqli_rollback($koneksi);
                die('Gagal memperbarui data mengajar: ' . htmlspecialchars(mysqli_stmt_error($stmtDelete), ENT_QUOTES, 'UTF-8'));
            }
            mysqli_stmt_close($stmtDelete);

            $hasilId = mysqli_query($koneksi, 'SELECT COALESCE(MAX(id), 0) + 1 AS id_berikutnya FROM mengajar');
            if (!$hasilId) {
                mysqli_rollback($koneksi);
                die('Gagal menyiapkan ID data mengajar: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
            }
            $idBerikutnya = (int) mysqli_fetch_assoc($hasilId)['id_berikutnya'];

            $stmtSiswa = mysqli_prepare(
                $koneksi,
                'SELECT siswa.id
                 FROM siswa
                 WHERE siswa.kelas = ?
                   AND NOT EXISTS (
                       SELECT 1 FROM mengajar
                       WHERE mengajar.mapel_id = ? AND mengajar.siswa_id = siswa.id
                   )
                 ORDER BY siswa.id'
            );
            if (!$stmtSiswa) {
                mysqli_rollback($koneksi);
                die('Gagal menyiapkan daftar siswa: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
            }
            mysqli_stmt_bind_param($stmtSiswa, 'si', $kelas, $mapelId);
            if (!mysqli_stmt_execute($stmtSiswa)) {
                mysqli_rollback($koneksi);
                die('Gagal memuat daftar siswa: ' . htmlspecialchars(mysqli_stmt_error($stmtSiswa), ENT_QUOTES, 'UTF-8'));
            }

            $hasilSiswa = mysqli_stmt_get_result($stmtSiswa);
            $stmtSimpan = mysqli_prepare($koneksi, 'INSERT INTO mengajar (id, mapel_id, siswa_id) VALUES (?, ?, ?)');
            if (!$stmtSimpan) {
                mysqli_rollback($koneksi);
                die('Gagal menyiapkan penyimpanan data mengajar: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
            }

            while ($siswa = mysqli_fetch_assoc($hasilSiswa)) {
                $siswaId = (int) $siswa['id'];
                mysqli_stmt_bind_param($stmtSimpan, 'iii', $idBerikutnya, $mapelId, $siswaId);
                if (!mysqli_stmt_execute($stmtSimpan)) {
                    mysqli_rollback($koneksi);
                    die('Gagal menyimpan data mengajar: ' . htmlspecialchars(mysqli_stmt_error($stmtSimpan), ENT_QUOTES, 'UTF-8'));
                }
                $idBerikutnya++;
            }

            mysqli_stmt_close($stmtSimpan);
            mysqli_stmt_close($stmtSiswa);
            mysqli_commit($koneksi);

            header('Location: daftar_mengajar.php?status=updated');
            exit;
        }
    }
}

$queryMapel = mysqli_query(
    $koneksi,
    'SELECT mapel.id, mapel.nama_mapel, guru.nama AS nama_guru
     FROM mapel LEFT JOIN guru ON guru.id = mapel.guru_id
     ORDER BY mapel.nama_mapel'
);
if (!$queryMapel) {
    die('Gagal memuat data mata pelajaran: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}

$queryKelas = mysqli_query(
    $koneksi,
    "SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND TRIM(kelas) <> '' ORDER BY kelas"
);
if (!$queryKelas) {
    die('Gagal memuat data kelas: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}

require_once 'form_edit_mengajar.php';