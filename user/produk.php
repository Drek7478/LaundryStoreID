<?php
/**
 * LaundryStoreID - Katalog Produk
 * 
 * Halaman katalog produk dengan filter, search, dan grid produk
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
// AMBIL DATA KATEGORI UNTUK FILTER
// ============================================
$stmt_kategori = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori");
$kategori_list = $stmt_kategori->fetchAll();

// ============================================
// FILTER & SEARCH
// ============================================
$where_clause = "WHERE 1=1";
$params = [];

// Filter kategori
$selected_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
if ($selected_kategori != '') {
    $where_clause .= " AND p.id_kategori = ?";
    $params[] = $selected_kategori;
}

// Search query
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
if ($search_query != '') {
    $where_clause .= " AND (p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";
    $params[] = "%" . $search_query . "%";
    $params[] = "%" . $search_query . "%";
}

// Filter stok (hanya tampilkan yang ada stok)
$show_out_of_stock = isset($_GET['show_all']) ? true : false;
if (!$show_out_of_stock) {
    // Default: tampilkan semua termasuk yang stok 0, tapi urutkan yang ada stok di atas
}

// Sorting
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'terbaru';
$order_clause = "ORDER BY p.created_at DESC";
switch ($sort_by) {
    case 'termurah':
        $order_clause = "ORDER BY p.harga ASC";
        break;
    case 'termahal':
        $order_clause = "ORDER BY p.harga DESC";
        break;
    case 'terlaris':
        $order_clause = "ORDER BY total_terjual DESC";
        break;
    case 'terbaru':
    default:
        $order_clause = "ORDER BY p.created_at DESC";
        break;
}

// ============================================
// AMBIL DATA PRODUK
// ============================================
$sql = "SELECT p.*, k.nama_kategori, 
               COALESCE(SUM(dp.jumlah), 0) as total_terjual
        FROM produk p 
        LEFT JOIN kategori k ON p.id_kategori = k.id 
        LEFT JOIN detail_pesanan dp ON p.id = dp.id_produk
        $where_clause 
        GROUP BY p.id
        $order_clause";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produk_list = $stmt->fetchAll();

// Hitung total produk
$total_produk = count($produk_list);

// Hitung produk dengan stok
$produk_tersedia = 0;
foreach ($produk_list as $p) {
    if ($p['stok'] > 0) $produk_tersedia++;
}

// ============================================
// PAGINATION (Opsional - sederhana)
// ============================================
$per_page = 12;
$total_pages = ceil($total_produk / $per_page);
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// Ambil data dengan pagination
$sql_paginated = $sql . " LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql_paginated);
$stmt->execute($params);
$produk_list = $stmt->fetchAll();
?>

<!-- ============================================
     HEADER HALAMAN KATALOG
     ============================================ -->
<div style="background: linear-gradient(135deg, #F5F3FF, #E0F2FE); padding: 32px 0;">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-2">
                <li class="breadcrumb-item">
                    <a href="dashboard.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    Katalog Produk
                </li>
            </ol>
        </nav>
        
        <!-- Title -->
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-800 mb-1">
                    <i class="fas fa-store me-2" style="color: var(--color-primary);"></i>
                    Katalog Produk
                </h2>
                <p class="text-muted mb-0">
                    Menampilkan <strong><?php echo $total_produk; ?></strong> produk 
                    (<?php echo $produk_tersedia; ?> tersedia)
                    <?php if ($search_query != ''): ?>
                        untuk pencarian "<strong><?php echo sanitize($search_query); ?></strong>"
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- Sort Dropdown (Desktop) -->
            <div class="d-none d-md-block">
                <div class="dropdown">
                    <button class="btn btn-outline btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" style="border-radius: 10px;">
                        <i class="fas fa-sort me-1"></i> 
                        <?php
                        switch ($sort_by) {
                            case 'termurah': echo 'Harga Termurah'; break;
                            case 'termahal': echo 'Harga Termahal'; break;
                            case 'terlaris': echo 'Terlaris'; break;
                            default: echo 'Terbaru';
                        }
                        ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3">
                        <li><a class="dropdown-item <?php echo $sort_by == 'terbaru' ? 'active fw-bold' : ''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'terbaru', 'page' => 1])); ?>"><i class="fas fa-clock me-2"></i> Terbaru</a></li>
                        <li><a class="dropdown-item <?php echo $sort_by == 'terlaris' ? 'active fw-bold' : ''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'terlaris', 'page' => 1])); ?>"><i class="fas fa-fire me-2"></i> Terlaris</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item <?php echo $sort_by == 'termurah' ? 'active fw-bold' : ''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'termurah', 'page' => 1])); ?>"><i class="fas fa-sort-amount-down me-2"></i> Harga Termurah</a></li>
                        <li><a class="dropdown-item <?php echo $sort_by == 'termahal' ? 'active fw-bold' : ''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'termahal', 'page' => 1])); ?>"><i class="fas fa-sort-amount-up me-2"></i> Harga Termahal</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================
     SEARCH & FILTER BAR
     ============================================ -->
<div class="container mt-4">
    <div class="card" style="border-radius: var(--radius-lg); border: 1px solid var(--color-border);">
        <div class="card-body">
            <form method="GET" action="" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Search Input -->
                    <div class="col-lg-5">
                        <label class="form-label fw-semibold small">
                            <i class="fas fa-search me-1"></i> Cari Produk
                        </label>
                        <div class="position-relative">
                            <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); z-index: 2;">
                                <i class="fas fa-search"></i>
                            </span>
                            <input 
                                type="text" 
                                name="search" 
                                class="form-control ps-5" 
                                placeholder="Cari deterjen, pewangi, alat laundry..." 
                                value="<?php echo sanitize($search_query); ?>" 
                                style="border-radius: 100px; height: 44px;"
                                id="searchInput"
                            >
                            <?php if ($search_query != ''): ?>
                            <a href="produk.php" class="position-absolute" style="right: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); z-index: 2;">
                                <i class="fas fa-times-circle"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Filter Kategori (Pills) -->
                    <div class="col-lg-5">
                        <label class="form-label fw-semibold small">
                            <i class="fas fa-filter me-1"></i> Filter Kategori
                        </label>
                        <div class="d-flex flex-wrap gap-2 filter-pills-wrapper" style="padding-top: 2px;">
                            <a href="?<?php echo $search_query != '' ? 'search=' . urlencode($search_query) . '&' : ''; ?>sort=<?php echo $sort_by; ?>" 
                               class="filter-pill <?php echo $selected_kategori == '' ? 'active' : ''; ?>">
                                <i class="fas fa-th-large me-1"></i> Semua
                            </a>
                            <?php foreach ($kategori_list as $kat): ?>
                            <a href="?kategori=<?php echo $kat['id']; ?><?php echo $search_query != '' ? '&search=' . urlencode($search_query) : ''; ?>&sort=<?php echo $sort_by; ?>" 
                               class="filter-pill <?php echo $selected_kategori == $kat['id'] ? 'active' : ''; ?>">
                                <?php echo $kat['nama_kategori']; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Submit & Reset -->
                    <div class="col-lg-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1" style="border-radius: 10px; height: 44px;">
                                <i class="fas fa-search me-1"></i> Cari
                            </button>
                            <?php if ($selected_kategori != '' || $search_query != ''): ?>
                            <a href="produk.php" class="btn btn-outline" style="border-radius: 10px; height: 44px; width: 44px; padding: 0; display: flex; align-items: center; justify-content: center;" title="Reset Filter">
                                <i class="fas fa-redo"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Filters (Show All / Sort Mobile) -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <!-- Sort (Mobile) -->
                            <div class="d-md-none">
                                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()" style="border-radius: 8px; width: auto;">
                                    <option value="terbaru" <?php echo $sort_by == 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                                    <option value="terlaris" <?php echo $sort_by == 'terlaris' ? 'selected' : ''; ?>>Terlaris</option>
                                    <option value="termurah" <?php echo $sort_by == 'termurah' ? 'selected' : ''; ?>>Termurah</option>
                                    <option value="termahal" <?php echo $sort_by == 'termahal' ? 'selected' : ''; ?>>Termahal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================
     PRODUCT GRID
     ============================================ -->
<div class="container mt-4 mb-5">
    <?php if (count($produk_list) > 0): ?>
    
    <!-- Product Count & View Info -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <small class="text-muted">
            Menampilkan <?php echo count($produk_list); ?> dari <?php echo $total_produk; ?> produk
            <?php if ($current_page > 1): ?>
                | Halaman <?php echo $current_page; ?> dari <?php echo $total_pages; ?>
            <?php endif; ?>
        </small>
    </div>
    
    <!-- Grid Produk -->
    <div class="row g-3">
        <?php foreach ($produk_list as $produk): ?>
        <div class="col-6 col-md-4 col-lg-3" data-filter-item data-category="<?php echo $produk['id_kategori']; ?>">
            <div class="product-card animate-slide-up">
                <!-- Product Image -->
                <div class="product-card-img-wrapper">
                    <?php if ($produk['gambar']): ?>
                    <img src="../assets/img/produk/<?php echo $produk['gambar']; ?>" 
                         alt="<?php echo $produk['nama_produk']; ?>" 
                         loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="product-card-img-placeholder" style="display: none;">
                        <i class="fas fa-image"></i>
                    </div>
                    <?php else: ?>
                    <div class="product-card-img-placeholder">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Stock Badge -->
                    <?php if ($produk['stok'] > 0 && $produk['stok'] <= 10): ?>
                    <span class="product-stock-badge">
                        <i class="fas fa-bolt me-1"></i> Terbatas
                    </span>
                    <?php elseif ($produk['stok'] == 0): ?>
                    <div class="product-out-stock-overlay">
                        <i class="fas fa-times-circle me-2"></i> Stok Habis
                    </div>
                    <?php endif; ?>
                    
                    <!-- New Badge (jika produk dibuat dalam 7 hari terakhir) -->
                    <?php if (strtotime($produk['created_at']) > strtotime('-7 days')): ?>
                    <span style="position: absolute; top: 10px; right: 10px; background: var(--color-success); color: white; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 100px; z-index: 2;">
                        <i class="fas fa-star me-1"></i> BARU
                    </span>
                    <?php endif; ?>
                    
                    <!-- Quick Add Button (hanya jika ada stok) -->
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
                    <?php if ($produk['nama_kategori']): ?>
                    <div class="product-category-label">
                        <i class="fas fa-tag me-1"></i> <?php echo $produk['nama_kategori']; ?>
                    </div>
                    <?php endif; ?>
                    
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
                    
                    <!-- Stok Info -->
                    <div class="product-stock-info mt-1">
                        <i class="fas fa-cubes me-1"></i> 
                        Stok: <?php echo $produk['stok']; ?> <?php echo $produk['satuan']; ?>
                        <?php if ($produk['total_terjual'] > 0): ?>
                        <span class="ms-2">
                            <i class="fas fa-shopping-cart me-1"></i> 
                            Terjual: <?php echo $produk['total_terjual']; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 mt-2">
                        <a href="detail_produk.php?id=<?php echo $produk['id']; ?>" 
                           class="btn btn-sm flex-grow-1" 
                           style="background: var(--color-primary-50); color: var(--color-primary); border-radius: 8px; font-weight: 600; font-size: 13px;">
                            <i class="fas fa-eye me-1"></i> Detail
                        </a>
                        <?php if ($produk['stok'] > 0): ?>
                        <form method="POST" action="keranjang.php">
                            <input type="hidden" name="id_produk" value="<?php echo $produk['id']; ?>">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="jumlah" value="1">
                            <button type="submit" class="btn btn-add-cart flex-grow-1" style="font-size: 13px;">
                                <i class="fas fa-cart-plus me-1"></i> Keranjang
                            </button>
                        </form>
                        <?php else: ?>
                        <button class="btn btn-sm flex-grow-1" disabled style="background: #F1F5F9; color: #94A3B8; border-radius: 8px; font-size: 13px;">
                            <i class="fas fa-times-circle me-1"></i> Habis
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- ============================================
         PAGINATION
         ============================================ -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <!-- Previous Page -->
            <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>" style="border-radius: 10px 0 0 10px;">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            
            <!-- Page Numbers -->
            <?php
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            if ($start_page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a>
            </li>
            <?php if ($start_page > 2): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
            <?php endif; ?>
            
            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
            
            <?php if ($end_page < $total_pages): ?>
            <?php if ($end_page < $total_pages - 1): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
            <li class="page-item">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>"><?php echo $total_pages; ?></a>
            </li>
            <?php endif; ?>
            
            <!-- Next Page -->
            <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>" style="border-radius: 0 10px 10px 0;">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Page Info -->
    <div class="text-center mt-2">
        <small class="text-muted">
            Halaman <?php echo $current_page; ?> dari <?php echo $total_pages; ?> 
            (Total <?php echo $total_produk; ?> produk)
        </small>
    </div>
    <?php endif; ?>
    
    <?php else: ?>
    <!-- ============================================
         EMPTY STATE
         ============================================ -->
    <div class="card" style="border-radius: var(--radius-lg);">
        <div class="card-body text-center py-5">
            <?php if ($search_query != '' || $selected_kategori != ''): ?>
            <!-- Hasil pencarian kosong -->
            <i class="fas fa-search" style="font-size: 5rem; color: var(--color-text-muted); opacity: 0.5;"></i>
            <h4 class="fw-bold mt-3">Produk Tidak Ditemukan</h4>
            <p class="text-muted mb-3">
                <?php if ($search_query != ''): ?>
                    Tidak ada produk yang cocok dengan kata kunci "<strong><?php echo sanitize($search_query); ?></strong>"
                <?php endif; ?>
                <?php if ($selected_kategori != ''): ?>
                    <?php 
                    $stmt_kat = $pdo->prepare("SELECT nama_kategori FROM kategori WHERE id = ?");
                    $stmt_kat->execute([$selected_kategori]);
                    $kat_name = $stmt_kat->fetchColumn();
                    ?>
                    dalam kategori "<strong><?php echo $kat_name ? sanitize($kat_name) : 'Tidak diketahui'; ?></strong>"
                <?php endif; ?>
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="produk.php" class="btn btn-primary" style="border-radius: 10px;">
                    <i class="fas fa-redo me-1"></i> Reset Filter
                </a>
                <a href="dashboard.php" class="btn btn-outline" style="border-radius: 10px;">
                    <i class="fas fa-home me-1"></i> Ke Dashboard
                </a>
            </div>
            <?php else: ?>
            <!-- Belum ada produk sama sekali -->
            <i class="fas fa-box-open" style="font-size: 5rem; color: var(--color-text-muted); opacity: 0.5;"></i>
            <h4 class="fw-bold mt-3">Belum Ada Produk</h4>
            <p class="text-muted mb-3">Katalog produk masih kosong. Silakan kembali lagi nanti.</p>
            <a href="dashboard.php" class="btn btn-primary" style="border-radius: 10px;">
                <i class="fas fa-home me-1"></i> Ke Dashboard
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ============================================
     PAGINATION STYLES
     ============================================ -->
<style>
    .pagination .page-link {
        border: 1px solid var(--color-border);
        color: var(--color-text);
        padding: 8px 14px;
        font-weight: 500;
        transition: all 0.2s ease;
        margin: 0 2px;
        border-radius: 8px;
    }
    
    .pagination .page-link:hover {
        background: var(--color-primary-50);
        color: var(--color-primary);
        border-color: var(--color-primary);
    }
    
    .pagination .page-item.active .page-link {
        background: var(--gradient-primary);
        color: white;
        border-color: var(--color-primary);
    }
    
    .pagination .page-item.disabled .page-link {
        color: var(--color-text-muted);
        pointer-events: none;
        background: #F8FAFC;
    }
</style>

<!-- ============================================
     JAVASCRIPT FOR FILTER
     ============================================ -->
<script>
    // Auto-submit when changing sort (mobile)
    document.querySelector('select[name="sort"]')?.addEventListener('change', function() {
        this.form.submit();
    });
    
    // Clear search and submit
    document.querySelector('#searchInput')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.form.submit();
        }
    });
</script>

<?php include '../includes/footer.php'; ?>