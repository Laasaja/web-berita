<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['id_users']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: login.php?error=".urlencode('Akses ditolak: hanya Admin'));
    exit;
}
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$action = trim($action);

if ($action === 'create_jurnalis') {
    $nama = trim($_POST['nama_users'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pw = $_POST['password'] ?? '';
    $pw2 = $_POST['password2'] ?? '';
    // HARD-CODED role — tidak pernah baca $_POST['role']
    $role = 'Jurnalis';

    if($nama===''||$email===''||$pw===''){ header("Location: dashboard_admin.php?error=".urlencode('Semua field wajib')); exit; }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){ header("Location: dashboard_admin.php?error=".urlencode('Email tidak valid')); exit; }
    if(strlen($pw)<6){ header("Location: dashboard_admin.php?error=".urlencode('Password minimal 6 karakter')); exit; }
    if($pw!==$pw2){ header("Location: dashboard_admin.php?error=".urlencode('Konfirmasi password tidak cocok')); exit; }
    // Cek duplikat email
    $cek=$conn->prepare("SELECT id_users FROM users WHERE email=?");
    $cek->bind_param("s",$email);
    $cek->execute();
    if($cek->get_result()->num_rows>0){ header("Location: dashboard_admin.php?error=".urlencode('Email sudah terdaftar')); exit; }
    $hash=password_hash($pw, PASSWORD_DEFAULT);
    $stmt=$conn->prepare("INSERT INTO users (nama_users, role, email, password) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss",$nama,$role,$email,$hash);
    if($stmt->execute()) header("Location: dashboard_admin.php?success=".urlencode('Jurnalis berhasil dibuat: '.$email));
    else header("Location: dashboard_admin.php?error=".urlencode('Gagal: '.$conn->error));
    exit;
}
if ($action === 'delete') {
    $id = $_GET['id'] ?? $_POST['id'] ?? null;
    if(!is_numeric($id)){ header("Location: dashboard_admin.php?error=".urlencode('ID tidak valid')); exit; }
    $id=(int)$id;
    if($id=== (int)$_SESSION['id_users']){ header("Location: dashboard_admin.php?error=".urlencode('Tidak bisa hapus akun sendiri')); exit; }
    // Hapus user — artikel tetap (id_users akan jadi orphan? biarkan, atau set null)
    // Option: set articles id_users null if needed, but keep as is for history
    $stmt=$conn->prepare("DELETE FROM users WHERE id_users=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    // Optional: nullify articles author
    $conn->query("UPDATE articles SET id_users=NULL WHERE id_users=$id");
    header("Location: dashboard_admin.php?success=".urlencode('User dihapus'));
    exit;
}
header("Location: dashboard_admin.php?error=".urlencode('Aksi tidak dikenal'));
exit;
