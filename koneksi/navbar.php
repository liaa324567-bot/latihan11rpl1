<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? 'user';
$nama = $_SESSION['nama'] ?? 'admin';
$role = $_SESSION['role'] ?? '';
$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$koneksiBasePath = preg_replace('~/projek tugas besar$~', '', rtrim($scriptDirectory, '/'));
$mengajarBasePath = htmlspecialchars($koneksiBasePath . '/projek%20tugas%20besar/', ENT_QUOTES, 'UTF-8');
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Latihan Navbar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
        <div class="container-fluid">
            <!-- Brand / Logo -->
            <a class="navbar-brand fw-bold" href="#">Navbar</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Menu Utama di Kiri -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Home</a>
                    </li>

                    <!-- Dropdown User -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            User
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="table.php">Daftar User</a></li>
                            <li><a class="dropdown-item" href="seluruh.php">Daftar Seluruh</a></li>
                            <li><a class="dropdown-item" href="form1.php">Daftar Admin</a></li>
                            <li><a class="dropdown-item" href="form2.php">Daftar Siswa</a></li>
                            <li><a class="dropdown-item" href="form3.php">Daftar Guru</a></li>
                        </ul>
                    </li>

                    <!-- Dropdown Siswa -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Siswa
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="daftar_siswa.php">Daftar Siswa</a></li>
                            <li><a class="dropdown-item" href="form2.php">Tambah Siswa</a></li>
                        </ul>
                    </li>

                    <!-- Dropdown Guru -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Guru
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="daftar_guru.php">Daftar Guru</a></li>
                            <li><a class="dropdown-item" href="form_guru.php">Tambah Guru</a></li>
                        </ul>
                    </li>

                    <!-- Dropdown Mapel -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Mapel
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="tambah_mapel.php">Daftar Mapel</a></li>
                            <li><a class="dropdown-item" href="mapel.php">Tambah Mapel</a></li>
                        </ul>
                    </li>

                    <!-- Dropdown Mengajar -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Mengajar
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= $mengajarBasePath ?>daftar_mengajar.php">Daftar Mengajar</a></li>
                            <li><a class="dropdown-item" href="<?= $mengajarBasePath ?>form_tambah_mengajar.php">Tambah Mengajar</a></li>
                        </ul>
                    </li>

                    <!-- Menu Dinamis Berdasarkan Role -->
                    <?php if ($role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashbord_admin.php">Dashboard</a>
                        </li>
                    <?php elseif ($role === 'guru'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $mengajarBasePath ?>daftar_nilai2.php">Daftar Siswa</a>
                        </li>
                    <?php elseif ($role === 'siswa'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $mengajarBasePath ?>daftar_nilai_siswa.php">Nilai Siswa</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Form Pencarian & Profil di Kanan -->
                <div class="d-flex align-items-center gap-3">
                    <form class="d-flex" role="search">
                        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                        <button class="btn btn-outline-success" type="submit">Search</button>
                    </form>

                    <!-- Dropdown Profil / Admin -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle text-dark" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if (empty($hideProfileName)): ?>
                                <?php echo htmlspecialchars($nama); ?>
                            <?php endif; ?>
                            (<?php echo htmlspecialchars($username); ?>)
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= htmlspecialchars($koneksiBasePath . '/logout.php', ENT_QUOTES, 'UTF-8') ?>">Logout</a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gepN89oc7kPSqU42B9lrf2N2pxu7SgSGsV3R0Hj4VdQ0w5k3D3jWmE4bq6xPzQ" crossorigin="anonymous"></script>
</body>

</html>