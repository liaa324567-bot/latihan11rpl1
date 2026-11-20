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


$userId = (int) $_SESSION['id'];
$stmt = mysqli_prepare(
    $koneksi,
    'SELECT mapel.id AS mapel_id, siswa.kelas, mapel.nama_mapel,
            COUNT(DISTINCT siswa.id) AS jumlah_siswa
     FROM mengajar
     INNER JOIN siswa ON siswa.id = mengajar.siswa_id
     INNER JOIN mapel ON mapel.id = mengajar.mapel_id
     INNER JOIN guru ON guru.id = mapel.guru_id
     WHERE guru.user_id = ?
     GROUP BY siswa.kelas, mapel.id, mapel.nama_mapel
     ORDER BY siswa.kelas, mapel.nama_mapel'
);
if (!$stmt) {
    die('Gagal menyiapkan daftar kelas: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}

if (!mysqli_stmt_bind_param($stmt, 'i', $userId) || !mysqli_stmt_execute($stmt)) {
    die('Gagal memuat daftar kelas: ' . htmlspecialchars(mysqli_stmt_error($stmt), ENT_QUOTES, 'UTF-8'));
}

$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    die('Gagal membaca daftar kelas: ' . htmlspecialchars(mysqli_stmt_error($stmt), ENT_QUOTES, 'UTF-8'));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kelas yang Diajar</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container my-5">
        <!-- Judul Halaman -->
        <h2 class="text-center mb-5 fw-bold">Daftar Kelas yang Diajar</h2>

        <!-- Card Container untuk Tabel dan Filter -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                
                <!-- Baris Kontrol Atas (Show Entries & Search) -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6 d-flex align-items-center mb-2 mb-md-0">
                        <label for="jumlahData" class="me-2 text-muted">Tampilkan</label>
                        <select id="jumlahData" class="form-select form-select-sm w-auto d-inline-block">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <label for="jumlahData" class="ms-2 text-muted">data per halaman</label>
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end align-items-center">
                        <label for="cariKelas" class="me-2 text-muted">Cari:</label>
                        <input id="cariKelas" type="text" class="form-control form-control-sm w-auto" placeholder="Kelas atau mata pelajaran">
                    </div>
                </div>

                <!-- Tabel Data -->
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-3">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 8%;">No <span class="float-end text-muted">↕</span></th>
                                <th style="width: 30%;">Kelas <span class="float-end text-muted">↕</span></th>
                                <th style="width: 32%;">Mata Pelajaran <span class="float-end text-muted">↕</span></th>
                                <th style="width: 20%;">Jumlah Siswa <span class="float-end text-muted">↕</span></th>
                                <th style="width: 10%;" class="text-center">Aksi <span class="text-muted">↕</span></th>
                            </tr>
                        </thead>
                        <tbody id="daftarKelas">
                            <?php $nomor = 1; ?>
                            <?php while ($kelasDiajar = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td class="fw-bold"><?= $nomor++ ?></td>
                                    <td><?= htmlspecialchars($kelasDiajar['kelas'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($kelasDiajar['nama_mapel'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int) $kelasDiajar['jumlah_siswa'] ?> siswa</td>
                                    <td class="text-center">
                                        <a class="btn btn-primary btn-sm px-3" href="detail_nilai.php?mapel_id=<?= (int) $kelasDiajar['mapel_id'] ?>&amp;kelas=<?= rawurlencode($kelasDiajar['kelas']) ?>">Lihat Nilai</a>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr id="tidakAdaData" <?= mysqli_num_rows($result) > 0 ? 'hidden' : '' ?>>
                                <td colspan="5" class="text-center text-muted">Belum ada kelas yang diajar.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Baris Informasi Bawah & Pagination -->
                <div class="row align-items-center mt-3">
                    <div id="infoHalaman" class="col-md-6 text-muted mb-2 mb-md-0" aria-live="polite">
                        Menampilkan 0 data
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end">
                        <nav aria-label="Navigasi daftar kelas">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item">
                                    <button id="halamanSebelumnya" class="page-link" type="button">Sebelumnya</button>
                                </li>
                                <li class="page-item active" aria-current="page">
                                    <span id="nomorHalaman" class="page-link">1</span>
                                </li>
                                <li class="page-item">
                                    <button id="halamanSelanjutnya" class="page-link" type="button">Selanjutnya</button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const daftarKelas = document.getElementById('daftarKelas');
        const barisKelas = Array.from(daftarKelas.querySelectorAll('tr:not(#tidakAdaData)'));
        const jumlahData = document.getElementById('jumlahData');
        const cariKelas = document.getElementById('cariKelas');
        const tidakAdaData = document.getElementById('tidakAdaData');
        const infoHalaman = document.getElementById('infoHalaman');
        const nomorHalaman = document.getElementById('nomorHalaman');
        const tombolSebelumnya = document.getElementById('halamanSebelumnya');
        const tombolSelanjutnya = document.getElementById('halamanSelanjutnya');
        let halamanAktif = 1;

        function perbaruiDaftar() {
            const kataKunci = cariKelas.value.trim().toLocaleLowerCase('id');
            const dataCocok = barisKelas.filter((baris) =>
                baris.textContent.toLocaleLowerCase('id').includes(kataKunci)
            );
            const batas = Number(jumlahData.value);
            const totalHalaman = Math.max(1, Math.ceil(dataCocok.length / batas));
            halamanAktif = Math.min(halamanAktif, totalHalaman);
            const awal = (halamanAktif - 1) * batas;

            barisKelas.forEach((baris) => {
                baris.hidden = true;
            });
            dataCocok.slice(awal, awal + batas).forEach((baris, index) => {
                baris.hidden = false;
                baris.cells[0].textContent = String(awal + index + 1);
            });

            tidakAdaData.hidden = dataCocok.length > 0;
            infoHalaman.textContent = dataCocok.length === 0
                ? 'Tidak ada data yang cocok.'
                : `Menampilkan ${awal + 1}-${Math.min(awal + batas, dataCocok.length)} dari ${dataCocok.length} data`;
            nomorHalaman.textContent = String(halamanAktif);
            tombolSebelumnya.disabled = halamanAktif === 1;
            tombolSelanjutnya.disabled = halamanAktif === totalHalaman;
        }

        jumlahData.addEventListener('change', () => {
            halamanAktif = 1;
            perbaruiDaftar();
        });
        cariKelas.addEventListener('input', () => {
            halamanAktif = 1;
            perbaruiDaftar();
        });
        tombolSebelumnya.addEventListener('click', () => {
            halamanAktif--;
            perbaruiDaftar();
        });
        tombolSelanjutnya.addEventListener('click', () => {
            halamanAktif++;
            perbaruiDaftar();
        });

        perbaruiDaftar();
    </script>
</body>
</html>