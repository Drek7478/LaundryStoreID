<?php
/**
 * LaundryStoreID - Navbar User Template
 * 
 * Hanya untuk halaman user (non-admin)
 * Tema: Violet + Cyan + Slate
 */

// Proteksi: Hanya user yang sudah login dan bukan admin yang bisa mengakses
if (!isLoggedIn() || isAdmin()) {
    redirect('../auth/login.php');
}

// Hitung jumlah item di keranjang untuk badge
$cartCount = 0;
try {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah), 0) as total FROM keranjang WHERE id_user = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart = $stmt->fetch();
    $cartCount = (int)($cart['total'] ?? 0);
} catch (Exception $e) {
    $cartCount = 0;
}

// Tentukan halaman aktif untuk nav-link
$current_page = basename($_SERVER['PHP_SELF']);

// Ambil nama depan user untuk greeting
$nama_depan = explode(' ', $_SESSION['nama'])[0];

// Cek apakah ada pesanan pending
$pendingOrders = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pesanan WHERE id_user = ? AND status IN ('menunggu_pembayaran', 'menunggu_konfirmasi')");
    $stmt->execute([$_SESSION['user_id']]);
    $pendingOrders = $stmt->fetch()['total'] ?? 0;
} catch (Exception $e) {
    $pendingOrders = 0;
}
?>

<!-- ============================================
     MAIN NAVBAR
     ============================================ -->
