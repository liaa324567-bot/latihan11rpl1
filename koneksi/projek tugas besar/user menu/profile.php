<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['login']) || empty($_SESSION['id'])) {
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/../../koneksi.php';
require_once __DIR__ . '/../../navbar.php';


$userId = (int) $_SESSION['id'];
$stmt = mysqli_prepare(
    $koneksi,
    'SELECT users.username, users.role, siswa.nama AS nama_siswa, siswa.nis, siswa.kelas,
            siswa.jenis_kelamin AS jenis_kelamin_siswa, guru.nama AS nama_guru, guru.nip,
            guru.jenis_kelamin AS jenis_kelamin_guru
     FROM users
     LEFT JOIN siswa ON siswa.user_id = users.id
     LEFT JOIN guru ON guru.user_id = users.id
     WHERE users.id = ?
     LIMIT 1'
);
if (!$stmt) {
    die('Gagal menyiapkan data profil: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}
mysqli_stmt_bind_param($stmt, 'i', $userId);
if (!mysqli_stmt_execute($stmt)) {
    die('Gagal memuat data profil: ' . htmlspecialchars(mysqli_stmt_error($stmt), ENT_QUOTES, 'UTF-8'));
}
$hasilProfil = mysqli_stmt_get_result($stmt);
$profil = $hasilProfil ? mysqli_fetch_assoc($hasilProfil) : null;
mysqli_stmt_close($stmt);

if (!$profil) {
    session_unset();
    session_destroy();
    header('Location: ../../login.php');
    exit;
}

$role = strtolower((string) $profil['role']);
$namaProfil = $role === 'siswa'
    ? (string) ($profil['nama_siswa'] ?? '')
    : ($role === 'guru' ? (string) ($profil['nama_guru'] ?? '') : '');
if ($namaProfil === '') {
    $namaProfil = (string) $profil['username'];
}
$inisialProfil = strtoupper(substr($namaProfil, 0, 1));
$pesanSukses = $_SESSION['profile_success'] ?? '';
unset($_SESSION['profile_success']);
$esc = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Admin</title>
    <!-- Memuat CSS Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <!-- Card Utama Profil -->
                <div class="card shadow-sm border-0 p-4">
                    
                    <!-- Header Profil -->
                    <div class="mb-4">
                        <h3 class="fw-bold">Profile</h3>
                        <p class="text-muted small">Informasi akun yang sedang login</p>
                    </div>

                    <hr>

                    <!-- Bagian Tengah: Avatar & Informasi -->
                    <div class="row align-items-center my-4">
                        
                        <!-- Kolom Avatar (Lingkaran Huruf A) -->
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold shadow" style="width: 90px; height: 90px; font-size: 2.5rem;">
                                <?= $esc($inisialProfil) ?>
                            </div>
                            <h5 class="mt-2 mb-0 fw-bold"><?= $esc($namaProfil) ?></h5>
                            <span class="badge bg-primary"><?= $esc(ucfirst($role)) ?></span>
                        </div>

                        <!-- Kolom Detail Informasi -->
                        <div class="col-md-8">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6 class="text-uppercase text-muted fs-7 fw-bold">Informasi Akun</h6>
                                </div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Username</div>
                                <div class="col-8 fw-semibold text-danger"><?= $esc($profil['username']) ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Role</div>
                                <div class="col-8 fw-semibold"><?= $esc(ucfirst($role)) ?></div>
                            </div>

                            <hr class="my-3">

                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6 class="text-uppercase text-muted fs-7 fw-bold">Data Profil</h6>
                                </div>
                            </div>

                            <?php if ($pesanSukses !== ''): ?>
                                <div class="alert alert-success" role="status"><?= $esc($pesanSukses) ?></div>
                            <?php endif; ?>

                            <div class="row mb-2">
                                <div class="col-4 text-muted">Nama</div>
                                <div class="col-8 fw-semibold"><?= $esc($namaProfil) ?></div>
                            </div>
                        </div>

                    </div>

                    <hr>

                    <!-- Tombol Aksi di Bagian Bawah -->
                    <div class="d-flex justify-content-start gap-2 mt-3">
                        <a href="../../index.php" class="btn btn-secondary px-4">Kembali</a>
                        <a href="edit_profile.php" class="btn btn-primary px-4">Edit Profile</a>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Opsional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>