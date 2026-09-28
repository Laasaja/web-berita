<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['id_users']) || !in_array($_SESSION['role'] ?? '', ['Jurnalis','Admin'], true)) {
    header("Location: login.php?error=".urlencode('Akses ditolak'));
    exit;
}
$id_article = $_POST['id_article'] ?? null;
$title = trim($_POST['title'] ?? '');
$isi = trim($_POST['isi_berita'] ?? '');
$id_kategori = $_POST['id_kategori'] ?? '';
$status = $_POST['status'] ?? 'draft';
$id_users = $_SESSION['id_users'];
$is_admin = ($_SESSION['role'] === 'Admin');

// Validasi input + validasi ID & kategori (prepared + server-side)
if(!is_numeric($id_article) || $title==='' || $isi==='' || $id_kategori===''){
    header("Location: edit_artikel.php?id=".urlencode($id_article)."&error=".urlencode('Semua field wajib diisi'));
    exit;
}
$id_article = (int)$id_article;
if(!in_array($status, ['draft','published','review','archived'], true)){
    $status = 'draft';
}
if(!is_numeric($id_kategori)){ header("Location: edit_artikel.php?id=".$id_article."&error=".urlencode('Kategori tidak valid')); exit; }

// Pastikan milik user ini — Admin boleh edit semua
if($is_admin){
    $cek = $conn->prepare("SELECT published_at, status FROM articles WHERE id_article = ?");
    $cek->bind_param("i", $id_article);
} else {
    $cek = $conn->prepare("SELECT published_at, status FROM articles WHERE id_article = ? AND id_users = ?");
    $cek->bind_param("ii", $id_article, $id_users);
}
$cek->execute();
$old = $cek->get_result()->fetch_assoc();
if(!$old){
    header("Location: ".($is_admin ? "dashboard_admin.php" : "dashboard_jurnalis.php")."?error=".urlencode('Artikel tidak ditemukan'));
    exit;
}

// published_at logic
$published_at = $old['published_at'];
if($status === 'published' && empty($published_at)){
    $published_at = date('Y-m-d H:i:s');
}

// Admin: update tanpa filter id_users; Jurnalis: filter miliknya
if($is_admin){
    $stmt = $conn->prepare("UPDATE articles SET title=?, isi_berita=?, id_kategori=?, status=?, published_at=? WHERE id_article=?");
    $stmt->bind_param("ssissi", $title, $isi, $id_kategori, $status, $published_at, $id_article);
} else {
    $stmt = $conn->prepare("UPDATE articles SET title=?, isi_berita=?, id_kategori=?, status=?, published_at=? WHERE id_article=? AND id_users=?");
    $stmt->bind_param("ssissii", $title, $isi, $id_kategori, $status, $published_at, $id_article, $id_users);
}

if($stmt->execute()){
    header("Location: ".($is_admin ? "dashboard_admin.php" : "dashboard_jurnalis.php")."?sukses=".urlencode('Artikel berhasil diperbarui'));
    exit;
} else {
    header("Location: edit_artikel.php?id=".$id_article."&error=".urlencode('Gagal memperbarui artikel'));
    exit;
}
