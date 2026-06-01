<?php

/**
 * LaundryStoreID - Landing Page (Neubrutalism Redesign)
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
    <meta name="description" content="LaundryStoreID - Toko Perlengkapan Laundry Terlengkap dengan gaya Neubrutalism.">
    <meta name="theme-color" content="#FFD600">

    <title>LaundryStoreID - Toko Perlengkapan Laundry Terlengkap</title>

    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🧺</text></svg>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ============================================
           NEUBRUTALISM DESIGN SYSTEM (UI-UX.md)
           ============================================ */
        :root {
            --color-bg: #FFFFFF;
            --color-bg-soft: #F2F2F0;
            --color-black: #0D0D0D;
            --color-primary: #1A6EFF;
            --color-accent: #FFD600;
            --color-danger: #FF3B3B;
            --color-success: #00C566;
            --color-border: #0D0D0D;
            --shadow-brutal: 4px 4px 0px #0D0D0D;
            --shadow-brutal-lg: 6px 6px 0px #0D0D0D;
            --border-width: 2.5px;
            --radius: 6px;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--color-bg);
            color: var(--color-black);
            overflow-x: hidden;
            line-height: 1.6;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .font-heading {
            font-family: 'Space Grotesk', sans-serif;
            color: var(--color-black);
            letter-spacing: -0.02em;
        }

        /* BRUTALIST BUTTONS */
        .btn-brutal {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 700;
            border: var(--border-width) solid var(--color-black);
            border-radius: var(--radius);
            box-shadow: var(--shadow-brutal);
            cursor: pointer;
            transition: transform 0.1s ease, box-shadow 0.1s ease;
            text-decoration: none;
            color: var(--color-black);
            background: var(--color-bg);
        }

        .btn-brutal:hover {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px var(--color-black);
            color: var(--color-black);
        }

        .btn-brutal:active {
            transform: translate(4px, 4px);
            box-shadow: none;
        }

        .btn-primary-brutal {
            background: var(--color-primary);
            color: #fff;
        }

        .btn-primary-brutal:hover {
            color: #fff;
        }

        .btn-accent-brutal {
            background: var(--color-accent);
            color: var(--color-black);
        }

        /* BRUTALIST CARDS */
        .card-brutal {
            background: var(--color-bg);
            border: var(--border-width) solid var(--color-black);
            border-radius: var(--radius);
            box-shadow: var(--shadow-brutal);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            height: 100%;
        }

        .card-brutal:hover {
            transform: translate(-2px, -2px);
            box-shadow: var(--shadow-brutal-lg);
        }

        .card-accent-blue {
            border-top: 6px solid var(--color-primary);
        }

        .card-accent-yellow {
            border-top: 6px solid var(--color-accent);
        }

        .card-accent-green {
            border-top: 6px solid var(--color-success);
        }

        /* NAVBAR */
        .navbar-brutal {
            background: var(--color-bg);
            border-bottom: var(--border-width) solid var(--color-black);
            padding: 16px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .brand-logo {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: var(--color-black);
            text-decoration: none;
        }

        /* HERO SECTION */
        .hero-section {
            padding: 140px 0 80px;
            background: var(--color-bg-soft);
            border-bottom: var(--border-width) solid var(--color-black);
        }

        .hero-title {
            font-size: clamp(3rem, 5vw, 4.5rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 24px;
        }

        .hero-highlight {
            background: var(--color-accent);
            padding: 0 8px;
            border: 2px solid var(--color-black);
            display: inline-block;
            transform: rotate(-2deg);
        }

        .trust-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--color-bg);
            border: 2px solid var(--color-black);
            padding: 8px 16px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 3px 3px 0px var(--color-black);
            margin-bottom: 32px;
            border-radius: 4px;
        }

        .stat-box {
            border: 2px solid var(--color-black);
            padding: 16px;
            background: var(--color-bg);
            box-shadow: 3px 3px 0px var(--color-black);
            text-align: center;
        }

        .stat-box h3 {
            font-size: 32px;
            margin: 0;
            font-weight: 800;
        }

        .stat-box p {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }

        /* GENERAL SECTIONS */
        .section-pad {
            padding: 96px 0;
            border-bottom: var(--border-width) solid var(--color-black);
        }

        .section-title {
            font-size: 40px;
            font-weight: 800;
            margin-bottom: 16px;
            text-transform: uppercase;
        }

        /* FEATURES */
        .feature-icon-box {
            width: 64px;
            height: 64px;
            border: 2px solid var(--color-black);
            background: var(--color-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: 3px 3px 0px var(--color-black);
            margin-bottom: 24px;
            border-radius: var(--radius);
        }

        /* PRODUCTS */
        .product-img-box {
            background: var(--color-bg-soft);
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            border-bottom: var(--border-width) solid var(--color-black);
            position: relative;
        }

        .badge-bestseller {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--color-accent);
            border: 2px solid var(--color-black);
            padding: 4px 10px;
            font-weight: 700;
            font-size: 12px;
            box-shadow: 2px 2px 0px var(--color-black);
        }

        .product-price {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 24px;
            font-weight: 800;
        }

        /* TESTIMONIALS */
        .testi-avatar {
            width: 56px;
            height: 56px;
            border: 2px solid var(--color-black);
            background: var(--color-primary);
            color: white;
            font-family: 'Space Grotesk';
            font-weight: 800;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 3px 3px 0px var(--color-black);
        }

        /* CTA SECTION */
        .cta-section {
            background: var(--color-primary);
            color: white;
            text-align: center;
        }

        .cta-section h2 {
            color: white;
            font-size: 48px;
        }

        /* FOOTER */
        .footer-brutal {
            background: var(--color-black);
            color: white;
            padding: 64px 0 32px;
        }

        .footer-brutal a {
            color: var(--color-accent);
            text-decoration: none;
            font-weight: 600;
        }

        .footer-brutal a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <nav class="navbar-brutal">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="brand-logo">
                LaundryStore
            </a>
            <div class="d-flex gap-3">
                <a href="auth/login.php" class="btn-brutal">Masuk</a>
                <a href="auth/register.php" class="btn-brutal btn-accent-brutal d-none d-sm-inline-flex">Daftar</a>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="trust-badge">
                        <i class="fas fa-star" style="color: #FF3B3B;"></i>
                        #1 Toko Laundry Terpercaya
                    </div>

                    <h1 class="hero-title">
                        Solusi Lengkap <br>
                        <span class="hero-highlight">Kebutuhan Laundry</span>
                    </h1>

                    <p class="fs-5 mb-4 fw-medium" style="max-width: 500px;">
                        Dapatkan deterjen, pewangi, pemutih, dan alat laundry berkualitas dengan gaya tanpa basa-basi. Pengiriman kilat ke seluruh Indonesia.
                    </p>

                    <div class="d-flex gap-3 flex-wrap mb-5">
                        <a href="auth/register.php" class="btn-brutal btn-primary-brutal btn-lg">
                            Mulai Belanja &rarr;
                        </a>
                        <a href="#produk" class="btn-brutal btn-lg">
                            Lihat Produk
                        </a>
                    </div>

                    <div class="row g-3">
                        <div class="col-auto">
                            <div class="stat-box">
                                <h3><?php echo $total_produk; ?>+</h3>
                                <p>Produk</p>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-box" style="background: var(--color-accent);">
                                <h3><?php echo $total_customers; ?>+</h3>
                                <p>Pelanggan</p>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-box">
                                <h3><?php echo $total_orders; ?>+</h3>
                                <p>Selesai</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 text-center d-none d-lg-block">
                    <div style="font-size: 15rem; transform: rotate(5deg); text-shadow: var(--shadow-brutal-lg);">

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-pad">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12">
                    <h2 class="section-title">Kenapa Pilih Kami?</h2>
                    <p class="fs-5 fw-medium">Layanan transparan, harga jujur, kualitas terjamin.</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card-brutal card-accent-yellow p-4">
                        <div class="feature-icon-box"><i class="fas fa-medal"></i></div>
                        <h4 class="fw-bold">100% Original</h4>
                        <p class="mb-0 fw-medium">Produk langsung dari distributor resmi. Kualitas terjamin tanpa kompromi.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-brutal card-accent-blue p-4">
                        <div class="feature-icon-box" style="background: var(--color-primary); color: white;"><i class="fas fa-tags"></i></div>
                        <h4 class="fw-bold">Harga Jujur</h4>
                        <p class="mb-0 fw-medium">Tidak ada biaya tersembunyi. Kompetitif dan masuk akal untuk bisnis Anda.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-brutal card-accent-green p-4">
                        <div class="feature-icon-box" style="background: var(--color-success); color: white;"><i class="fas fa-truck-fast"></i></div>
                        <h4 class="fw-bold">Kirim Kilat</h4>
                        <p class="mb-0 fw-medium">Gratis ongkir untuk pembelian minimal Rp 150.000 ke seluruh Indonesia.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-brutal p-4" style="border-top: 6px solid var(--color-danger);">
                        <div class="feature-icon-box" style="background: var(--color-danger); color: white;"><i class="fas fa-headset"></i></div>
                        <h4 class="fw-bold">Support 24/7</h4>
                        <p class="mb-0 fw-medium">Ada masalah? Tim kami siap membantu kapan saja tanpa basa-basi.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-pad" id="produk" style="background: var(--color-bg-soft);">
        <div class="container">
            <div class="row align-items-end mb-5">
                <div class="col-md-8">
                    <h2 class="section-title">Produk Unggulan</h2>
                    <p class="fs-5 fw-medium mb-0">Barang paling cepat laku. Jangan sampai kehabisan.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="auth/register.php" class="btn-brutal btn-accent-brutal">Lihat Semua Katalog &rarr;</a>
                </div>
            </div>

            <div class="row g-4">
                <?php if (count($featured_products) > 0): ?>
                    <?php foreach ($featured_products as $produk): ?>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card-brutal overflow-hidden">
                                <div class="product-img-box">
                                    <?php if ($produk['gambar'] && $produk['gambar'] !== 'default.jpg'): ?>
                                        <img src="assets/img/produk/<?php echo $produk['gambar']; ?>" alt="<?php echo $produk['nama_produk']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        📦
                                    <?php endif; ?>

                                    <?php if ($produk['total_terjual'] > 10): ?>
                                        <span class="badge-bestseller">LAKU KERAS</span>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <?php if ($produk['nama_kategori']): ?>
                                        <div class="fw-bold mb-2" style="font-size: 12px; letter-spacing: 1px; text-transform: uppercase;">
                                            [ <?php echo $produk['nama_kategori']; ?> ]
                                        </div>
                                    <?php endif; ?>

                                    <h5 class="fw-bold mb-3" style="font-family: 'DM Sans', sans-serif;"><?php echo $produk['nama_produk']; ?></h5>

                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <div class="product-price">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
                                            <div class="fw-bold text-muted" style="font-size: 14px;">/ <?php echo $produk['satuan']; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <h3 class="fw-bold">Belum ada produk saat ini.</h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="section-pad">
        <div class="container">
            <h2 class="section-title text-center mb-5">Kata Mereka</h2>

            <div class="row g-4 justify-content-center">
                <?php foreach ($testimonials as $t): ?>
                    <div class="col-md-4">
                        <div class="card-brutal p-4">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="testi-avatar">
                                    <?php echo $t['avatar']; ?>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0" style="font-family: 'DM Sans';"><?php echo $t['nama']; ?></h5>
                                    <span class="fw-bold" style="font-size: 13px;"><?php echo strtoupper($t['role']); ?></span>
                                </div>
                            </div>
                            <p class="fw-medium mb-3" style="font-size: 15px;">
                                "<?php echo $t['pesan']; ?>"
                            </p>
                            <div style="color: var(--color-black); font-size: 18px;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $t['rating']): ?>
                                        <i class="fas fa-star" style="color: var(--color-accent); text-shadow: 1px 1px 0px var(--color-black);"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section-pad cta-section">
        <div class="container">
            <h2 class="mb-4">Siap Memulai Belanja?</h2>
            <p class="fs-5 fw-medium mb-5 mx-auto" style="max-width: 600px;">
                Daftar sekarang dan dapatkan akses ke ratusan produk laundry berkualitas. Tanpa ribet, langsung order.
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="auth/register.php" class="btn-brutal btn-accent-brutal btn-xl" style="font-size: 18px; padding: 16px 32px;">
                    Daftar Gratis Sekarang
                </a>
            </div>
        </div>
    </section>

    <footer class="footer-brutal">
        <div class="container">
            <div class="row g-5 mb-5">
                <div class="col-md-5">
                    <h3 class="fw-bold text-white mb-3" style="font-size: 32px;">
                        LaundryStore
                    </h3>
                    <p class="fw-medium" style="opacity: 0.8; max-width: 300px;">
                        Toko perlengkapan laundry terlengkap dengan gaya fungsional. Transparan dan dapat dipercaya.
                    </p>
                </div>
                <div class="col-md-3">
                    <h5 class="fw-bold text-white mb-4">Navigasi</h5>
                    <ul class="list-unstyled fw-medium d-flex flex-column gap-2">
                        <li><a href="auth/login.php">Masuk Sistem</a></li>
                        <li><a href="auth/register.php">Buat Akun Baru</a></li>
                        <li><a href="#produk">Katalog Produk</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="fw-bold text-white mb-4">Kontak (24/7)</h5>
                    <ul class="list-unstyled fw-medium d-flex flex-column gap-3" style="opacity: 0.9;">
                        <li><i class="fas fa-envelope me-2" style="color: var(--color-accent);"></i> hello@laundrystore.id</li>
                        <li><i class="fas fa-phone me-2" style="color: var(--color-accent);"></i> 0812-3456-7890</li>
                        <li><i class="fas fa-map-marker-alt me-2" style="color: var(--color-accent);"></i> Jl. Brutal No. 99, Jakarta</li>
                    </ul>
                </div>
            </div>
            <div class="pt-4 mt-4" style="border-top: 2px solid rgba(255,255,255,0.2);">
                <p class="mb-0 fw-medium text-center" style="opacity: 0.6; font-size: 14px;">
                    &copy; <?php echo date('Y'); ?> LaundryStoreID. Desain Neubrutalism. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>