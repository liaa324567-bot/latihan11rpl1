<?php
require_once 'koneksi.php';
require_once 'navbar.php';
?>

<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <h5 class="mb-0">Form Tambah Mata Pelajaran</h5>
    </div>
    <div class="card-body">
      <h4 class="card-title mb-4">Mata Pelajaran</h4>

      <form action="aksi tambah mapel.php" method="POST">
        <!-- Kode Mata Pelajaran -->
        <div class="mb-3">
          <label for="kode_mapel" class="form-label">Kode Mata Pelajaran</label>
          <input type="text" class="form-control" id="kode_mapel" name="kode_mapel" placeholder="Masukkan kode mata pelajaran" required>
        </div>

        <!-- Nama Mata Pelajaran -->
        <div class="mb-3">
          <label for="nama_mapel" class="form-label">Nama Mata Pelajaran</label>
          <input type="text" class="form-control" id="nama_mapel" name="nama_mapel" placeholder="Masukkan nama mata pelajaran" required>
        </div>

        <!-- Pilih Guru -->
        <div class="mb-3">
          <label for="id_guru" class="form-label">Pilih Guru</label>
          <select class="form-select" id="id_guru" name="id_guru" required>
            <option value="" selected disabled>Pilih Guru...</option>
            <?php
            // Ambil data guru dari database
            $query_guru = mysqli_query($koneksi, "SELECT * FROM guru");

            if ($query_guru && mysqli_num_rows($query_guru) > 0) {
                while ($guru = mysqli_fetch_assoc($query_guru)) {
                    // Catatan: Sesuaikan 'id_guru' dan 'nama_guru' dengan nama kolom di database kamu
                    echo "<option value='" . $guru['id'] . "'>" . $guru['nama'] . "</option>";
                }
            } else {
                echo "<option value='' disabled>Data guru belum ada/kosong</option>";
            }
            ?>
          </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn btn-primary">Simpan Data</button>
          <a href="tambah_mapel.php" class="btn btn-secondary">Kembali</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
// 2. Panggil footer di paling bawah file
require_once 'footer.php';
?>