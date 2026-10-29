<?php
session_start();
$errorLogin = $_SESSION['error_login'] ?? '';
unset($_SESSION['error_login']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-card {
            width: 100%;
            max-width: 360px;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-sm p-4 login-card border-0 rounded-3">
        <h3 class="text-center mb-4 text-secondary">Login</h3>

        <?php if ($errorLogin !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errorLogin, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form action="aksi_login.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label text-muted small">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan Username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label text-muted small">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 my-2">Login</button>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">Belum memiliki akun? <a href="register.php" class="text-decoration-none">Register</a></small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>