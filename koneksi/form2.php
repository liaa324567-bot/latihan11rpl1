<?php 
require_once 'navbar.php'; 
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title mb-0 fw-bold">Form Tambah user 2</h4>
                </div>
                
                <div class="card-body">
                    <form action="form3.php" method="POST">
                        
                        <h5 class="fw-bold mb-4">Data Profil User 2</h5>
                        
                        <div class="mb-3">
                            <label for="nis" class="form-label">
                                Nomor Induk Siswa <span class="text-muted fw-normal ms-2">| Contoh: 12345678</span>
                            </label>
                            <input type="text" class="form-control" id="nis" name="nis" required>
                        </div>

                        <div class="mb-3">
                            <label for="nama_siswa" class="form-label">
                                Nama Lengkap<span class="text-muted fw-normal ms-2">| Contoh: Ujang A</span>
                            </label>
                            <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" required>
                        </div>

                        <div class="mb-3">
                            <label for="kelas" class="form-label">
                                Kelas Siswa <span class="text-muted fw-normal ms-2">| Contoh: XI-RPL-1</span>
                            </label>
                            <input type="text" class="form-control" id="kelas" name="kelas" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-block">Jenis Kelamin:</label>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="laki" value="L" required>
                                <label class="form-check-label" for="laki">Laki-laki</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="perempuan" value="P">
                                <label class="form-check-label" for="perempuan">Perempuan</label>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-4">Data Kredensial Akun (Tabel Users)</h5>

                        <div class="mb-3">
                            <label for="username" class="form-label">
                                Username Akun <span class="text-muted fw-normal ms-2">| Contoh: ujang123</span>
                            </label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">
                                Password Akun <span class="text-muted fw-normal ms-2">| Contoh: password123</span>
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <input type="hidden" name="role" value="siswa">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="tambah2" class="btn btn-primary">Simpan Data</button>
                            <a href="table.php" class="btn btn-secondary">Kembali</a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
