<?php
session_start();
include "koneksi.php";

// PUBLIC: tidak perlu login untuk membaca berita (revisi autentikasi)
$kategori_aktif = trim($_GET['kategori'] ?? '');
$q              = trim($_GET['q'] ?? '');
$is_logged = isset($_SESSION['id_users']);
$role_user = $_SESSION['role'] ?? null;

// Daftar kategori untuk menu & sidebar
$kategori_list = $conn->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori")->fetch_all(MYSQLI_ASSOC);

// Hitung jumlah artikel per kategori (published only) untuk widget
$cat_counts = [];
$rc = $conn->query("SELECT k.nama_kategori, COUNT(a.id_article) as jml FROM kategori k LEFT JOIN articles a ON a.id_kategori=k.id_kategori AND a.status='published' GROUP BY k.id_kategori ORDER BY k.nama_kategori");
if($rc){ while($r=$rc->fetch_assoc()) $cat_counts[$r['nama_kategori']] = (int)$r['jml']; }

// Query berita utama (filtered)
$sql = "
    SELECT articles.*, users.nama_users, users.email, kategori.nama_kategori
    FROM articles
    JOIN users ON articles.id_users = users.id_users
    LEFT JOIN kategori ON kategori.id_kategori = articles.id_kategori
    WHERE articles.status = 'published'
";
$types  = "";
$params = [];

if ($kategori_aktif !== '') {
    $sql .= " AND kategori.nama_kategori = ?";
    $types .= "s";
    $params[] = $kategori_aktif;
}
if ($q !== '') {
    $sql .= " AND (articles.title LIKE ? OR articles.isi_berita LIKE ?)";
    $types .= "ss";
    $like = "%{$q}%";
    $params[] = $like;
    $params[] = $like;
}
$sql .= " ORDER BY articles.published_at DESC";

