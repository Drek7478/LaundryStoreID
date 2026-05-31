<?php
/**
 * LaundryStoreID - Header Template
 * 
 * Digunakan oleh semua halaman user dan admin
 * Tema: Violet + Cyan + Slate
 */

// Deteksi base path untuk asset (CSS, JS, images)
// Ini memastikan path selalu benar baik di halaman user maupun admin
$base_path = '/laundry-store';

// Deteksi apakah halaman admin atau user
$is_admin_page = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);

// Tentukan title berdasarkan halaman
$page_title = 'LaundryStoreID - Toko Perlengkapan Laundry Modern';
if ($is_admin_page) {
    $page_title = 'Admin Panel - LaundryStoreID';
}

// Deteksi halaman spesifik untuk title yang lebih akurat
$current_page = basename($_SERVER['PHP_SELF']);
switch ($current_page) {
    case 'login.php':
        $page_title = 'Login - LaundryStoreID';
        break;
    case 'register.php':
        $page_title = 'Daftar - LaundryStoreID';
        break;
    case 'dashboard.php':
        $page_title = $is_admin_page ? 'Dashboard Admin - LaundryStoreID' : 'Dashboard - LaundryStoreID';
        break;
    case 'produk.php':
        $page_title = 'Katalog Produk - LaundryStoreID';
        break;
    case 'detail_produk.php':
        $page_title = 'Detail Produk - LaundryStoreID';
        break;
    case 'keranjang.php':
        $page_title = 'Keranjang Belanja - LaundryStoreID';
        break;
    case 'checkout.php':
        $page_title = 'Checkout - LaundryStoreID';
        break;
    case 'riwayat.php':
        $page_title = 'Riwayat Transaksi - LaundryStoreID';
        break;
    case 'upload_bukti.php':
        $page_title = 'Upload Bukti Pembayaran - LaundryStoreID';
        break;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="LaundryStoreID - Toko Perlengkapan Laundry Terlengkap. Menyediakan deterjen, pewangi, pemutih, dan alat laundry berkualitas dengan harga terbaik.">
    <meta name="keywords" content="laundry, deterjen, pewangi, pemutih, alat laundry, toko laundry, perlengkapan laundry, LaundryStoreID">
    <meta name="author" content="LaundryStoreID">
    <meta name="robots" content="index, follow">
    <meta name="language" content="Indonesian">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="Toko Perlengkapan Laundry Terlengkap - LaundryStoreID">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="LaundryStoreID">
    
    <!-- Theme Color -->
    <meta name="theme-color" content="#7C3AED">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- Title -->
    <title><?php echo $page_title; ?></title>
    
    <!-- Favicon (Emoji SVG) -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🧺</text></svg>">
    <link rel="apple-touch-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🧺</text></svg>">
    
    <!-- Preconnect for Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    
    <!-- Google Fonts - Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS (via CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons (via CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Font Awesome 6 (via CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS - LaundryStoreID -->
    <link href="<?php echo $base_path; ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- Inline Critical CSS untuk mencegah flash of unstyled content (FOUC) -->
    <style>
        /* Critical CSS - Load sebelum halaman selesai */
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #F8FAFC;
            color: #1E293B;
            opacity: 0;
            animation: criticalFadeIn 0.3s ease forwards;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        @keyframes criticalFadeIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Skeleton loading untuk gambar */
        img:not([src]):not([srcset]) {
            visibility: hidden;
        }
        
        img[loading="lazy"] {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        img[loading="lazy"].loaded {
            opacity: 1;
        }
        
        /* Prevent layout shift */
        .product-card-img-wrapper {
            aspect-ratio: 1 / 1;
            background: #F8FAFC;
        }
        
        /* Navbar initial state */
        .navbar-main {
            background: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
        }
        
        /* Admin sidebar initial state */
        .admin-sidebar {
            background: #0F172A;
        }
        
        /* Hide elements that require JS */
        .js-only {
            display: none;
        }
        
        html.js-loaded .js-only {
            display: block;
        }
        
        html.js-loaded .no-js-only {
            display: none;
        }
    </style>
    
    <!-- Preload important assets -->
    <link rel="preload" href="<?php echo $base_path; ?>/assets/css/style.css" as="style">
    <link rel="preload" href="<?php echo $base_path; ?>/assets/js/main.js" as="script">
</head>
<body>

<!-- Add 'js-loaded' class to html when JavaScript is ready -->
<script>
    // Deteksi JavaScript enabled
    document.documentElement.classList.add('js-loaded');
    
    // Lazy load images
    document.addEventListener('DOMContentLoaded', function() {
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                        }
                        img.classList.add('loaded');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            lazyImages.forEach(function(img) {
                imageObserver.observe(img);
            });
        } else {
            // Fallback for browsers without IntersectionObserver
            lazyImages.forEach(function(img) {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                }
                img.classList.add('loaded');
            });
        }
    });
</script>

<!-- Loading Indicator (optional, untuk halaman yang membutuhkan loading state) -->
<div id="page-loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.9); z-index: 99999; display: flex; align-items: center; justify-content: center;">
    <div style="text-align: center;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted fw-medium">Memuat halaman...</p>
    </div>
</div>

<script>
    // Sembunyikan page loader saat halaman sudah selesai dimuat
    window.addEventListener('load', function() {
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.style.opacity = '0';
            loader.style.transition = 'opacity 0.3s ease';
            setTimeout(function() {
                loader.style.display = 'none';
            }, 300);
        }
    });
</script>

<!-- Main Content Wrapper - semua konten halaman akan dimasukkan di sini -->
<div id="app-wrapper">