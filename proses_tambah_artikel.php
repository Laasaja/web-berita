<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['id_users']) || !in_array($_SESSION['role'] ?? '', ['Jurnalis','Admin'], true)) {
    header("Location: login.php?error=".urlencode('Akses ditolak: hanya Jurnalis/Admin'));
    exit;
}

$title       = trim($_POST['title'] ?? '');
$isi         = trim($_POST['isi_berita'] ?? '');
$id_kategori = $_POST['id_kategori'] ?? '';
$status      = $_POST['status'] ?? 'draft';
$id_users    = $_SESSION['id_users'];

// Validasi input tidak boleh kosong — server side
if ($title === '' || $isi === '' || $id_kategori === '' ) {
    header("Location: tambah_artikel.php?error=".urlencode('Semua field wajib diisi'));
    exit;
}
if(!is_numeric($id_kategori)){ header("Location: tambah_artikel.php?error=".urlencode('Kategori tidak valid')); exit; }
if(!in_array($status, ['draft','published','review','archived'], true)) $status='draft';
if(mb_strlen($title)>255) $title=mb_substr($title,0,255);

// published_at diisi otomatis kalau status published, kalau draft biarkan NULL
$published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;

// Simpan ke database — prepared statement, id_users diambil dari session bukan POST
$stmt = $conn->prepare("
    INSERT INTO articles (title, isi_berita, id_kategori, id_users, status, published_at)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("ssiiss", $title, $isi, $id_kategori, $id_users, $status, $published_at);

if ($stmt->execute()) {
    $dest = ($_SESSION['role']==='Admin') ? "dashboard_admin.php" : "dashboard_jurnalis.php";
    header("Location: $dest?sukses=".urlencode('Artikel berhasil disimpan'));
    exit;
} else {
    header("Location: tambah_artikel.php?error=".urlencode('Gagal menyimpan artikel'));
    exit;
}
