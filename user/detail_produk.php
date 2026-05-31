<?php
/**
 * LaundryStoreID - Detail Produk
 * 
 * Halaman detail produk lengkap dengan informasi, gambar, dan aksi
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
// AMBIL ID PRODUK DARI URL
// ============================================
$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_produk <= 0) {
    setAlert('danger', 'Produk tidak ditemukan!');
    redirect('produk.php');
}

// ============================================
// AMBIL DATA PRODUK
// ============================================
$stmt = $pdo->prepare("
    SELECT p.*, k.nama_kategori,
           COALESCE(SUM(dp.jumlah), 0) as total_terjual,
           COUNT(DISTINCT dp.id_pesanan) as total_pesanan
    FROM produk p 
    LEFT JOIN kategori k ON p.id_kategori = k.id 
    LEFT JOIN detail_pesanan dp ON p.id = dp.id_produk
    WHERE p.id = ?
    GROUP BY p.id
");
$stmt->execute([$id_produk]);
$produk = $stmt->fetch();

// Jika produk tidak ditemukan
if (!$produk) {
    setAlert('danger', 'Produk tidak ditemukan!');
    redirect('produk.php');
}

// ============================================
// AMBIL PRODUK TERKAIT (Kategori Sama)
// ============================================
$stmt = $pdo->prepare("
    SELECT * FROM produk 
    WHERE id_kategori = ? AND id != ? AND stok > 0 
    ORDER BY created_at DESC 
    LIMIT 4
");
$stmt->execute([$produk['id_kategori'], $id_produk]);
$related_products = $stmt->fetchAll();

// ============================================
// AMBIL PRODUK TERLARIS (Fallback jika terkait kosong)
// ============================================
if (count($related_products) < 4) {
    $stmt = $pdo->query("
        SELECT p.*, COALESCE(SUM(dp.jumlah), 0) as total_terjual
        FROM produk p 
        LEFT JOIN detail_pesanan dp ON p.id = dp.id_produk
        WHERE p.id != $id_produk AND p.stok > 0
        GROUP BY p.id
        ORDER BY total_terjual DESC 
        LIMIT 4
    ");
    $popular_products = $stmt->fetchAll();
}

// ============================================
// AMBIL ULASAN (Jika ada tabel ulasan)
// ============================================
$reviews = [];
// Uncomment jika ada tabel reviews
/*
$stmt = $pdo->prepare("
    SELECT r.*, u.nama 
    FROM reviews r 
    JOIN users u ON r.id_user = u.id 
    WHERE r.id_produk = ? 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$stmt->execute([$id_produk]);
$reviews = $stmt->fetchAll();
*/

// ============================================
// FORMAT DATA
// ============================================
$stok_tersedia = $produk['stok'] > 0;
$stok_terbatas = $produk['stok'] > 0 && $produk['stok'] <= 10;
$is_new = strtotime($produk['created_at']) > strtotime('-14 days');
$has_discount = false; // Bisa ditambahkan logika diskon
$discount_percent = 0;
$harga_diskon = $produk['harga'];

// Rating (dummy, bisa diganti dengan data asli)
$rating = 4.5;
$total_reviews = 128;
?>

<!-- ============================================
     BREADCRUMB
     ============================================ -->
<div style="background: linear-gradient(135deg, #F5F3FF, #E0F2FE); padding: 20px 0;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item">
                    <a href="dashboard.php"><i class="fas fa-home me-1"></i> Home</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="produk.php"><i class="fas fa-store me-1"></i> Katalog</a>
                </li>
                <?php if ($produk['nama_kategori']): ?>
                <li class="breadcrumb-item">
                    <a href="produk.php?kategori=<?php echo $produk['id_kategori']; ?>">
                        <?php echo $produk['nama_kategori']; ?>
                    </a>
                </li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo $produk['nama_produk']; ?>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- ============================================
     MAIN CONTENT
     ============================================ -->
