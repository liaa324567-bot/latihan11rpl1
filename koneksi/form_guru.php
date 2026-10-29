<?php
require_once 'koneksi.php';

$error = '';
$nip = '';
$namaLengkap = '';
$username = '';
$jenisKelamin = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nip = trim($_POST['nip'] ?? '');
  $namaLengkap = trim($_POST['nama_lengkap'] ?? '');
  $jenisKelamin = $_POST['jenis_kelamin'] ?? '';
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($nip === '' || $namaLengkap === '' || $username === '' || $password === '') {
    $error = 'Semua data wajib diisi.';
  } elseif (!in_array($jenisKelamin, ['L', 'P'], true)) {
    $error = 'Pilih jenis kelamin yang valid.';
  } else {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $transactionStarted = false;

    try {
      $cekUsername = mysqli_prepare($koneksi, 'SELECT id FROM users WHERE username = ?');
      mysqli_stmt_bind_param($cekUsername, 's', $username);
      mysqli_stmt_execute($cekUsername);
      mysqli_stmt_store_result($cekUsername);

      if (mysqli_stmt_num_rows($cekUsername) > 0) {
        $error = 'Username sudah digunakan.';
      } else {
        mysqli_begin_transaction($koneksi);
        $transactionStarted = true;

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $buatUser = mysqli_prepare(
          $koneksi,
          "INSERT INTO users (username, password, role) VALUES (?, ?, 'guru')"
        );
        mysqli_stmt_bind_param($buatUser, 'ss', $username, $passwordHash);
        mysqli_stmt_execute($buatUser);
        $userId = mysqli_insert_id($koneksi);

        $buatGuru = mysqli_prepare(
          $koneksi,
          'INSERT INTO guru (user_id, nama, nip, jenis_kelamin) VALUES (?, ?, ?, ?)'
        );
        mysqli_stmt_bind_param($buatGuru, 'isss', $userId, $namaLengkap, $nip, $jenisKelamin);
        mysqli_stmt_execute($buatGuru);

        mysqli_commit($koneksi);
        $transactionStarted = false;
        header('Location: daftar_guru.php');
        exit;
      }
    } catch (mysqli_sql_exception $exception) {
      if ($transactionStarted) {
        mysqli_rollback($koneksi);
      }
      $error = 'Gagal menyimpan data guru. Periksa kembali data yang dimasukkan.';
    }
  }
}

$hideProfileName = true;
require_once 'navbar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bootstrap demo</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

  <div class="container d-flex justify-content-center">
    <div class="card shadow-sm col-12 col-md-8 col-lg-6">
      
      <!-- Card Header -->
      <div class="card-header bg-white py-3">
        <h4 class="mb-0 fs-5 fw-medium">Form Tambah Guru</h4>
      </div>

      <!-- Card Body -->
      <div class="card-body p-4">
        <?php if ($error !== ''): ?>
          <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form action="" method="POST">
          
          <!-- Bagian Data Profil Guru -->
          <h3 class="fw-bold mb-3">Data Profil Guru</h3>

          <div class="mb-3">
            <label for="nip" class="form-label">NIP:</label>
            <input type="text" class="form-control" id="nip" name="nip" value="<?= htmlspecialchars($nip, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nomor Induk Guru | Contoh: 1234567890" required>
          </div>

          <div class="mb-3">
            <label for="nama_lengkap" class="form-label">Nama Lengkap:</label>
            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($namaLengkap, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama Lengkap Guru | Contoh: Pak Budi" required>
          </div>

          <div class="mb-3">
            <label class="form-label d-block">Jenis Kelamin:</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="jenis_kelamin" id="laki" value="L" <?= $jenisKelamin === 'L' ? 'checked' : '' ?> required>
              <label class="form-check-label" for="laki">
                Laki-laki
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="jenis_kelamin" id="perempuan" value="P" <?= $jenisKelamin === 'P' ? 'checked' : '' ?>>
              <label class="form-check-label" for="perempuan">
                Perempuan
              </label>
            </div>
          </div>

          <hr class="my-4">

          <!-- Bagian Data Kredensial Akun -->
          <h3 class="fw-bold mb-3">Data Kredensial Akun</h3>

          <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" placeholder="Username Akun | Contoh: ujang123" required>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label">Password Akun:</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password Akun | Contoh: password123" required>
          </div>

          <!-- Tombol Aksi -->
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan Data</button>
            <a href="daftar_guru.php" class="btn btn-secondary">Kembali</a>
          </div>

        </form>
      </div>

    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>