<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['login']) || empty($_SESSION['id'])) {
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/../../koneksi.php';

$userId = (int) $_SESSION['id'];
$stmt = mysqli_prepare(
    $koneksi,
    'SELECT users.username, users.role, siswa.nis, siswa.nama AS nama_siswa, siswa.kelas,
            siswa.jenis_kelamin AS jenis_kelamin_siswa, guru.nip, guru.nama AS nama_guru,
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
$isSiswa = $role === 'siswa';
$isGuru = $role === 'guru';
$nama = $isSiswa
    ? (string) ($profil['nama_siswa'] ?? '')
    : ($isGuru ? (string) ($profil['nama_guru'] ?? '') : '');
$jenisKelamin = $isSiswa
    ? (string) ($profil['jenis_kelamin_siswa'] ?? '')
    : (string) ($profil['jenis_kelamin_guru'] ?? '');
$error = $_SESSION['profile_error'] ?? '';
unset($_SESSION['profile_error']);
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_profile'] = $csrfToken;
$esc = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 720px;">
            <div class="card-body p-4">
                <h3 class="fw-bold mb-4">Edit Profil</h3>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= $esc($error) ?></div>
                <?php endif; ?>

                <form action="aksi_edit_profile.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken) ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?= $esc($profil['username']) ?>" required>
                    </div>

                    <?php if ($isSiswa || $isGuru): ?>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama"
                                   value="<?= $esc($nama) ?>" required>
                        </div>

                        <?php if ($isSiswa): ?>
                            <div class="mb-3">
                                <label for="nis" class="form-label">NIS</label>
                                <input type="text" class="form-control" id="nis" name="nis"
                                       value="<?= $esc($profil['nis'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="kelas" class="form-label">Kelas</label>
                                <input type="text" class="form-control" id="kelas" name="kelas"
                                       value="<?= $esc($profil['kelas'] ?? '') ?>" required>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label for="nip" class="form-label">NIP</label>
                                <input type="text" class="form-control" id="nip" name="nip"
                                       value="<?= $esc($profil['nip'] ?? '') ?>" required>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="L" <?= $jenisKelamin === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= $jenisKelamin === 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="8">
                        <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="profile.php" class="btn btn-secondary">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
