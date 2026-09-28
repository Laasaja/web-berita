<?php
session_start();
include_once("koneksi.php");

// Hanya Jurnalis atau Admin yang boleh hapus — server-side role check
if (!isset($_SESSION['id_users']) || !in_array($_SESSION['role'] ?? '', ['Jurnalis','Admin'], true)) {
    header("Location: login.php?error=".urlencode('Akses ditolak'));
    exit;
}

// Terima berbagai nama param: id, id_article, id_berita
$id = $_GET['id'] ?? $_GET['id_article'] ?? $_GET['id_berita'] ?? null;
if (!is_numeric($id)) {
    $dest = ($_SESSION['role']==='Admin') ? "dashboard_admin.php" : "dashboard_jurnalis.php";
    header("Location: $dest?error=".urlencode('ID tidak valid'));
    exit;
}
$id = (int)$id;
$id_users = $_SESSION['id_users'];
$is_admin = ($_SESSION['role'] === 'Admin');

// Validasi kepemilikan: Admin bisa hapus semua, Jurnalis hanya miliknya
if($is_admin){
    $cek = $conn->prepare("SELECT id_article FROM articles WHERE id_article = ?");
    $cek->bind_param("i", $id);
} else {
    $cek = $conn->prepare("SELECT id_article FROM articles WHERE id_article = ? AND id_users = ?");
    $cek->bind_param("ii", $id, $id_users);
}
$cek->execute();
if($cek->get_result()->num_rows === 0){
    $dest = $is_admin ? "dashboard_admin.php" : "dashboard_jurnalis.php";
    header("Location: $dest?error=".urlencode('Artikel tidak ditemukan atau bukan milik Anda'));
    exit;
}

// Hapus — prepared, validasi role di atas
if($is_admin){
    $stmt = $conn->prepare("DELETE FROM articles WHERE id_article = ?");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("DELETE FROM articles WHERE id_article = ? AND id_users = ?");
    $stmt->bind_param("ii", $id, $id_users);
}
$stmt->execute();

$dest = $is_admin ? "dashboard_admin.php" : "dashboard_jurnalis.php";
header("Location: $dest?sukses=".urlencode('Artikel berhasil dihapus'));
exit;
