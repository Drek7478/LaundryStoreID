<?php
/**
 * LaundryStoreID - User Dashboard
 * 
 * Halaman utama setelah user login
 * Menampilkan hero section, stats, kategori, dan produk terbaru
 */

require_once '../config/db.php';

// Proteksi: Hanya user yang sudah login dan BUKAN admin
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}
if (isAdmin()) {
    redirect('../admin/dashboard.php');
}

include '../includes/header.php';
include '../includes/navbar_user.php';

// ============================================
// AMBIL DATA USER
// ============================================
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// ============================================
// HITUNG STATISTIK USER
// ============================================

// Pesanan aktif (menunggu pembayaran, menunggu konfirmasi, dikonfirmasi)
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM pesanan 
    WHERE id_user = ? 
    AND status IN ('menunggu_pembayaran', 'menunggu_konfirmasi', 'dikonfirmasi')
");
$stmt->execute([$_SESSION['user_id']]);
$pesanan_aktif = $stmt->fetch()['total'];

// Total belanja (semua pesanan kecuali dibatalkan)
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(total_harga), 0) as total 
    FROM pesanan 
    WHERE id_user = ? 
    AND status != 'dibatalkan'
");
$stmt->execute([$_SESSION['user_id']]);
$total_belanja = $stmt->fetch()['total'];

// Total produk tersedia
$stmt = $pdo->query("SELECT COUNT(*) as total FROM produk WHERE stok > 0");
$produk_tersedia = $stmt->fetch()['total'];

