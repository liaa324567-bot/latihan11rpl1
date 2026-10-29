<?php 
require_once 'navbar.php';
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title mb-0 fw-bold">Form Tambah User (Admin)</h4>
                </div>
                
                <div class="card-body">
                    <form action="form3.php" method="POST">
                        <h5 class="fw-bold mb-4">Data Kredensial Akun</h5>

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
                            <input type="hidden" name="role" value="admin">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="tambah admin" class="btn btn-primary">Simpan Data</button>
                            <a href="#" class="btn btn-secondary">Kembali</a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