<div class="container mt-4 mb-5">
    <div class="row g-4">
        
        <!-- ============================================
             LEFT COLUMN - PRODUCT IMAGE
             ============================================ -->
        <div class="col-lg-6">
            <div class="position-relative" style="border-radius: var(--radius-lg); overflow: hidden; border: 2px solid var(--color-border); background: white;">
                
                <!-- Main Image -->
                <?php if ($produk['gambar']): ?>
                <img src="../assets/img/produk/<?php echo $produk['gambar']; ?>" 
                     alt="<?php echo $produk['nama_produk']; ?>" 
                     id="mainProductImage"
                     style="width: 100%; aspect-ratio: 1; object-fit: cover; cursor: zoom-in;"
                     onclick="enlargeImage('../assets/img/produk/<?php echo $produk['gambar']; ?>')"
                     onerror="this.style.display='none'; document.getElementById('imagePlaceholder').style.display='flex';">
                <div id="imagePlaceholder" style="display: none; width: 100%; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #F5F3FF, #CFFAFE); font-size: 6rem; color: var(--color-primary);">
                    <i class="fas fa-image"></i>
                </div>
                <?php else: ?>
                <div style="width: 100%; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #F5F3FF, #CFFAFE); font-size: 6rem; color: var(--color-primary);">
                    <i class="fas fa-box-open"></i>
                </div>
                <?php endif; ?>
                
                <!-- Badges on Image -->
                <?php if ($is_new): ?>
                <span style="position: absolute; top: 16px; left: 16px; background: var(--color-success); color: white; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 100px; z-index: 2;">
                    <i class="fas fa-star me-1"></i> BARU
                </span>
                <?php endif; ?>
                
                <?php if ($has_discount): ?>
                <span style="position: absolute; top: 16px; right: 16px; background: var(--color-danger); color: white; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 100px; z-index: 2;">
                    <i class="fas fa-tag me-1"></i> -<?php echo $discount_percent; ?>%
                </span>
                <?php endif; ?>
                
                <?php if ($stok_terbatas): ?>
                <span style="position: absolute; bottom: 16px; left: 16px; background: var(--color-warning); color: #92400E; font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 100px; z-index: 2;">
                    <i class="fas fa-bolt me-1"></i> Stok Terbatas
                </span>
                <?php endif; ?>
                
                <?php if (!$stok_tersedia): ?>
                <div style="position: absolute; inset: 0; background: rgba(15,23,42,0.7); display: flex; align-items: center; justify-content: center; z-index: 2;">
                    <div style="text-align: center; color: white;">
                        <i class="fas fa-times-circle" style="font-size: 4rem; margin-bottom: 10px; display: block;"></i>
                        <span style="font-size: 1.5rem; font-weight: 800;">Stok Habis</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- ============================================
             RIGHT COLUMN - PRODUCT INFO
             ============================================ -->
        <div class="col-lg-6">
            
            <!-- Kategori Badge -->
            <?php if ($produk['nama_kategori']): ?>
            <a href="produk.php?kategori=<?php echo $produk['id_kategori']; ?>" class="text-decoration-none">
                <span style="background: var(--color-accent-light); color: var(--color-accent-dark); padding: 6px 14px; border-radius: 100px; font-size: 12px; font-weight: 600; display: inline-block; margin-bottom: 10px;">
                    <i class="fas fa-tag me-1"></i> <?php echo $produk['nama_kategori']; ?>
                </span>
            </a>
            <?php endif; ?>
            
            <!-- Nama Produk -->
            <h1 class="fw-800 mb-2" style="font-size: 1.8rem; line-height: 1.3;">
                <?php echo $produk['nama_produk']; ?>
            </h1>
            
            <!-- Rating & Terjual -->
            <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                <!-- Stars -->
                <div class="d-flex align-items-center">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= floor($rating)): ?>
                            <i class="fas fa-star" style="color: #F59E0B; font-size: 14px;"></i>
                        <?php elseif ($i - 0.5 <= $rating): ?>
                            <i class="fas fa-star-half-alt" style="color: #F59E0B; font-size: 14px;"></i>
                        <?php else: ?>
                            <i class="far fa-star" style="color: #D1D5DB; font-size: 14px;"></i>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <span class="ms-1 fw-semibold small"><?php echo $rating; ?></span>
                    <span class="text-muted small ms-1">(<?php echo $total_reviews; ?> ulasan)</span>
                </div>
                
                <span style="color: #D1D5DB;">|</span>
                
                <!-- Terjual -->
                <span class="small text-muted">
                    <i class="fas fa-shopping-cart me-1"></i> 
                    <?php echo $produk['total_terjual']; ?> terjual
                </span>
                
                <?php if ($is_new): ?>
                <span style="color: #D1D5DB;">|</span>
                <span class="badge" style="background: #D1FAE5; color: #065F46; font-size: 11px;">
                    <i class="fas fa-star me-1"></i> Produk Baru
                </span>
                <?php endif; ?>
            </div>
            
            <!-- Harga -->
            <div class="mb-4 p-3" style="background: #F8FAFC; border-radius: 12px;">
                <div class="d-flex align-items-baseline gap-2">
                    <?php if ($has_discount): ?>
                    <span style="text-decoration: line-through; color: #94A3B8; font-size: 1rem;">
                        Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?>
                    </span>
                    <span style="font-size: 2rem; font-weight: 800; color: var(--color-danger);">
                        Rp <?php echo number_format($harga_diskon, 0, ',', '.'); ?>
                    </span>
                    <?php else: ?>
                    <span style="font-size: 2rem; font-weight: 800; color: var(--color-primary);">
                        Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?>
                    </span>
                    <?php endif; ?>
                    <span style="background: #F1F5F9; color: var(--color-text-muted); padding: 4px 10px; border-radius: 6px; font-size: 13px;">
                        / <?php echo $produk['satuan']; ?>
                    </span>
                </div>
            </div>
            
            <!-- Info Stok -->
            <div class="mb-3">
                <?php if ($stok_tersedia): ?>
                    <?php if ($stok_terbatas): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2" style="border-radius: 10px; padding: 10px 14px; font-size: 14px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Stok terbatas! Tersisa <strong><?php echo $produk['stok']; ?> <?php echo $produk['satuan']; ?></strong> saja.</span>
                    </div>
                    <?php else: ?>
                    <div style="color: var(--color-success); font-weight: 600; font-size: 14px;">
                        <i class="fas fa-check-circle me-1"></i> 
                        Stok tersedia: <strong><?php echo $produk['stok']; ?> <?php echo $produk['satuan']; ?></strong>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                <div class="alert alert-danger d-flex align-items-center gap-2" style="border-radius: 10px; padding: 10px 14px; font-size: 14px;">
                    <i class="fas fa-times-circle"></i>
                    <span>Maaf, produk ini sedang kosong. Silakan cek kembali nanti.</span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Add to Cart Form -->
            <?php if ($stok_tersedia): ?>
            <form method="POST" action="keranjang.php" class="mt-4">
                <input type="hidden" name="id_produk" value="<?php echo $produk['id']; ?>">
                <input type="hidden" name="action" value="add">
                
                <!-- Quantity Selector -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Jumlah:</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="qty-stepper">
                            <button type="button" class="qty-btn" onclick="changeQty(-1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" name="jumlah" id="qtyInput" class="qty-input" 
                                   value="1" min="1" max="<?php echo $produk['stok']; ?>" readonly>
                            <button type="button" class="qty-btn" onclick="changeQty(1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <span class="text-muted small"><?php echo $produk['satuan']; ?></span>
                        
                        <!-- Stok info -->
                        <span class="text-muted small">
                            (Maks. <?php echo $produk['stok']; ?> <?php echo $produk['satuan']; ?>)
                        </span>
                    </div>
                </div>
                
                <!-- Subtotal Preview -->
                <div class="mb-3 p-3" style="background: #F8FAFC; border-radius: 10px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Subtotal:</span>
                        <span id="subtotalPreview" style="font-weight: 700; color: var(--color-primary);">
                            Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg flex-grow-1" style="border-radius: 12px;">
                        <i class="fas fa-cart-plus me-2"></i> Tambah ke Keranjang
                    </button>
                    <button type="button" class="btn btn-outline btn-lg" style="border-radius: 12px; border-color: var(--color-primary); color: var(--color-primary);" 
                            onclick="beliSekarang()">
                        <i class="fas fa-bolt me-2"></i> Beli Sekarang
                    </button>
                </div>
            </form>
            <?php else: ?>
            <!-- Notify Me Button (Optional) -->
            <button class="btn btn-lg w-100 mt-4" disabled style="background: #F1F5F9; color: #94A3B8; border-radius: 12px; padding: 14px;">
                <i class="fas fa-times-circle me-2"></i> Stok Habis
            </button>
            <?php endif; ?>
            
            <!-- Info Tambahan -->
            <div class="mt-4 p-3" style="background: #F8FAFC; border-radius: 12px;">
                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted d-block">
                            <i class="fas fa-box me-1"></i> Kategori:
                        </small>
                        <small class="fw-semibold"><?php echo $produk['nama_kategori'] ?? 'Umum'; ?></small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">
                            <i class="fas fa-weight me-1"></i> Satuan:
                        </small>
                        <small class="fw-semibold"><?php echo $produk['satuan']; ?></small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">
                            <i class="fas fa-shopping-cart me-1"></i> Terjual:
                        </small>
                        <small class="fw-semibold"><?php echo $produk['total_terjual']; ?> <?php echo $produk['satuan']; ?></small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">
                            <i class="fas fa-calendar me-1"></i> Ditambahkan:
                        </small>
                        <small class="fw-semibold"><?php echo date('d M Y', strtotime($produk['created_at'])); ?></small>
                    </div>
                </div>
            </div>
            
            <!-- Share Buttons -->
            <div class="mt-3 d-flex align-items-center gap-2">
                <small class="text-muted">Bagikan:</small>
                <a href="#" class="btn btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; background: #EFF6FF; color: #3B82F6;" title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="btn btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; background: #F0FDF4; color: #10B981;" title="WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="#" class="btn btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; background: #FEF2F2; color: #EF4444;" title="Copy Link" onclick="copyToClipboard(window.location.href)">
                    <i class="fas fa-link"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- ============================================
         DESKRIPSI PRODUK (FULL WIDTH)
         ============================================ -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card" style="border-radius: var(--radius-lg);">
                <div class="card-header" style="background: linear-gradient(135deg, #F5F3FF, #E0F2FE);">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-align-left me-2"></i> Deskripsi Produk
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($produk['deskripsi'])): ?>
                    <div style="line-height: 1.8; color: var(--color-text);">
                        <?php echo nl2br($produk['deskripsi']); ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-2"></i> 
                        Belum ada deskripsi untuk produk ini.
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ============================================
         PRODUK TERKAIT
         ============================================ -->
    <?php if (count($related_products) > 0 || count($popular_products ?? []) > 0): ?>
    <div class="mt-5">
        <div class="mb-4">
            <h3 class="fw-800 mb-1">
                <i class="fas fa-link me-2" style="color: var(--color-primary);"></i>
                Produk Terkait
            </h3>
            <p class="text-muted small">Produk lain yang mungkin Anda sukai</p>
            <div style="width: 50px; height: 4px; background: var(--gradient-primary); border-radius: 2px;"></div>
        </div>
        
        <div class="row g-3">
            <?php 
            $display_products = count($related_products) > 0 ? $related_products : ($popular_products ?? []);
            foreach (array_slice($display_products, 0, 4) as $rp): 
            ?>
            <div class="col-6 col-md-3">
                <div class="product-card">
                    <div class="product-card-img-wrapper">
                        <?php if ($rp['gambar']): ?>
                        <img src="../assets/img/produk/<?php echo $rp['gambar']; ?>" 
                             alt="<?php echo $rp['nama_produk']; ?>" 
                             loading="lazy">
                        <?php else: ?>
                        <div class="product-card-img-placeholder">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-body">
                        <div class="product-name"><?php echo $rp['nama_produk']; ?></div>
                        <div class="product-price-row">
                            <span class="product-price-main">Rp <?php echo number_format($rp['harga'], 0, ',', '.'); ?></span>
                            <span class="product-price-unit">/ <?php echo $rp['satuan']; ?></span>
                        </div>
                        <a href="detail_produk.php?id=<?php echo $rp['id']; ?>" class="btn btn-add-cart mt-2">
                            <i class="fas fa-eye me-1"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ============================================
     JAVASCRIPT
     ============================================ -->