// Pesanan terbaru user (3 pesanan)
$stmt = $pdo->prepare("
    SELECT * FROM pesanan 
    WHERE id_user = ? 
    ORDER BY created_at DESC 
    LIMIT 3
");
$stmt->execute([$_SESSION['user_id']]);
$pesanan_terbaru = $stmt->fetchAll();

// ============================================
// AMBIL DATA PRODUK & KATEGORI
// ============================================

// 6 kategori untuk section kategori
$stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori LIMIT 6");
$kategori_list = $stmt->fetchAll();

// 4 produk terbaru
$stmt = $pdo->query("
    SELECT p.*, k.nama_kategori 
    FROM produk p 
    LEFT JOIN kategori k ON p.id_kategori = k.id 
    WHERE p.stok > 0 
    ORDER BY p.created_at DESC 
    LIMIT 4
");
$produk_terbaru = $stmt->fetchAll();

// 4 produk terlaris (berdasarkan jumlah di detail_pesanan)
$stmt = $pdo->query("
    SELECT p.*, k.nama_kategori, 
           COALESCE(SUM(dp.jumlah), 0) as total_terjual
    FROM produk p 
    LEFT JOIN kategori k ON p.id_kategori = k.id 
    LEFT JOIN detail_pesanan dp ON p.id = dp.id_produk
    WHERE p.stok > 0
    GROUP BY p.id
    ORDER BY total_terjual DESC 
    LIMIT 4
");
$produk_terlaris = $stmt->fetchAll();

// Nama depan user untuk greeting
$nama_depan = explode(' ', $user['nama'])[0];

// Waktu saat ini untuk greeting
$jam = (int)date('H');
if ($jam >= 5 && $jam < 12) {
    $greeting = 'Selamat Pagi';
} elseif ($jam >= 12 && $jam < 15) {
    $greeting = 'Selamat Siang';
} elseif ($jam >= 15 && $jam < 18) {
    $greeting = 'Selamat Sore';
} else {
    $greeting = 'Selamat Malam';
}
?>

<!-- ============================================
     HERO SECTION
     ============================================ -->
<section class="hero-section">
    <!-- Background Glows -->
    <div class="hero-glow-1"></div>
    <div class="hero-glow-2"></div>
    
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <!-- Hero Text -->
            <div class="col-lg-7">
                <!-- Greeting Badge -->
                <div class="hero-badge">
                    <i class="fas fa-hand-wave" style="font-size: 16px;"></i>
                    <span><?php echo $greeting; ?>, <?php echo $nama_depan; ?>!</span>
                </div>
                
                <!-- Main Heading -->
                <h1 class="hero-title">
                    Semua Kebutuhan Laundry<br>Ada Di Sini
                </h1>
                
                <!-- Subtitle -->
                <p class="hero-subtitle">
                    Dapatkan produk laundry berkualitas premium untuk kebutuhan 
                    rumah tangga maupun bisnis laundry Anda. Harga terbaik, 
                    kualitas terjamin, pengiriman cepat.
                </p>
                
                <!-- CTA Buttons -->
                <div class="d-flex gap-3 flex-wrap">
                    <a href="produk.php" class="btn btn-hero">
                        <i class="fas fa-store me-2"></i> Lihat Katalog
                    </a>
                    <a href="riwayat.php" class="btn btn-hero-outline">
                        <i class="fas fa-clipboard-list me-2"></i> Pesanan Saya
                    </a>
                </div>
            </div>
            
            <!-- Hero Image/Icon -->
            <div class="col-lg-5 text-center mt-4 mt-lg-0">
                <div class="hero-emoji">
                    <i class="fas fa-jug-detergent" style="font-size: 8rem; color: rgba(255,255,255,0.9); filter: drop-shadow(0 10px 30px rgba(0,0,0,0.3));"></i>
                </div>
                <!-- Floating badges -->
                <div style="position: relative; margin-top: -40px;">
                    <span class="badge" style="background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; border-radius: 100px; font-size: 13px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="fas fa-check-circle me-1"></i> 100% Original
                    </span>
                    <span class="badge ms-2" style="background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; border-radius: 100px; font-size: 13px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="fas fa-truck-fast me-1"></i> Gratis Ongkir
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     STATS BAR (3 Kartu di bawah hero)
     ============================================ -->
<div class="container" style="margin-top: -50px; position: relative; z-index: 10;">
    <div class="row g-4">
        <!-- Pesanan Aktif -->
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon-wrap violet">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div class="stat-value"><?php echo $pesanan_aktif; ?></div>
                    <div class="stat-label">Pesanan Aktif</div>
                    <?php if ($pesanan_aktif > 0): ?>
                    <small style="color: var(--color-primary); font-weight: 600;">
                        <a href="riwayat.php" style="color: var(--color-primary);">Lihat detail →</a>
                    </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Total Belanja -->
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon-wrap cyan">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <div class="stat-value">
                        <?php if ($total_belanja > 0): ?>
                            Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?>
                        <?php else: ?>
                            Rp 0
                        <?php endif; ?>
                    </div>
                    <div class="stat-label">Total Belanja</div>
                    <small style="color: var(--color-text-muted);">Semua transaksi</small>
                </div>
            </div>
        </div>
        
        <!-- Produk Tersedia -->
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon-wrap emerald">
                    <i class="fas fa-store-alt"></i>
                </div>
                <div>
                    <div class="stat-value"><?php echo $produk_tersedia; ?>+</div>
                    <div class="stat-label">Produk Tersedia</div>
                    <small style="color: var(--color-text-muted);">Siap dipesan</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================
     KATEGORI PRODUK
     ============================================ -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-800 mb-1">
                <i class="fas fa-th-large me-2" style="color: var(--color-primary);"></i>
                Kategori Produk
            </h3>
            <div style="width: 50px; height: 4px; background: var(--gradient-primary); border-radius: 2px;"></div>
        </div>
        <a href="produk.php" class="btn btn-outline btn-sm" style="border-radius: 10px;">
            Semua Kategori <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
    
    <div class="row g-3">
        <?php
        // Ikon untuk setiap kategori
        $category_icons = [
            'fa-flask',        // Deterjen
            'fa-wind',         // Pewangi
            'fa-star',         // Pemutih
            'fa-toolbox',      // Alat Laundry
            'fa-box',          // Kemasan
            'fa-soap',         // Lainnya
        ];
        
        foreach ($kategori_list as $i => $kat):
            $icon = $category_icons[$i % count($category_icons)];
            $colors = ['#7C3AED', '#06B6D4', '#10B981', '#F59E0B', '#EF4444', '#3B82F6'];
            $color = $colors[$i % count($colors)];
        ?>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="produk.php?kategori=<?php echo $kat['id']; ?>" class="text-decoration-none">
                <div class="card text-center p-3 card-hover" style="border-radius: var(--radius-lg);">
                    <div style="width: 56px; height: 56px; border-radius: 14px; background: <?php echo $color; ?>15; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                        <i class="fas <?php echo $icon; ?>" style="font-size: 1.5rem; color: <?php echo $color; ?>;"></i>
                    </div>
                    <h6 class="mb-1" style="font-size: 13px; font-weight: 600;"><?php echo $kat['nama_kategori']; ?></h6>
                    <small class="text-muted" style="font-size: 11px;">Lihat Produk</small>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
        
        <?php if (count($kategori_list) == 0): ?>
        <div class="col-12 text-center py-4">
            <p class="text-muted">Belum ada kategori tersedia</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================
     PRODUK TERBARU
     ============================================ -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-800 mb-1">
                <i class="fas fa-box-open me-2" style="color: var(--color-primary);"></i>
                Produk Terbaru
            </h3>
            <div style="width: 50px; height: 4px; background: var(--gradient-primary); border-radius: 2px;"></div>
        </div>
        <a href="produk.php" class="btn btn-outline btn-sm" style="border-radius: 10px;">
            Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
    
    <div class="row g-4">
        <?php if (count($produk_terbaru) > 0): ?>
            <?php foreach ($produk_terbaru as $produk): ?>
            <div class="col-6 col-lg-3">
                <div class="product-card">
                    <!-- Product Image -->
                    <div class="product-card-img-wrapper">
                        <?php if ($produk['gambar']): ?>
                        <img src="../assets/img/produk/<?php echo $produk['gambar']; ?>" 
                             alt="<?php echo $produk['nama_produk']; ?>" 
                             loading="lazy">
                        <?php else: ?>
                        <div class="product-card-img-placeholder">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Stock Badge -->
                        <?php if ($produk['stok'] > 0 && $produk['stok'] <= 10): ?>
                        <span class="product-stock-badge">
                            <i class="fas fa-bolt me-1"></i> Stok Terbatas
                        </span>
                        <?php elseif ($produk['stok'] == 0): ?>
                        <div class="product-out-stock-overlay">
                            <i class="fas fa-times-circle me-2"></i> Stok Habis
                        </div>
                        <?php endif; ?>
                        
                        <!-- Quick Add Button -->
                        <?php if ($produk['stok'] > 0): ?>
                        <form method="POST" action="keranjang.php">
                            <input type="hidden" name="id_produk" value="<?php echo $produk['id']; ?>">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="jumlah" value="1">
                            <button type="submit" class="product-quick-add" title="Tambah ke Keranjang">
                                <i class="fas fa-plus"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="product-card-body">
                        <!-- Kategori -->
                        <div class="product-category-label">
                            <i class="fas fa-tag me-1"></i> 
                            <?php echo $produk['nama_kategori'] ?? 'Umum'; ?>
                        </div>
                        
                        <!-- Nama Produk -->
                        <a href="detail_produk.php?id=<?php echo $produk['id']; ?>" class="text-decoration-none">
                            <div class="product-name">
                                <?php echo $produk['nama_produk']; ?>
                            </div>
                        </a>
                        
                        <!-- Harga & Satuan -->
                        <div class="product-price-row">
                            <span class="product-price-main">
                                Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?>
                            </span>
                            <span class="product-price-unit">
                                / <?php echo $produk['satuan']; ?>
                            </span>
                        </div>
                        
                        <!-- Stok -->
                        <div class="product-stock-info mt-1">
                            <i class="fas fa-cubes me-1"></i> 
                            Stok: <?php echo $produk['stok']; ?> <?php echo $produk['satuan']; ?>
                        </div>
                        
                        <!-- Actions -->
                        <div class="d-flex gap-2 mt-2">
                            <a href="detail_produk.php?id=<?php echo $produk['id']; ?>" 
                               class="btn btn-sm flex-grow-1" 
                               style="background: var(--color-primary-50); color: var(--color-primary); border-radius: 8px; font-weight: 600;">
                                <i class="fas fa-eye me-1"></i> Detail
                            </a>
                            <?php if ($produk['stok'] > 0): ?>
                            <form method="POST" action="keranjang.php" style="flex-shrink: 0;">
                                <input type="hidden" name="id_produk" value="<?php echo $produk['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="jumlah" value="1">
                                <button type="submit" class="btn btn-sm" 
                                        style="background: var(--gradient-primary); color: white; border-radius: 8px; width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                        title="Tambah ke Keranjang">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card" style="border-radius: var(--radius-lg);">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-box-open" style="font-size: 3rem; color: var(--color-text-muted);"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada produk tersedia</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================
     PRODUK TERLARIS
     ============================================ -->
<?php if (count($produk_terlaris) > 0): ?>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-800 mb-1">
                <i class="fas fa-fire me-2" style="color: #EF4444;"></i>
                Produk Terlaris
            </h3>
            <div style="width: 50px; height: 4px; background: linear-gradient(135deg, #EF4444, #F59E0B); border-radius: 2px;"></div>
        </div>
        <a href="produk.php" class="btn btn-outline btn-sm" style="border-radius: 10px;">
            Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
    
    <div class="row g-4">
        <?php foreach ($produk_terlaris as $produk): ?>
        <div class="col-6 col-lg-3">
            <div class="product-card" style="border-color: rgba(239,68,68,0.3);">
                <div class="product-card-img-wrapper">
                    <?php if ($produk['gambar']): ?>
                    <img src="../assets/img/produk/<?php echo $produk['gambar']; ?>" 
                         alt="<?php echo $produk['nama_produk']; ?>" 
                         loading="lazy">
                    <?php else: ?>
                    <div class="product-card-img-placeholder">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Best Seller Badge -->
                    <span style="position: absolute; top: 10px; right: 10px; background: linear-gradient(135deg, #EF4444, #F59E0B); color: white; font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 100px; z-index: 2;">
                        <i class="fas fa-fire me-1"></i> Best Seller
                    </span>
                </div>
                <div class="product-card-body">
                    <div class="product-name"><?php echo $produk['nama_produk']; ?></div>
                    <div class="product-price-row">
                        <span class="product-price-main">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></span>
                        <span class="product-price-unit">/ <?php echo $produk['satuan']; ?></span>
                    </div>
                    <a href="detail_produk.php?id=<?php echo $produk['id']; ?>" 
                       class="btn btn-add-cart mt-2">
                        <i class="fas fa-eye me-1"></i> Lihat Detail
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ============================================
     PESANAN TERBARU USER
     ============================================ -->
<?php if (count($pesanan_terbaru) > 0): ?>
<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-800 mb-1">
                <i class="fas fa-history me-2" style="color: var(--color-primary);"></i>
                Pesanan Terbaru
            </h3>
            <div style="width: 50px; height: 4px; background: var(--gradient-primary); border-radius: 2px;"></div>
        </div>
        <a href="riwayat.php" class="btn btn-outline btn-sm" style="border-radius: 10px;">
            Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
    
    <div class="row g-3">
        <?php foreach ($pesanan_terbaru as $pesanan): 
            $badge_class = ''; $icon = '';
            switch ($pesanan['status']) {
                case 'menunggu_pembayaran': $badge_class = 'badge-pending'; $icon = 'fa-clock'; break;
                case 'menunggu_konfirmasi': $badge_class = 'badge-confirming'; $icon = 'fa-search'; break;
                case 'dikonfirmasi': $badge_class = 'badge-confirmed'; $icon = 'fa-check-circle'; break;
                case 'selesai': $badge_class = 'badge-done'; $icon = 'fa-trophy'; break;
                case 'dibatalkan': $badge_class = 'badge-cancelled'; $icon = 'fa-times-circle'; break;
            }
        ?>
        <div class="col-md-4">
            <div class="card card-hover" style="border-radius: var(--radius-lg);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span style="font-family: 'Courier New', monospace; background: #F5F3FF; padding: 3px 10px; border-radius: 6px; font-size: 12px;">
                            <?php echo $pesanan['kode_pesanan']; ?>
                        </span>
                        <span class="badge badge-status <?php echo $badge_class; ?>" style="font-size: 11px;">
                            <span class="status-dot"></span>
                            <i class="fas <?php echo $icon; ?> ms-1"></i>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></div>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i> 
                                <?php echo date('d/m/Y', strtotime($pesanan['created_at'])); ?>
                            </small>
                        </div>
                        <a href="upload_bukti.php?id=<?php echo $pesanan['id']; ?>" 
                           class="btn btn-sm" 
                           style="background: var(--color-primary-50); color: var(--color-primary); border-radius: 8px;">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ============================================
     CALL TO ACTION BANNER
     ============================================ -->
<div class="container mt-5 mb-5">
    <div style="background: var(--gradient-primary); border-radius: var(--radius-xl); padding: 40px; position: relative; overflow: hidden;">
        <!-- Decorative elements -->
        <div style="position: absolute; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.1); top: -50px; right: -50px;"></div>
        <div style="position: absolute; width: 150px; height: 150px; border-radius: 50%; background: rgba(255,255,255,0.08); bottom: -30px; left: -30px;"></div>
        
        <div class="row align-items-center position-relative" style="z-index: 1;">
            <div class="col-lg-8">
                <h3 class="text-white fw-800 mb-2">Butuh Bantuan Memilih Produk?</h3>
                <p class="text-white mb-0" style="opacity: 0.9;">
                    Hubungi tim kami untuk mendapatkan rekomendasi produk terbaik sesuai kebutuhan laundry Anda.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 700; color: var(--color-primary);">
                    <i class="fab fa-whatsapp me-2"></i> Chat WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>