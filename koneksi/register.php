<?php
require_once 'koneksi.php';

$error = '';
$registered = false;
$username = '';
$nis = '';
$namaSiswa = '';
$kelas = '';
$jenisKelamin = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username'] ?? '');
	$nis = trim($_POST['nis'] ?? '');
	$namaSiswa = trim($_POST['nama_siswa'] ?? '');
	$kelas = trim($_POST['kelas'] ?? '');
	$jenisKelamin = $_POST['jenis_kelamin'] ?? '';
	$password = $_POST['password'] ?? '';
	$konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

	if ($username === '' || $nis === '' || $namaSiswa === '' || $kelas === '') {
		$error = 'Semua data wajib diisi.';
	} elseif (!in_array($jenisKelamin, ['L', 'P'], true)) {
		$error = 'Pilih jenis kelamin yang valid.';
	} elseif (strlen($password) < 8) {
		$error = 'Password minimal 8 karakter.';
	} elseif ($password !== $konfirmasiPassword) {
		$error = 'Konfirmasi password tidak cocok.';
	} else {
		try {
			$cekUsername = mysqli_prepare($koneksi, 'SELECT id FROM users WHERE username = ?');
			mysqli_stmt_bind_param($cekUsername, 's', $username);
			mysqli_stmt_execute($cekUsername);
			mysqli_stmt_store_result($cekUsername);

			$cekNis = mysqli_prepare($koneksi, 'SELECT user_id FROM siswa WHERE nis = ?');
			mysqli_stmt_bind_param($cekNis, 's', $nis);
			mysqli_stmt_execute($cekNis);
			mysqli_stmt_store_result($cekNis);

			if (mysqli_stmt_num_rows($cekUsername) > 0) {
				$error = 'Username sudah digunakan.';
			} elseif (mysqli_stmt_num_rows($cekNis) > 0) {
				$error = 'NIS sudah terdaftar.';
			} else {
				mysqli_begin_transaction($koneksi);

				$passwordHash = password_hash($password, PASSWORD_DEFAULT);
				$buatUser = mysqli_prepare(
					$koneksi,
					"INSERT INTO users (username, password, role) VALUES (?, ?, 'siswa')"
				);
				mysqli_stmt_bind_param($buatUser, 'ss', $username, $passwordHash);
				mysqli_stmt_execute($buatUser);
				$userId = mysqli_insert_id($koneksi);

				$buatSiswa = mysqli_prepare(
					$koneksi,
					'INSERT INTO siswa (nis, nama_siswa, kelas, jenis_kelamin, user_id) VALUES (?, ?, ?, ?, ?)'
				);
				mysqli_stmt_bind_param($buatSiswa, 'ssssi', $nis, $namaSiswa, $kelas, $jenisKelamin, $userId);
				mysqli_stmt_execute($buatSiswa);

				mysqli_commit($koneksi);
				$registered = true;
				$username = '';
				$nis = '';
				$namaSiswa = '';
				$kelas = '';
				$jenisKelamin = '';
			}
		} catch (Throwable $exception) {
			mysqli_rollback($koneksi);
			$error = 'Pendaftaran gagal. Periksa kembali data yang dimasukkan.';
		}
	}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Register Siswa</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body { background-color: #f8f9fa; }
		.register-card { width: 100%; max-width: 520px; }
	</style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-4">
	<main class="card shadow-sm p-4 register-card border-0 rounded-3">
		<h3 class="text-center mb-4 text-secondary">Register Siswa</h3>

		<?php if ($registered): ?>
			<div class="alert alert-success" role="status">Pendaftaran berhasil. Silakan login.</div>
		<?php endif; ?>

		<?php if ($error !== ''): ?>
			<div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
		<?php endif; ?>

		<form method="POST" action="register.php">
			<div class="mb-3">
				<label for="nama_siswa" class="form-label">Nama lengkap</label>
				<input type="text" class="form-control" id="nama_siswa" name="nama_siswa" value="<?= htmlspecialchars($namaSiswa, ENT_QUOTES, 'UTF-8') ?>" required>
			</div>
			<div class="mb-3">
				<label for="nis" class="form-label">NIS</label>
				<input type="text" class="form-control" id="nis" name="nis" value="<?= htmlspecialchars($nis, ENT_QUOTES, 'UTF-8') ?>" required>
			</div>
			<div class="mb-3">
				<label for="kelas" class="form-label">Kelas</label>
				<input type="text" class="form-control" id="kelas" name="kelas" value="<?= htmlspecialchars($kelas, ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: XI-RPL-1" required>
			</div>
			<fieldset class="mb-3">
				<legend class="form-label fs-6">Jenis kelamin</legend>
				<div class="form-check form-check-inline">
					<input class="form-check-input" type="radio" name="jenis_kelamin" id="laki" value="L" <?= $jenisKelamin === 'L' ? 'checked' : '' ?> required>
					<label class="form-check-label" for="laki">Laki-laki</label>
				</div>
				<div class="form-check form-check-inline">
					<input class="form-check-input" type="radio" name="jenis_kelamin" id="perempuan" value="P" <?= $jenisKelamin === 'P' ? 'checked' : '' ?>>
					<label class="form-check-label" for="perempuan">Perempuan</label>
				</div>
			</fieldset>
			<div class="mb-3">
				<label for="username" class="form-label">Username</label>
				<input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" required>
			</div>
			<div class="mb-3">
				<label for="password" class="form-label">Password</label>
				<input type="password" class="form-control" id="password" name="password" minlength="8" required>
			</div>
			<div class="mb-3">
				<label for="konfirmasi_password" class="form-label">Konfirmasi password</label>
				<input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" minlength="8" required>
			</div>
			<button type="submit" class="btn btn-primary w-100">Daftar</button>
		</form>

		<div class="text-center mt-3">
			<small class="text-muted">Sudah punya akun? <a href="login.php" class="text-decoration-none">Login</a></small>
		</div>
	</main>
</body>
</html>
