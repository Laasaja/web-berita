<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['id_users']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: login.php?error=".urlencode('Akses ditolak: hanya Admin'));
    exit;
}
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$action = trim($action);

if ($action === 'add') {
    $nama = trim($_POST['nama_kategori'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if ($nama === '') { header("Location: dashboard_admin.php?error=".urlencode('Nama kategori wajib')); exit; }
    if (mb_strlen($nama) > 100) $nama = mb_substr($nama,0,100);
    // Cek duplikat
    $cek = $conn->prepare("SELECT id_kategori FROM kategori WHERE nama_kategori=?");
    $cek->bind_param("s",$nama);
    $cek->execute();
    if($cek->get_result()->num_rows>0){ header("Location: dashboard_admin.php?error=".urlencode('Kategori sudah ada')); exit; }
    $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori, description) VALUES (?,?)");
    $stmt->bind_param("ss",$nama,$desc);
    if($stmt->execute()) header("Location: dashboard_admin.php?success=".urlencode('Kategori ditambahkan'));
    else header("Location: dashboard_admin.php?error=".urlencode('Gagal: '.$conn->error));
    exit;
}
if ($action === 'delete') {
    $id = $_GET['id'] ?? $_POST['id'] ?? null;
    if(!is_numeric($id)){ header("Location: dashboard_admin.php?error=".urlencode('ID tidak valid')); exit; }
    $id=(int)$id;
    // Jangan hapus jika ada artikel yang masih pakai kategori? Izinkan — set null
    $conn->query("UPDATE articles SET id_kategori=NULL WHERE id_kategori=$id");
    $stmt=$conn->prepare("DELETE FROM kategori WHERE id_kategori=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    header("Location: dashboard_admin.php?success=".urlencode('Kategori dihapus'));
    exit;
}
header("Location: dashboard_admin.php?error=".urlencode('Aksi tidak dikenal'));
exit;
