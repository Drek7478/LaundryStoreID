<?php
/**
 * LaundryStoreID - Landing Page
 * 
 * Halaman depan website sebelum login
 * Menampilkan informasi tentang toko, produk unggulan, dan CTA
 */

require_once 'config/db.php';

// Jika sudah login, redirect ke dashboard sesuai role
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('user/dashboard.php');
    }
}

// Ambil data untuk landing page
$stmt = $pdo->query("SELECT COUNT(*) as total FROM produk WHERE stok > 0");
$total_produk = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_customers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM pesanan WHERE status = 'selesai'");
$total_orders = $stmt->fetch()['total'];

// Produk unggulan (4 produk terlaris)
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
$featured_products = $stmt->fetchAll();

// Testimoni (dummy data)
$testimonials = [
    [
        'nama' => 'Ibu Sarah',
        'role' => 'Pemilik Laundry',
        'pesan' => 'Produk dari LaundryStoreID sangat berkualitas! Deterjennya wangi dan awet. Pengiriman juga cepat.',
        'rating' => 5,
        'avatar' => 'S'
    ],
    [
        'nama' => 'Pak Ahmad',
        'role' => 'Pengusaha Laundry',
        'pesan' => 'Harga bersaing dan pelayanan sangat baik. Sudah 2 tahun jadi pelanggan setia.',
        'rating' => 5,
        'avatar' => 'A'
    ],
    [
        'nama' => 'Mbak Rina',
        'role' => 'Ibu Rumah Tangga',
        'pesan' => 'Belanja perlengkapan laundry jadi lebih mudah. Banyak pilihan produk dan harganya terjangkau.',
        'rating' => 4,
        'avatar' => 'R'
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LaundryStoreID - Toko Perlengkapan Laundry Terlengkap. Deterjen, pewangi, pemutih, alat laundry berkualitas harga terbaik.">
    <meta name="keywords" content="laundry, deterjen, pewangi, pemutih, alat laundry, toko laundry online">
    <meta name="author" content="LaundryStoreID">
    <meta name="theme-color" content="#7C3AED">
    
    <title>LaundryStoreID - Toko Perlengkapan Laundry Terlengkap</title>
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🧺</text></svg>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        /* ============================================
           LANDING PAGE SPECIFIC STYLES
           ============================================ */
        
        :root {
            --color-primary: #7C3AED;
            --color-primary-dark: #6D28D9;
            --color-accent: #06B6D4;
            --color-dark: #0F172A;
            --gradient-primary: linear-gradient(135deg, #7C3AED 0%, #6D28D9 50%, #06B6D4 100%);
            --gradient-hero: linear-gradient(135deg, #0F172A 0%, #4C1D95 60%, #0891B2 100%);
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
        }
        
        /* ============================================
           NAVBAR
           ============================================ */
        .landing-navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .landing-navbar.scrolled {
            box-shadow: 0 4px 30px rgba(0,0,0,0.1);
        }
        
        .navbar-brand-landing {
            font-weight: 800;
            font-size: 1.5rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .navbar-brand-landing .brand-laundry {
            color: #7C3AED;
        }
        
        .navbar-brand-landing .brand-store {
            color: #06B6D4;
        }
        
        .btn-nav-login {
            background: transparent;
            border: 2px solid #7C3AED;
            color: #7C3AED;
            padding: 8px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .btn-nav-login:hover {
            background: #7C3AED;
            color: white;
        }
        
        .btn-nav-register {
            background: var(--gradient-primary);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
        }
        
        .btn-nav-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124,58,237,0.4);
            color: white;
        }
        
        /* ============================================
           HERO SECTION
           ============================================ */
        .hero-section-landing {
            background: var(--gradient-hero);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 100px 0 60px;
        }
        
        .hero-glow-1 {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: rgba(124,58,237,0.2);
            filter: blur(100px);
            top: -200px;
            right: -200px;
            animation: floatGlow 8s ease-in-out infinite;
        }
        
        .hero-glow-2 {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(6,182,212,0.2);
            filter: blur(80px);
            bottom: -100px;
            left: -100px;
            animation: floatGlow 10s ease-in-out infinite reverse;
        }
        
        @keyframes floatGlow {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, -30px); }
        }
        
        .hero-title-landing {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 800;
            color: white;
            line-height: 1.2;
            margin-bottom: 16px;
        }
        
        .hero-title-landing .highlight {
            background: linear-gradient(135deg, #A78BFA, #22D3EE);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle-landing {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.8);
            margin-bottom: 32px;
            max-width: 500px;
            line-height: 1.7;
        }
        
        .hero-image {
            text-align: center;
            animation: floatEmoji 4s ease-in-out infinite;
        }
        
        @keyframes floatEmoji {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .hero-stats {
            display: flex;
            gap: 30px;
            margin-top: 40px;
        }
        
        .hero-stat-item {
            text-align: center;
        }
        
        .hero-stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: white;
        }
        
        .hero-stat-label {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.7);
        }
        
        /* ============================================
           FEATURES SECTION
           ============================================ */
        .features-section {
            padding: 80px 0;
            background: white;
        }
        
        .feature-card {
            text-align: center;
            padding: 40px 24px;
            border-radius: 20px;
            transition: all 0.3s ease;
            border: 1px solid #E2E8F0;
            background: white;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            border-color: #7C3AED;
        }
        
        .feature-icon {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }
        
        .feature-icon.violet {
            background: #F5F3FF;
            color: #7C3AED;
        }
        
        .feature-icon.cyan {
            background: #CFFAFE;
            color: #0891B2;
        }
        
        .feature-icon.emerald {
            background: #D1FAE5;
            color: #059669;
        }
        
        .feature-icon.amber {
            background: #FEF3C7;
            color: #D97706;
        }
        
        /* ============================================
           PRODUCTS SECTION
           ============================================ */
        .products-section {
            padding: 80px 0;
            background: #F8FAFC;
        }
        
        .product-card-landing {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #E2E8F0;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .product-card-landing:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 40px rgba(124,58,237,0.15);
        }
        
        .product-img-wrapper {
            aspect-ratio: 1;
            background: #F8FAFC;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            position: relative;
            overflow: hidden;
        }
        
        .product-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* ============================================
           TESTIMONIALS SECTION
           ============================================ */
        .testimonials-section {
            padding: 80px 0;
            background: white;
        }
        
        .testimonial-card {
            background: #F8FAFC;
            border-radius: 16px;
            padding: 30px;
            border: 1px solid #E2E8F0;
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .testimonial-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--gradient-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 16px;
        }
        
        .stars {
            color: #F59E0B;
        }
        
        /* ============================================
           CTA SECTION
           ============================================ */
        .cta-section {
            padding: 80px 0;
            background: var(--gradient-hero);
            position: relative;
            overflow: hidden;
        }
        
        .cta-glow {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            filter: blur(80px);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        /* ============================================
           FOOTER
           ============================================ */
        .landing-footer {
            background: #0F172A;
            color: white;
            padding: 40px 0 20px;
        }
        
        .footer-link {
            color: #94A3B8;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .footer-link:hover {
            color: #A78BFA;
        }
        
        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 767.98px) {
            .hero-section-landing {
                min-height: auto;
                padding: 120px 0 40px;
                text-align: center;
            }
            
            .hero-subtitle-landing {
                margin: 0 auto 24px;
            }
            
            .hero-stats {
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .hero-image {
                margin-top: 30px;
                font-size: 6rem !important;
            }
        }
    </style>
</head>
<body>

<!-- ============================================
     NAVBAR
     ============================================ -->
<nav class="landing-navbar" id="landingNavbar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center py-3">
            <!-- Logo -->
            <a href="index.php" class="navbar-brand-landing">
                <span style="font-size: 1.8rem;">🧺</span>
                <span class="brand-laundry">Laundry</span><span class="brand-store">StoreID</span>
            </a>
            
            <!-- Buttons -->
            <div class="d-flex gap-2">
                <a href="auth/login.php" class="btn-nav-login">
                    <i class="fas fa-sign-in-alt me-1"></i> Masuk
                </a>
                <a href="auth/register.php" class="btn-nav-register d-none d-sm-inline-flex">
                    <i class="fas fa-user-plus me-1"></i> Daftar
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- ============================================
     HERO SECTION
     ============================================ -->
<section class="hero-section-landing">
    <div class="hero-glow-1"></div>
    <div class="hero-glow-2"></div>
    
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <!-- Hero Text -->
            <div class="col-lg-7">
                <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 8px 16px; border-radius: 100px; font-size: 13px; font-weight: 500; margin-bottom: 20px;">
                    <i class="fas fa-star" style="color: #F59E0B;"></i>
                    <span>#1 Toko Perlengkapan Laundry Terpercaya</span>
                </div>
                
                <h1 class="hero-title-landing">
                    Solusi Lengkap<br>
                    <span class="highlight">Kebutuhan Laundry</span> Anda
                </h1>
                
                <p class="hero-subtitle-landing">
                    Dapatkan deterjen, pewangi, pemutih, dan alat laundry berkualitas premium 
                    dengan harga terbaik. Pengiriman cepat ke seluruh Indonesia.
                </p>
                
                <div class="d-flex gap-3 flex-wrap">
                    <a href="auth/register.php" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 700; padding: 14px 32px; color: #7C3AED;">
                        <i class="fas fa-rocket me-2"></i> Mulai Belanja Sekarang
                    </a>
                    <a href="#features" class="btn btn-outline-light btn-lg" style="border-radius: 12px; font-weight: 700; padding: 14px 32px;">
                        <i class="fas fa-info-circle me-2"></i> Pelajari Lebih Lanjut
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="hero-stats">
                    <div class="hero-stat-item">
                        <div class="hero-stat-value"><?php echo $total_produk; ?>+</div>
                        <div class="hero-stat-label">Produk Tersedia</div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="hero-stat-value"><?php echo $total_customers; ?>+</div>
                        <div class="hero-stat-label">Pelanggan</div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="hero-stat-value"><?php echo $total_orders; ?>+</div>
                        <div class="hero-stat-label">Pesanan Selesai</div>
                    </div>
                </div>
            </div>
            
            <!-- Hero Image -->
            <div class="col-lg-5">
                <div class="hero-image" style="font-size: 12rem;">
                    🧺
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     FEATURES SECTION
     ============================================ -->
<section class="features-section" id="features">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-800 mb-2" style="font-size: 2rem;">Mengapa Memilih Kami?</h2>
            <p class="text-muted">Keunggulan LaundryStoreID dibandingkan yang lain</p>
            <div style="width: 60px; height: 4px; background: var(--gradient-primary); border-radius: 2px; margin: 16px auto 0;"></div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3">
                <div class="feature-card">
                    <div class="feature-icon violet">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Produk Original</h5>
                    <p class="text-muted small mb-0">Semua produk 100% original dari distributor resmi dengan kualitas terjamin.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-card">
                    <div class="feature-icon cyan">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Harga Terbaik</h5>
                    <p class="text-muted small mb-0">Harga kompetitif dengan berbagai pilihan produk untuk semua kebutuhan.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-card">
                    <div class="feature-icon emerald">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Pengiriman Cepat</h5>
                    <p class="text-muted small mb-0">Gratis ongkir untuk pembelian minimal Rp 150.000 ke seluruh Indonesia.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-card">
                    <div class="feature-icon amber">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Support 24/7</h5>
                    <p class="text-muted small mb-0">Tim support siap membantu Anda kapan saja melalui chat WhatsApp.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     PRODUCTS SECTION
     ============================================ -->
<section class="products-section" id="products">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-800 mb-2" style="font-size: 2rem;">Produk Unggulan</h2>
            <p class="text-muted">Produk terlaris yang paling banyak dicari pelanggan</p>
            <div style="width: 60px; height: 4px; background: var(--gradient-primary); border-radius: 2px; margin: 16px auto 0;"></div>
        </div>
        
        <div class="row g-4">
            <?php if (count($featured_products) > 0): ?>
                <?php foreach ($featured_products as $produk): ?>
                <div class="col-6 col-md-3">
                    <div class="product-card-landing">
                        <div class="product-img-wrapper">
                            <?php if ($produk['gambar']): ?>
                            <img src="assets/img/produk/<?php echo $produk['gambar']; ?>" alt="<?php echo $produk['nama_produk']; ?>" loading="lazy">
                            <?php else: ?>
                            <span style="font-size: 4rem;">📦</span>
                            <?php endif; ?>
                            
                            <?php if ($produk['total_terjual'] > 10): ?>
                            <span style="position: absolute; top: 12px; left: 12px; background: linear-gradient(135deg, #EF4444, #F59E0B); color: white; font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 100px;">
                                <i class="fas fa-fire me-1"></i> Best Seller
                            </span>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 16px;">
                            <?php if ($produk['nama_kategori']): ?>
                            <small style="color: #06B6D4; font-weight: 600; font-size: 11px; text-transform: uppercase;">
                                <?php echo $produk['nama_kategori']; ?>
                            </small>
                            <?php endif; ?>
                            <h6 class="fw-bold mt-1 mb-2"><?php echo $produk['nama_produk']; ?></h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <span style="font-weight: 800; color: #7C3AED; font-size: 1.1rem;">
                                    Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?>
                                </span>
                                <small class="text-muted">/ <?php echo $produk['satuan']; ?></small>
                            </div>
                            <?php if ($produk['total_terjual'] > 0): ?>
                            <small class="text-muted">
                                <i class="fas fa-shopping-cart me-1"></i> <?php echo $produk['total_terjual']; ?> terjual
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-box-open" style="font-size: 4rem; color: #D1D5DB;"></i>
                    <p class="text-muted mt-3">Produk akan segera tersedia. Stay tuned!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="auth/register.php" class="btn btn-primary btn-lg" style="border-radius: 12px; font-weight: 700; padding: 14px 32px;">
                <i class="fas fa-eye me-2"></i> Lihat Semua Produk
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     TESTIMONIALS SECTION
     ============================================ -->
<section class="testimonials-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-800 mb-2" style="font-size: 2rem;">Apa Kata Pelanggan?</h2>
            <p class="text-muted">Testimoni dari pelanggan setia LaundryStoreID</p>
            <div style="width: 60px; height: 4px; background: var(--gradient-primary); border-radius: 2px; margin: 16px auto 0;"></div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($testimonials as $t): ?>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="testimonial-avatar">
                            <?php echo $t['avatar']; ?>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?php echo $t['nama']; ?></h6>
                            <small class="text-muted"><?php echo $t['role']; ?></small>
                        </div>
                    </div>
                    <div class="stars mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $t['rating']): ?>
                            <i class="fas fa-star"></i>
                            <?php else: ?>
                            <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 14px; line-height: 1.7;">
                        "<?php echo $t['pesan']; ?>"
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================
     CTA SECTION
     ============================================ -->
<section class="cta-section">
    <div class="cta-glow"></div>
    <div class="container text-center position-relative" style="z-index: 2;">
        <h2 class="fw-800 text-white mb-3" style="font-size: 2rem;">
            Siap Memulai Belanja? 🚀
        </h2>
        <p class="text-white mb-4" style="opacity: 0.9; max-width: 600px; margin: 0 auto;">
            Daftar sekarang dan dapatkan akses ke ratusan produk laundry berkualitas dengan harga terbaik. 
            Gratis ongkir untuk pembelian pertama!
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="auth/register.php" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 700; padding: 14px 36px; color: #7C3AED;">
                <i class="fas fa-user-plus me-2"></i> Daftar Gratis
            </a>
            <a href="auth/login.php" class="btn btn-outline-light btn-lg" style="border-radius: 12px; font-weight: 700; padding: 14px 36px;">
                <i class="fas fa-sign-in-alt me-2"></i> Sudah Punya Akun?
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     FOOTER
     ============================================ -->
<footer class="landing-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">
                    <span style="color: #A78BFA;">🧺 Laundry</span><span style="color: #22D3EE;">StoreID</span>
                </h5>
                <p class="text-muted small">Toko perlengkapan laundry terlengkap dan terpercaya di Indonesia.</p>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold text-white mb-3">Tautan</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="auth/login.php" class="footer-link"><i class="fas fa-sign-in-alt me-2"></i> Masuk</a></li>
                    <li class="mb-2"><a href="auth/register.php" class="footer-link"><i class="fas fa-user-plus me-2"></i> Daftar</a></li>
                    <li class="mb-2"><a href="#features" class="footer-link"><i class="fas fa-star me-2"></i> Keunggulan</a></li>
                    <li class="mb-2"><a href="#products" class="footer-link"><i class="fas fa-box me-2"></i> Produk</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold text-white mb-3">Kontak</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2 text-muted"><i class="fas fa-envelope me-2"></i> support@laundrystoreid.com</li>
                    <li class="mb-2 text-muted"><i class="fas fa-phone me-2"></i> 0812-3456-7890</li>
                    <li class="mb-2 text-muted"><i class="fas fa-clock me-2"></i> Senin-Sabtu, 08.00-21.00</li>
                </ul>
            </div>
        </div>
        <hr style="border-color: rgba(255,255,255,0.1);">
        <div class="text-center">
            <small class="text-muted">&copy; <?php echo date('Y'); ?> LaundryStoreID. All rights reserved.</small>
        </div>
    </div>
</footer>

<!-- ============================================
     SCRIPTS
     ============================================ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Navbar scroll effect
    const navbar = document.getElementById('landingNavbar');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Counter animation
    function animateCounter(element, target, duration = 2000) {
        let start = 0;
        const increment = target / (duration / 16);
        
        function updateCounter() {
            start += increment;
            if (start < target) {
                element.textContent = Math.floor(start) + '+';
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target + '+';
            }
        }
        
        updateCounter();
    }
    
    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe feature cards and product cards
    document.querySelectorAll('.feature-card, .product-card-landing, .testimonial-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease';
        observer.observe(card);
    });
</script>

</body>
</html>