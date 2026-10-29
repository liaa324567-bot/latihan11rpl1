<?php
$koneksi = mysqli_connect("localhost", "root", "", "db_siacad_smk");

$id = $_GET['id'];

$user = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT * FROM users WHERE id='$id'"
));

$role = $user['role'];

if ($role == 'siswa') {
    $data = mysqli_fetch_assoc(mysqli_query(
        $koneksi,
        "SELECT * FROM siswa WHERE user_id='$id'"
    ));
} elseif ($role == 'guru') {
    $data = mysqli_fetch_assoc(mysqli_query(
        $koneksi,
        "SELECT * FROM guru WHERE user_id='$id'"
    ));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Data</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow mx-auto" style="max-width: 600px;">

        <div class="card-body p-4">

            <h3 class="mb-4">
                Edit Data <?= ucfirst($role) ?>
            </h3>


            <?php if ($role == 'siswa') { ?>

            <!-- FORM SISWA -->
            <form action="aksi_edit.php" method="POST">

                <input type="hidden" name="id" value="<?= $id ?>">
    
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        value="<?= $user['username'] ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                    >
                    <small class="text-muted">
                        Kosongkan jika tidak ingin mengubah password.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">NIS</label>
                    <input
                        type="text"
                        name="nis"
                        class="form-control"
                        value="<?= $data['nis'] ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input
                        type="text"
                        name="nama_siswa"
                        class="form-control"
                        value="<?= $data['nama'] ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Kelas</label>
                    <input
                        type="text"
                        name="kelas"
                        class="form-control"
                        value="<?= $data['kelas'] ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Kelamin</label>

                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="L"
                            <?= $data['jenis_kelamin'] == 'L' ? 'selected' : '' ?>>
                            Laki-laki
                        </option>

                        <option value="P"
                            <?= $data['jenis_kelamin'] == 'P' ? 'selected' : '' ?>>
                            Perempuan
                        </option>
                    </select>
                </div>

                <button class="btn btn-primary">
                    Simpan
                </button>

                <a href="table.php" class="btn btn-secondary">
                    Batal
                </a>

            </form>


            <?php } elseif ($role == 'guru') { ?>

            <!-- FORM GURU -->
            <form action="aksi_edit.php" method="POST">

                <input type="hidden" name="id" value="<?= $id ?>">

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        value="<?= $user['username'] ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                    >
                    <small class="text-muted">
                        Kosongkan jika tidak ingin mengubah password.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">NIP</label>
                    <input
                        type="text"
                        name="nip"
                        class="form-control"
                        value="<?= $data['nip'] ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input
                        type="text"
                        name="nama"
                        class="form-control"
                        value="<?= $data['nama'] ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Kelamin</label>

                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="L"
                            <?= $data['jenis_kelamin'] == 'L' ? 'selected' : '' ?>>
                            Laki-laki
                        </option>

                        <option value="P"
                            <?= $data['jenis_kelamin'] == 'P' ? 'selected' : '' ?>>
                            Perempuan
                        </option>
                    </select>
                </div>

                <button class="btn btn-primary">
                    Simpan
                </button>

                <a href="table.php" class="btn btn-secondary">
                    Batal
                </a>

            </form>


            <?php } elseif ($role == 'admin') { ?>

            <!-- FORM ADMIN -->
            <form action="aksi_edit.php" method="POST">

                <input type="hidden" name="id" value="<?= $id ?>">

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        value="<?= $user['username'] ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                    >
                    <small class="text-muted">
                        Kosongkan jika tidak ingin mengubah password.
                    </small>
                </div>

                <button class="btn btn-primary">
                    Simpan
                </button>

                <a href="table.php" class="btn btn-secondary">
                    Batal
                </a>

            </form>

            <?php } ?>

        </div>

    </div>

</div>

</body>
</html>