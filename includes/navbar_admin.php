<?php
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// Pending orders count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM pesanan WHERE status IN ('menunggu_pembayaran', 'menunggu_konfirmasi')");
$pending_orders = $stmt->fetch()['total'];

// Determine base path
$current_path = $_SERVER['PHP_SELF'];
if (strpos($current_path, '/admin/produk/') !== false || 
    strpos($current_path, '/admin/pesanan/') !== false || 
    strpos($current_path, '/admin/users/') !== false || 
    strpos($current_path, '/admin/stok/') !== false) {
    $base_admin = '../../';
} else {
    $base_admin = '../';
}
?>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Admin Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-glow"></div>
    
    <!-- Logo -->
    <div class="sidebar-logo">
        <h5 class="mb-0">
            <span style="font-size: 1.3rem;"><i class="fas fa-jug-detergent"></i></span>
            <span class="brand-laundry">Laundry</span><span class="brand-store">StoreID</span>
        </h5>
        <span class="admin-badge-tag">ADMIN</span>
    </div>
    
    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Menu Utama</div>
        
        <a class="nav-item-admin <?php echo (basename($current_path) == 'dashboard.php' && strpos($current_path, '/admin/produk/') === false && strpos($current_path, '/admin/pesanan/') === false && strpos($current_path, '/admin/users/') === false) ? 'active' : ''; ?>" 
           href="<?php echo $base_admin; ?>admin/dashboard.php">
            <span class="nav-icon"><i class="fas fa-chart-pie"></i></span> Dashboard
        </a>
        
        <div class="sidebar-section-label mt-3">Manajemen</div>
        
        <a class="nav-item-admin <?php echo (strpos($current_path, '/produk') !== false) ? 'active' : ''; ?>" 
           href="<?php echo $base_admin; ?>admin/produk/index.php">
            <span class="nav-icon"><i class="fas fa-boxes"></i></span> Produk
        </a>
        
        <a class="nav-item-admin <?php echo (strpos($current_path, '/pesanan') !== false) ? 'active' : ''; ?>" 
           href="<?php echo $base_admin; ?>admin/pesanan/index.php">
            <span class="nav-icon"><i class="fas fa-shopping-cart"></i></span> Pesanan
            <?php if ($pending_orders > 0): ?>
            <span class="badge bg-danger ms-auto" style="font-size: 10px;"><?php echo $pending_orders; ?></span>
            <?php endif; ?>
        </a>
        
        <a class="nav-item-admin <?php echo (strpos($current_path, '/users') !== false) ? 'active' : ''; ?>" 
           href="<?php echo $base_admin; ?>admin/users/index.php">
            <span class="nav-icon"><i class="fas fa-users"></i></span> Users
        </a>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #7C3AED, #06B6D4); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px;">
                <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
            </div>
            <div>
                <div style="font-size: 13px; color: rgba(255,255,255,0.8);"><?php echo $_SESSION['nama']; ?></div>
                <div style="font-size: 11px; color: rgba(255,255,255,0.4);">Administrator</div>
            </div>
        </div>
        <a href="<?php echo $base_admin; ?>auth/logout.php" class="btn btn-sm w-100 mt-2" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;">
            <i class="fas fa-sign-out-alt me-1"></i> Logout
        </a>
    </div>
</aside>

<!-- Admin Main Content Wrapper -->
<div class="admin-main">
    <!-- Top Bar -->
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light btn-sm d-lg-none sidebar-toggle-btn" style="border-radius: 8px;">
                <i class="fas fa-bars" style="font-size: 1.3rem;"></i>
            </button>
            <div>
                <h6 class="mb-0 fw-bold"><?php echo date('l, d F Y'); ?></h6>
                <small class="text-muted">Selamat datang, <?php echo explode(' ', $_SESSION['nama'])[0]; ?></small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="<?php echo $base_admin; ?>../user/dashboard.php" target="_blank" class="btn btn-sm" style="background: var(--color-primary-50); color: var(--color-primary); border-radius: 8px;">
                <i class="fas fa-eye me-1"></i> Lihat Website
            </a>
        </div>
    </div>
    
    <div class="p-4">