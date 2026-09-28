<?php
session_start();
include "koneksi.php";

$nama  = trim($_POST['nama_users'] ?? '');
$email = trim($_POST['email'] ?? '');
$password_raw = $_POST['password'] ?? '';

// HARDENING: Jangan pernah ambil role dari $_POST. Default aman = Pembaca.
$role = 'Pembaca';

// Validasi input
if ($nama === '' || $email === '' || $password_raw === '') {
    header("Location: signup.php?error=".urlencode('Semua field wajib diisi'));
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: signup.php?error=".urlencode('Email tidak valid'));
    exit;
}
if (strlen($password_raw) < 6) {
    header("Location: signup.php?error=".urlencode('Password minimal 6 karakter'));
    exit;
}
$password = password_hash($password_raw, PASSWORD_DEFAULT);

// Cek email sudah dipakai atau belum — prepared statement
$cek = $conn->prepare("SELECT id_users FROM users WHERE email = ?");
$cek->bind_param("s", $email);
$cek->execute();
$hasil = $cek->get_result();

if ($hasil->num_rows > 0) {
    header("Location: signup.php?error=".urlencode('Email sudah terdaftar'));
    exit;
} else {
    // Role diset server-side, bukan dari POST — cegah privilege escalation via hidden input/POST manipulation
    $stmt = $conn->prepare("INSERT INTO users (nama_users, role, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $role, $email, $password);

    if ($stmt->execute()) {
        // Pembaca langsung diarahkan ke login
        header("Location: login.php?success=".urlencode('Akun berhasil dibuat. Silakan login.'));
        exit;
    } else {
        header("Location: signup.php?error=".urlencode('Gagal mendaftar: '.$conn->error));
        exit;
    }
}
