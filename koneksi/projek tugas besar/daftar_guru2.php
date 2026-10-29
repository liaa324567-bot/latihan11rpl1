<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../navbar.php';


$sql = "SELECT u.username, u.role, g.nama,
        GROUP_CONCAT(DISTINCT m.nama_mapel ORDER BY m.nama_mapel SEPARATOR ', ') AS mapel
        FROM guru g
        LEFT JOIN users u ON u.id = g.user_id
        LEFT JOIN mapel m ON m.guru_id = g.id
        GROUP BY g.id, u.username, u.role, g.nama
        ORDER BY g.id DESC";
$data = mysqli_query($koneksi, $sql);
if (!$data) {
    die('Gagal mengambil data guru: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2 class='text-center'>Daftar Guru</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Nama</th>
                    <th>Mapel</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($d = mysqli_fetch_assoc($data)) { ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($d['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($d['role'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($d['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($d['mapel'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>