<script>
    // ============================================
    // QUANTITY CHANGER
    // ============================================
    const maxStok = <?php echo $produk['stok']; ?>;
    const hargaSatuan = <?php echo $produk['harga']; ?>;
    
    function changeQty(change) {
        const input = document.getElementById('qtyInput');
        let value = parseInt(input.value) + change;
        
        if (value < 1) value = 1;
        if (value > maxStok) value = maxStok;
        
        input.value = value;
        updateSubtotal();
    }
    
    function updateSubtotal() {
        const qty = parseInt(document.getElementById('qtyInput').value);
        const subtotal = qty * hargaSatuan;
        document.getElementById('subtotalPreview').textContent = 
            'Rp ' + subtotal.toLocaleString('id-ID');
    }
    
    // Update subtotal saat input berubah
    document.getElementById('qtyInput').addEventListener('change', updateSubtotal);
    
    // ============================================
    // BELI SEKARANG
    // ============================================
    function beliSekarang() {
        // Tambah ke keranjang dulu, lalu redirect ke checkout
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'keranjang.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id_produk';
        idInput.value = '<?php echo $produk['id']; ?>';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'add';
        
        const jumlahInput = document.createElement('input');
        jumlahInput.type = 'hidden';
        jumlahInput.name = 'jumlah';
        jumlahInput.value = document.getElementById('qtyInput').value;
        
        form.appendChild(idInput);
        form.appendChild(actionInput);
        form.appendChild(jumlahInput);
        document.body.appendChild(form);
        form.submit();
        
        // Set flag untuk redirect ke checkout
        sessionStorage.setItem('redirect_to_checkout', 'true');
    }
</script>

<?php include '../includes/footer.php'; ?>