<nav class="navbar navbar-expand-lg navbar-main py-2" id="mainNavbar">
    <div class="container">
        <!-- Logo Brand -->
        <a class="navbar-brand" href="../user/dashboard.php" title="LaundryStoreID - Home">
            <span class="brand-icon">
                <i class="fas fa-jug-detergent"></i>
            </span>
            <span class="brand-laundry">Laundry</span>
            <span class="brand-store">StoreID</span>
        </a>
        
        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarUser" 
                aria-controls="navbarUser" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarUser">
            
            <!-- Search Bar (Desktop) -->
            <div class="mx-auto d-none d-lg-block search-nav">
                <form action="../user/produk.php" method="GET" class="position-relative">
                    <i class="fas fa-search position-absolute" style="left: 16px; top: 50%; transform: translateY(-50%); color: #94A3B8; z-index: 3; pointer-events: none;"></i>
                    <input type="text" name="search" class="form-control ps-5 pe-4" 
                           placeholder="Cari deterjen, pewangi, alat laundry..." 
                           aria-label="Cari produk"
                           value="<?php echo isset($_GET['search']) ? sanitize($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-sm position-absolute" style="right: 8px; top: 50%; transform: translateY(-50%); color: var(--color-primary); display: none;">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
            
            <!-- Navigation Links -->
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                
                <!-- Katalog -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'produk.php' || $current_page == 'detail_produk.php' ? 'active' : ''; ?>" 
                       href="../user/produk.php">
                        <i class="fas fa-th-large me-1"></i> 
                        <span>Katalog</span>
                    </a>
                </li>
                
                <!-- Pesanan / Riwayat -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'riwayat.php' ? 'active' : ''; ?> position-relative" 
                       href="../user/riwayat.php">
                        <i class="fas fa-history me-1"></i> 
                        <span>Pesanan</span>
                        <?php if ($pendingOrders > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                            <?php echo $pendingOrders; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <!-- Keranjang -->
                <li class="nav-item">
                    <a class="nav-link position-relative <?php echo $current_page == 'keranjang.php' || $current_page == 'checkout.php' ? 'active' : ''; ?>" 
                       href="../user/keranjang.php" 
                       title="Keranjang Belanja">
                        <div class="cart-icon-wrapper">
                            <i class="fas fa-shopping-cart" style="font-size: 1.3rem;"></i>
                            <?php if ($cartCount > 0): ?>
                            <span class="cart-badge" id="cartBadge"><?php echo $cartCount > 99 ? '99+' : $cartCount; ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="d-lg-none ms-2">Keranjang</span>
                        <?php if ($cartCount > 0): ?>
                        <span class="d-none d-lg-inline ms-1 small fw-bold" style="color: var(--color-primary);">
                            (<?php echo $cartCount; ?>)
                        </span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <!-- User Dropdown -->
                <li class="nav-item dropdown ms-lg-2">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" 
                       href="#" role="button" data-bs-toggle="dropdown" 
                       aria-expanded="false"
                       style="padding: 4px 12px;">
                        <div class="avatar-sm" title="<?php echo $_SESSION['nama']; ?>">
                            <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                        </div>
                        <span class="d-none d-md-inline small fw-medium">
                            Halo, <?php echo $nama_depan; ?>
                        </span>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2" style="min-width: 220px;">
                        <!-- User Info Header -->
                        <li>
                            <div class="px-3 py-2">
                                <div class="fw-bold small"><?php echo $_SESSION['nama']; ?></div>
                                <div class="text-muted" style="font-size: 12px;"><?php echo $_SESSION['email']; ?></div>
                            </div>
                        </li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <!-- Menu Items -->
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="../user/dashboard.php">
                                <i class="fas fa-home" style="width: 18px;"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="../user/riwayat.php">
                                <i class="fas fa-history" style="width: 18px;"></i> Riwayat Pesanan
                                <?php if ($pendingOrders > 0): ?>
                                <span class="badge bg-danger ms-auto" style="font-size: 10px;"><?php echo $pendingOrders; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="../user/keranjang.php">
                                <i class="fas fa-shopping-cart" style="width: 18px;"></i> Keranjang Saya
                                <?php if ($cartCount > 0): ?>
                                <span class="badge bg-primary ms-auto" style="font-size: 10px;"><?php echo $cartCount; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <!-- Logout -->
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="../auth/logout.php">
                                <i class="fas fa-sign-out-alt" style="width: 18px;"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ============================================
     MOBILE SEARCH BAR (Muncul di mobile)
     ============================================ -->
<div class="d-lg-none bg-white border-bottom py-2" id="mobileSearchBar" style="display: none;">
    <div class="container">
        <form action="../user/produk.php" method="GET" class="position-relative">
            <i class="fas fa-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: #94A3B8; z-index: 3;"></i>
            <input type="text" name="search" class="form-control ps-5" 
                   placeholder="Cari produk..." 
                   style="border-radius: 100px; height: 42px; border: 2px solid var(--color-border);">
        </form>
    </div>
</div>

<!-- Mobile Search Toggle Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileSearchBar = document.getElementById('mobileSearchBar');
        const searchToggleBtn = document.getElementById('mobileSearchToggle');
        
        if (searchToggleBtn && mobileSearchBar) {
            searchToggleBtn.addEventListener('click', function() {
                if (mobileSearchBar.style.display === 'none') {
                    mobileSearchBar.style.display = 'block';
                    mobileSearchBar.querySelector('input').focus();
                } else {
                    mobileSearchBar.style.display = 'none';
                }
            });
        }
    });
</script>

<!-- Navbar Active State Helper -->
<script>
    // Set active state berdasarkan URL
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href.replace('../user/', ''))) {
                link.classList.add('active');
            }
        });
    });
</script>

<!-- Cart Badge Animation (Saat item ditambahkan) -->
<script>
    // Fungsi untuk mengupdate badge keranjang
    function updateNavCartBadge(count) {
        const badge = document.getElementById('cartBadge');
        if (!badge) {
            // Jika badge belum ada, buat baru
            const cartLink = document.querySelector('.cart-icon-wrapper');
            if (cartLink && count > 0) {
                const newBadge = document.createElement('span');
                newBadge.className = 'cart-badge bounce';
                newBadge.id = 'cartBadge';
                newBadge.textContent = count > 99 ? '99+' : count;
                cartLink.appendChild(newBadge);
            }
        } else {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('bounce');
                void badge.offsetWidth;
                badge.classList.add('bounce');
            } else {
                badge.remove();
            }
        }
    }
</script>