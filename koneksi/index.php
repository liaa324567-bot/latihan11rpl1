<?php
session_start();

if (!isset($_SESSION['login'], $_SESSION['id'], $_SESSION['role'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$role = strtolower((string)($_SESSION['role'] ?? ''));

switch ($role) {
    case 'admin':
        header('Location: table.php');
        exit;

    case 'guru':
        header('Location: daftar_guru.php');
        exit;

    case 'siswa':
        header('Location: projek%20tugas%20besar/daftar_siswa2.php');
        exit;

    default:
        session_destroy();
        header('Location: login.php');
        exit;
}