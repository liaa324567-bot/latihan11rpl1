<?php
require_once '../koneksi.php';

$pesanError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kelas = trim($_POST['kelas'] ?? '');
    $mapelId = filter_var($_POST['mapel_id'] ?? null, FILTER_VALIDATE_INT);

    if ($kelas === '' || !$mapelId || $mapelId < 1) {
        $pesanError = 'Silakan pilih kelas dan guru beserta mata pelajarannya.';
    } else {
        $stmtValidasi = mysqli_prepare($koneksi, 'SELECT 1 FROM siswa WHERE kelas = ? LIMIT 1');
        if (!$stmtValidasi) {
            die('Gagal menyiapkan validasi kelas: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
        }
        mysqli_stmt_bind_param($stmtValidasi, 's', $kelas);
        mysqli_stmt_execute($stmtValidasi);
        $kelasValid = mysqli_stmt_get_result($stmtValidasi)->num_rows > 0;
        mysqli_stmt_close($stmtValidasi);

        $stmtMapel = mysqli_prepare($koneksi, 'SELECT 1 FROM mapel WHERE id = ? AND guru_id IS NOT NULL LIMIT 1');
        if (!$stmtMapel) {
            die('Gagal menyiapkan validasi guru dan mata pelajaran: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
        }
        mysqli_stmt_bind_param($stmtMapel, 'i', $mapelId);
        mysqli_stmt_execute($stmtMapel);
        $guruMapelValid = mysqli_stmt_get_result($stmtMapel)->num_rows > 0;
        mysqli_stmt_close($stmtMapel);

        if (!$kelasValid || !$guruMapelValid) {
            $pesanError = 'Kelas, guru, atau mata pelajaran tidak ditemukan atau tidak sesuai.';
        } else {
            mysqli_begin_transaction($koneksi);

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

            $jumlahDisimpan = 0;
            while ($siswa = mysqli_fetch_assoc($hasilSiswa)) {
                $siswaId = (int) $siswa['id'];
                mysqli_stmt_bind_param($stmtSimpan, 'iii', $idBerikutnya, $mapelId, $siswaId);
                if (!mysqli_stmt_execute($stmtSimpan)) {
                    mysqli_rollback($koneksi);
                    die('Gagal menyimpan data mengajar: ' . htmlspecialchars(mysqli_stmt_error($stmtSimpan), ENT_QUOTES, 'UTF-8'));
                }
                $idBerikutnya++;
                $jumlahDisimpan++;
            }

            mysqli_stmt_close($stmtSimpan);
            mysqli_stmt_close($stmtSiswa);

            if ($jumlahDisimpan === 0) {
                mysqli_rollback($koneksi);
                $pesanError = 'Mata pelajaran ini sudah ditambahkan untuk semua siswa di kelas tersebut.';
            } else {
                mysqli_commit($koneksi);
                header('Location: daftar_mengajar.php?status=added');
                exit;
            }
        }
    }
}

$queryMapel = mysqli_query(
    $koneksi,
    'SELECT mapel.id, mapel.nama_mapel, guru.nama AS nama_guru
     FROM mapel
     INNER JOIN guru ON guru.id = mapel.guru_id
     ORDER BY guru.nama, mapel.nama_mapel'
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
