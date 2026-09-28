<?php
session_start();
include "koneksi.php";

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header("Location: login.php?error=".urlencode('Email dan password wajib diisi'));
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    $login_ok = password_verify($password, $user['password']);

    // Fallback: data lama plain text — migrasikan ke hash
    if (!$login_ok && $password === $user['password']) {
        $login_ok = true;
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET password = ? WHERE id_users = ?");
        $upd->bind_param("si", $new_hash, $user['id_users']);
        $upd->execute();
    }

    if ($login_ok) {
        // Regenerate session id untuk cegah fixation
        session_regenerate_id(true);
        $_SESSION['id_users']   = $user['id_users'];
        $_SESSION['nama_users'] = $user['nama_users'];
        $_SESSION['email']      = $user['email'];
        $_SESSION['role']       = $user['role'];

        // Redirect sesuai role — strict
        if ($user['role'] === 'Admin') {
            header("Location: dashboard_admin.php");
        } elseif ($user['role'] === 'Jurnalis') {
            header("Location: dashboard_jurnalis.php");
        } else {
            // Pembaca dan role lain ke homepage publik
            header("Location: index.php");
        }
        exit;
    } else {
        header("Location: login.php?error=" . urlencode("Email atau password salah"));
        exit;
    }
} else {
    header("Location: login.php?error=" . urlencode("Email atau password salah"));
    exit;
}