$stmt = $conn->prepare($sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$allArticles = $result->fetch_all(MYSQLI_ASSOC);

// Untuk homepage tanpa filter: pisahkan featured & latest
$is_home = ($kategori_aktif === '' && $q === '');
$featured = null;
$latest = [];
$listMore = [];
if($is_home && !empty($allArticles)){
    $featured = $allArticles[0];
    $latest = array_slice($allArticles, 1, 4); // 4 cards grid
    $listMore = array_slice($allArticles, 5); // sisa sebagai list
} else {
    $latest = $allArticles;
}

// Breaking news: ambil 1 terbaru
$breaking = $featured ?? ($allArticles[0] ?? null);

// Popular: 5 terbaru (jika ada view count, ganti ORDER BY views DESC)
$popular = [];
$pr = $conn->query("SELECT a.id_article, a.title, a.published_at, k.nama_kategori FROM articles a LEFT JOIN kategori k ON k.id_kategori=a.id_kategori WHERE a.status='published' ORDER BY a.published_at DESC LIMIT 5");
if($pr){ $popular = $pr->fetch_all(MYSQLI_ASSOC); }

function tgl_short($dt){ return $dt ? date('d M Y', strtotime($dt)) : '—'; }
function img_seed($title,$w=600,$h=400){ $seed=substr(md5($title),0,8); return "https://picsum.photos/seed/{$seed}/{$w}/{$h}"; }
function excerpt($text,$len=160){ $t = trim(strip_tags($text)); if(mb_strlen($t) > $len) return mb_substr($t,0,$len).'…'; return $t; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEWS — Portal Berita Terkini</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="description" content="Portal berita NEWS — Politik, Teknologi, Olahraga, Lifestyle. Berita terkini, terpercaya, minimal & profesional.">
</head>
<body>

<div class="topbar">
    <div class="container">
        <div class="topbar__left"><span><?= date('l, d M Y') ?></span><span style="opacity:.35">|</span><span>Portal Berita Terpercaya</span></div>
        <div class="topbar__right">
            <?php if($is_logged): ?>
                <span style="color:#9CA3AF">Halo, <?= htmlspecialchars($_SESSION['nama_users']) ?></span>
                <?php if($role_user==='Admin'): ?><a href="dashboard_admin.php" style="color:#FECACA;font-weight:600">Admin</a><?php endif; ?>
                <?php if($role_user==='Jurnalis'): ?><a href="dashboard_jurnalis.php" style="color:#FECACA;font-weight:600">Dashboard</a><?php endif; ?>
                <a href="logout.php">Keluar</a>
            <?php else: ?>
                <a href="login.php">Masuk</a><a href="signup.php" style="color:#fff;font-weight:600">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<header class="header">
    <div class="container">
        <div class="header__inner">
            <a href="dashboard_user.php" class="brand"><span class="brand__mark">N</span><span class="brand__text">NEWS</span></a>
            <nav class="nav" id="mainNav">
                <a href="dashboard_user.php" class="<?= $kategori_aktif==='' && $q==='' ? 'active':'' ?>">Home</a>
                <?php foreach($kategori_list as $k): ?>
                    <a href="dashboard_user.php?kategori=<?= urlencode($k['nama_kategori']) ?>" class="<?= $kategori_aktif===$k['nama_kategori'] ? 'active':'' ?>"><?= htmlspecialchars(strtoupper($k['nama_kategori'])) ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="header__actions">
                <form class="search" action="dashboard_user.php" method="get">
                    <?php if($kategori_aktif!==''): ?><input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori_aktif) ?>"><?php endif; ?>
                    <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari berita...">
                    <button type="submit" aria-label="Cari">⌕</button>
                </form>
                <?php if($is_logged && $role_user==='Jurnalis'): ?>
                    <a href="tambah_artikel.php" class="btn btn--primary btn--sm">Tulis</a>
                <?php elseif($is_logged && $role_user==='Admin'): ?>
                    <a href="dashboard_admin.php" class="btn btn--primary btn--sm">Kelola</a>
                <?php elseif(!$is_logged): ?>
                    <a href="login.php" class="btn btn--ghost btn--sm" style="border-radius:999px">Login</a>
                <?php else: ?>
                    <a href="logout.php" class="btn btn--ghost btn--sm" style="border-radius:999px">Keluar</a>
                <?php endif; ?>
                <button class="hamburger" id="hamburger" aria-label="Menu">≡</button>
            </div>
        </div>
    </div>
</header>

<?php if($is_home && $breaking): ?>
<div class="breaking">
    <div class="container">
        <div class="breaking__inner">
            <span class="breaking__label">BREAKING NEWS</span>
            <div class="breaking__track">
                <div class="breaking__headline"><a href="detail_berita.php?id=<?= $breaking['id_article'] ?>"><?= htmlspecialchars($breaking['title']) ?></a></div>
            </div>
            <span class="breaking__meta"><?= tgl_short($breaking['published_at']) ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container">
    <?php if(!$is_home): ?>
        <div style="padding:18px 0 0;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
            <p class="small muted">
                <?php if($kategori_aktif!==''): ?>Kategori: <strong style="color:var(--text)"><?= htmlspecialchars($kategori_aktif) ?></strong> · <?php endif; ?>
                <?php if($q!==''): ?>Pencarian: <strong style="color:var(--text)">"<?= htmlspecialchars($q) ?>"</strong> · <?php endif; ?>
                Ditemukan <strong><?= count($allArticles) ?></strong> artikel
            </p>
            <a href="dashboard_user.php" class="btn btn--ghost btn--sm">Reset filter</a>
        </div>
    <?php endif; ?>

    <div class="layout">
        <div class="maincol">

            <?php if($is_home): ?>
                <?php if($featured): ?>
                <article class="featured">
                    <a href="detail_berita.php?id=<?= $featured['id_article'] ?>" class="featured__media">
                        <img src="<?= img_seed($featured['title'], 1200, 675) ?>" alt="<?= htmlspecialchars($featured['title']) ?>" loading="eager">
                    </a>
                    <div class="featured__body">
                        <span class="cat"><?= htmlspecialchars($featured['nama_kategori'] ?? 'Berita') ?></span>
                        <h1 class="featured__title"><a href="detail_berita.php?id=<?= $featured['id_article'] ?>"><?= htmlspecialchars($featured['title']) ?></a></h1>
                        <p class="featured__excerpt"><?= htmlspecialchars(excerpt($featured['isi_berita'], 180)) ?></p>
                        <div class="meta"><span>Oleh <?= htmlspecialchars($featured['nama_users']) ?></span><span class="dot"></span><time><?= tgl_short($featured['published_at']) ?></time></div>
                    </div>
                </article>
                <?php else: ?>
                    <div class="empty" style="background:#fff;border:1px dashed var(--border);border-radius:12px;padding:32px;text-align:center">Belum ada berita published.</div>
                <?php endif; ?>

                <?php if(!empty($latest)): ?>
                <div class="section-head">
                    <h2>Berita Terbaru</h2>
                    <a href="dashboard_user.php">Lihat semua →</a>
                </div>
                <div class="grid-cards">
                    <?php foreach($latest as $row): ?>
                    <article class="card">
                        <a href="detail_berita.php?id=<?= $row['id_article'] ?>" class="card__media"><img src="<?= img_seed($row['title'], 600, 375) ?>" alt="" loading="lazy"></a>
                        <div class="card__body">
                            <span class="cat"><?= htmlspecialchars($row['nama_kategori'] ?? 'Berita') ?></span>
                            <h3 class="card__title"><a href="detail_berita.php?id=<?= $row['id_article'] ?>"><?= htmlspecialchars($row['title']) ?></a></h3>
                            <p class="card__excerpt"><?= htmlspecialchars(excerpt($row['isi_berita'], 110)) ?></p>
                            <div class="meta"><time><?= tgl_short($row['published_at']) ?></time><span class="dot"></span><span><?= htmlspecialchars($row['nama_users']) ?></span></div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if(!empty($listMore)): ?>
                <div class="section-head"><h2>Lainnya</h2></div>
                <div class="list-cards">
                    <?php foreach($listMore as $row): ?>
                    <article class="list-card">
                        <a href="detail_berita.php?id=<?= $row['id_article'] ?>" class="list-card__media"><img src="<?= img_seed($row['title'], 400, 260) ?>" alt="" loading="lazy"></a>
                        <div>
                            <span class="cat" style="font-size:10px;padding:3px 7px"><?= htmlspecialchars($row['nama_kategori'] ?? 'Berita') ?></span>
                            <h3 class="list-card__title"><a href="detail_berita.php?id=<?= $row['id_article'] ?>"><?= htmlspecialchars($row['title']) ?></a></h3>
                            <p class="small muted" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.5"><?= htmlspecialchars(excerpt($row['isi_berita'], 120)) ?></p>
                            <div class="meta" style="margin-top:6px"><time><?= tgl_short($row['published_at']) ?></time></div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Filtered view -->
                <?php if(!empty($allArticles)): ?>
                    <div class="list-cards" style="margin-top:18px">
                    <?php foreach($allArticles as $row): ?>
                        <article class="list-card">
                            <a href="detail_berita.php?id=<?= $row['id_article'] ?>" class="list-card__media"><img src="<?= img_seed($row['title'], 400, 260) ?>" alt="" loading="lazy"></a>
                            <div>
                                <span class="cat" style="font-size:10px;padding:3px 7px"><?= htmlspecialchars($row['nama_kategori'] ?? 'Berita') ?></span>
                                <h3 class="list-card__title"><a href="detail_berita.php?id=<?= $row['id_article'] ?>"><?= htmlspecialchars($row['title']) ?></a></h3>
                                <p class="small muted" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?= htmlspecialchars(excerpt($row['isi_berita'], 120)) ?></p>
                                <div class="meta" style="margin-top:6px"><span><?= htmlspecialchars($row['nama_users']) ?></span><span class="dot"></span><time><?= tgl_short($row['published_at']) ?></time></div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty" style="background:#fff;border:1px dashed var(--border);border-radius:12px;padding:36px;text-align:center;margin-top:18px">
                        <p style="font-size:28px;margin-bottom:8px">🔍</p>
                        <p style="font-weight:700">Tidak ada hasil</p>
                        <p class="small muted">Coba kata kunci lain atau reset filter.</p>
                        <a href="dashboard_user.php" class="btn btn--primary btn--sm" style="margin-top:12px">Kembali ke Beranda</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>

        <aside class="sidebar">
            <div class="widget popular">
                <div class="widget__title">Berita Populer</div>
                <?php if(!empty($popular)): foreach($popular as $i=>$p): ?>
                <div class="pop-item">
                    <div class="pop-rank"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></div>
                    <div>
                        <div class="pop-title"><a href="detail_berita.php?id=<?= $p['id_article'] ?>"><?= htmlspecialchars($p['title']) ?></a></div>
                        <div class="pop-meta"><?= htmlspecialchars($p['nama_kategori'] ?? 'Berita') ?> · <?= tgl_short($p['published_at']) ?></div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                    <p class="small muted">Belum ada data populer.</p>
                <?php endif; ?>
            </div>

            <div class="widget">
                <div class="widget__title">Kategori</div>
                <div class="cat-list">
                    <?php foreach($kategori_list as $k): ?>
                        <a class="cat-link" href="dashboard_user.php?kategori=<?= urlencode($k['nama_kategori']) ?>">
                            <span><?= htmlspecialchars($k['nama_kategori']) ?></span>
                            <span class="cat-count"><?= $cat_counts[$k['nama_kategori']] ?? 0 ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="newsletter">
                <h4>Tetap update</h4>
                <p>Dapatkan ringkasan berita terbaik setiap pagi. Tanpa spam.</p>
                <form action="dashboard_user.php" method="get" style="display:flex;gap:8px">
                    <input type="search" name="q" placeholder="Cari topik..." style="flex:1;padding:9px 12px;border-radius:8px;border:1px solid #1F2937;background:#1F2937;color:#fff;font-size:13px;outline:0">
                    <button type="submit" class="btn btn--primary btn--sm" style="white-space:nowrap">Cari</button>
                </form>
            </div>

            <div class="widget" style="background:var(--accent-light);border-color:var(--accent-border)">
                <div style="font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--accent);margin-bottom:8px">INFO REDAKSI</div>
                <p class="small" style="color:var(--text2);line-height:1.6">NEWS berkomitmen pada jurnalisme yang akurat dan berimbang. Lihat <a href="dashboard_user.php" style="color:var(--accent);font-weight:600">semua berita</a> atau hubungi redaksi untuk koreksi.</p>
            </div>
        </aside>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="footer__inner">
            <div>
                <a href="dashboard_user.php" class="brand footer__brand"><span class="brand__mark">N</span><span class="brand__text">NEWS</span></a>
                <p style="margin-top:10px;font-size:13px;line-height:1.6;color:#9CA3AF;max-width:360px">Portal berita modern, minimalis, dan terpercaya. Fokus pada isi, cepat diakses, nyaman di semua perangkat.</p>
            </div>
            <div>
                <h5>Kategori</h5>
                <?php foreach(array_slice($kategori_list,0,6) as $k): ?>
                    <a href="dashboard_user.php?kategori=<?= urlencode($k['nama_kategori']) ?>"><?= htmlspecialchars($k['nama_kategori']) ?></a>
                <?php endforeach; ?>
            </div>
            <div>
                <h5>Bantuan</h5>
                <a href="index.php">Home</a>
                <a href="login.php">Masuk</a>
                <a href="signup.php">Daftar</a>
                <?php if($is_logged && $role_user==='Jurnalis'): ?><a href="dashboard_jurnalis.php">Dashboard Jurnalis</a><?php endif; ?>
                <?php if($is_logged && $role_user==='Admin'): ?><a href="dashboard_admin.php">Dashboard Admin</a><?php endif; ?>
                <?php if($is_logged): ?><a href="logout.php">Keluar</a><?php endif; ?>
            </div>
        </div>
        <div class="footer__bottom">
            <span>&copy; <?= date('Y') ?> NEWS. All rights reserved.</span>
            <div style="display:flex;gap:12px">
                <a href="#" style="color:#9CA3AF">Facebook</a>
                <a href="#" style="color:#9CA3AF">X (Twitter)</a>
                <a href="#" style="color:#9CA3AF">Instagram</a>
            </div>
        </div>
    </div>
</footer>

<script>document.getElementById('hamburger')?.addEventListener('click',()=>document.getElementById('mainNav')?.classList.toggle('open'))</script>
</body>
</html>
