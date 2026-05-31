<?php
/**
 * LaundryStoreID - Keranjang Belanja
 * 
 * Halaman keranjang belanja user dengan fitur update quantity, hapus item, dan checkout
 */

require_once '../config/db.php';

// Proteksi: Hanya user yang sudah login dan BUKAN admin
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}
if (isAdmin()) {
    redirect('../admin/dashboard.php');
}

// ============================================
// HANDLE ACTIONS (ADD, UPDATE, DELETE)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id_user = $_SESSION['user_id'];
    
    if ($action === 'add') {
        // Tambah produk ke keranjang
        $id_produk = (int)$_POST['id_produk'];
        $jumlah = (int)($_POST['jumlah'] ?? 1);
        
        // Cek stok produk
        $stmt = $pdo->prepare("SELECT stok, nama_produk FROM produk WHERE id = ?");
        $stmt->execute([$id_produk]);
        $produk = $stmt->fetch();
        
        if (!$produk) {
            setAlert('danger', 'Produk tidak ditemukan!');
        } elseif ($jumlah <= 0) {
            setAlert('danger', 'Jumlah tidak valid!');
        } elseif ($jumlah > $produk['stok']) {
            setAlert('danger', 'Stok tidak mencukupi! Maksimal ' . $produk['stok'] . ' item.');
        } else {
            // Cek apakah produk sudah ada di keranjang
            $stmt = $pdo->prepare("SELECT id, jumlah FROM keranjang WHERE id_user = ? AND id_produk = ?");
            $stmt->execute([$id_user, $id_produk]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update jumlah jika sudah ada
                $new_jumlah = $existing['jumlah'] + $jumlah;
                if ($new_jumlah > $produk['stok']) {
                    $new_jumlah = $produk['stok'];
                    setAlert('warning', 'Jumlah disesuaikan dengan stok tersedia (' . $produk['stok'] . ' item).');
                } else {
                    setAlert('success', 'Jumlah produk berhasil diupdate!');
                }
                $stmt = $pdo->prepare("UPDATE keranjang SET jumlah = ? WHERE id = ?");
                $stmt->execute([$new_jumlah, $existing['id']]);
            } else {
                // Insert baru
                $stmt = $pdo->prepare("INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (?, ?, ?)");
                $stmt->execute([$id_user, $id_produk, $jumlah]);
                setAlert('success', 'Produk berhasil ditambahkan ke keranjang! 🛒');
            }
        }
    } elseif ($action === 'update') {
        // Update jumlah item di keranjang
        $id_keranjang = (int)$_POST['id_keranjang'];
        $jumlah = (int)$_POST['jumlah'];
        
        if ($jumlah <= 0) {
            // Jika jumlah 0 atau minus, hapus item
            $stmt = $pdo->prepare("DELETE FROM keranjang WHERE id = ? AND id_user = ?");
            $stmt->execute([$id_keranjang, $id_user]);
            setAlert('success', 'Produk berhasil dihapus dari keranjang!');
        } else {
            // Cek stok
            $stmt = $pdo->prepare("SELECT p.stok FROM keranjang k JOIN produk p ON k.id_produk = p.id WHERE k.id = ? AND k.id_user = ?");
            $stmt->execute([$id_keranjang, $id_user]);
            $cart_item = $stmt->fetch();
            
            if ($cart_item) {
                if ($jumlah > $cart_item['stok']) {
                    $jumlah = $cart_item['stok'];
                    setAlert('warning', 'Jumlah disesuaikan dengan stok tersedia.');
                } else {
                    setAlert('success', 'Jumlah berhasil diupdate!');
                }
                $stmt = $pdo->prepare("UPDATE keranjang SET jumlah = ? WHERE id = ? AND id_user = ?");
                $stmt->execute([$jumlah, $id_keranjang, $id_user]);
            }
        }
    } elseif ($action === 'delete') {
        // Hapus item dari keranjang
        $id_keranjang = (int)$_POST['id_keranjang'];
        $stmt = $pdo->prepare("DELETE FROM keranjang WHERE id = ? AND id_user = ?");
        $stmt->execute([$id_keranjang, $id_user]);
        setAlert('success', 'Produk berhasil dihapus dari keranjang!');
    } elseif ($action === 'clear') {
        // Kosongkan keranjang
        $stmt = $pdo->prepare("DELETE FROM keranjang WHERE id_user = ?");
        $stmt->execute([$id_user]);
        setAlert('success', 'Keranjang berhasil dikosongkan!');
    }
    
    // Redirect untuk mencegah resubmission
    redirect('keranjang.php');
}

include '../includes/header.php';
include '../includes/navbar_user.php';

// ============================================
// AMBIL DATA KERANJANG
// ============================================
$stmt = $pdo->prepare("
    SELECT k.*, p.nama_produk, p.harga, p.gambar, p.stok, p.satuan, 
           kt.nama_kategori
    FROM keranjang k 
    JOIN produk p ON k.id_produk = p.id 
    LEFT JOIN kategori kt ON p.id_kategori = kt.id
    WHERE k.id_user = ?
    ORDER BY k.id DESC
");
$stmt->execute([$_SESSION['user_id']]);
$keranjang_items = $stmt->fetchAll();

// ============================================
// HITUNG TOTAL
// ============================================
$total_harga = 0;
$total_item = 0;
$total_berat = 0; // Jika ada kolom berat
$item_with_issues = []; // Item dengan masalah (stok habis, dll)

foreach ($keranjang_items as $item) {
    $subtotal = $item['harga'] * $item['jumlah'];
    $total_harga += $subtotal;
    $total_item += $item['jumlah'];
    
    // Cek masalah
    if ($item['stok'] == 0) {
        $item_with_issues[] = [
            'id' => $item['id'],
            'nama' => $item['nama_produk'],
            'issue' => 'stok_habis'
        ];
    } elseif ($item['jumlah'] > $item['stok']) {
        $item_with_issues[] = [
            'id' => $item['id'],
            'nama' => $item['nama_produk'],
            'issue' => 'stok_kurang',
            'stok' => $item['stok'],
            'jumlah' => $item['jumlah']
        ];
    }
}

// Ongkos kirim (gratis jika > 150000)
$ongkir = $total_harga >= 150000 ? 0 : 15000;
$grand_total = $total_harga + $ongkir;
?>

<!-- ============================================
     HEADER
     ============================================ -->
<div style="background: linear-gradient(135deg, #F5F3FF, #E0F2FE); padding: 32px 0;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-2">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home me-1"></i> Home</a></li>
                <li class="breadcrumb-item active">Keranjang Belanja</li>
            </ol>
        </nav>
        <h2 class="fw-800 mb-1">
            <i class="fas fa-shopping-cart me-2" style="color: var(--color-primary);"></i>
            Keranjang Belanja
        </h2>
        <p class="text-muted mb-0">
            <?php echo count($keranjang_items); ?> item dalam keranjang · 
            Total <?php echo $total_item; ?> produk
        </p>
    </div>
</div>

<div class="container py-4">
    
    <?php if (count($keranjang_items) > 0): ?>
    
    <!-- Warning jika ada item bermasalah -->
    <?php if (count($item_with_issues) > 0): ?>
    <div class="alert alert-warning d-flex align-items-start gap-2 mb-4" style="border-radius: 10px;">
        <i class="fas fa-exclamation-triangle mt-1"></i>
        <div>
            <strong>Perhatian!</strong> Beberapa item perlu diperiksa:
            <ul class="mb-0 mt-1 small">
                <?php foreach ($item_with_issues as $issue): ?>
                    <?php if ($issue['issue'] == 'stok_habis'): ?>
                    <li><strong><?php echo $issue['nama']; ?></strong> - Stok habis, silakan hapus dari keranjang.</li>
                    <?php elseif ($issue['issue'] == 'stok_kurang'): ?>
                    <li><strong><?php echo $issue['nama']; ?></strong> - Stok hanya <?php echo $issue['stok']; ?>, Anda memesan <?php echo $issue['jumlah']; ?>.</li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row g-4">
        <!-- ============================================
             DAFTAR ITEM KERANJANG
             ============================================ -->
        <div class="col-lg-8">
            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden;">
                <!-- Card Header -->
                <div style="background: linear-gradient(135deg, #F5F3FF, #E0F2FE); padding: 16px 24px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
                    <span class="fw-bold">
                        <i class="fas fa-list me-2"></i> Daftar Produk
                    </span>
                    <form method="POST" action="" onsubmit="return confirmDelete('Kosongkan seluruh keranjang?')" class="d-inline">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-sm" style="background: #FEF2F2; color: #EF4444; border-radius: 8px; font-size: 12px;">
                            <i class="fas fa-trash-alt me-1"></i> Kosongkan
                        </button>
                    </form>
                </div>
                
                <!-- Cart Items -->
                <?php foreach ($keranjang_items as $index => $item): 
                    $subtotal = $item['harga'] * $item['jumlah'];
                    $has_issue = $item['stok'] == 0 || $item['jumlah'] > $item['stok'];
                ?>
                <div class="cart-item" style="<?php echo $has_issue ? 'background: #FFFBEB;' : ''; ?> <?php echo $index < count($keranjang_items) - 1 ? 'border-bottom: 1px solid #F1F5F9;' : ''; ?>">
                    
                    <!-- Product Image -->
                    <div style="flex-shrink: 0;">
                        <a href="detail_produk.php?id=<?php echo $item['id_produk']; ?>">
                            <?php if ($item['gambar']): ?>
                            <img src="../assets/img/produk/<?php echo $item['gambar']; ?>" 
                                 class="cart-item-img" 
                                 alt="<?php echo $item['nama_produk']; ?>"
                                 loading="lazy">
                            <?php else: ?>
                            <div class="cart-item-img" style="background: linear-gradient(135deg, #F5F3FF, #CFFAFE); display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--color-primary);">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <!-- Product Info -->
                    <div style="flex-grow: 1; min-width: 150px;">
                        <a href="detail_produk.php?id=<?php echo $item['id_produk']; ?>" class="text-decoration-none">
                            <div class="cart-item-name"><?php echo $item['nama_produk']; ?></div>
                        </a>
                        <?php if ($item['nama_kategori']): ?>
                        <div class="cart-item-category">
                            <i class="fas fa-tag me-1"></i> <?php echo $item['nama_kategori']; ?>
                        </div>
                        <?php endif; ?>
                        <div class="cart-item-price">
                            Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?> 
                            <small class="text-muted">/ <?php echo $item['satuan']; ?></small>
                        </div>
                        
                        <!-- Stock Warning -->
                        <?php if ($item['stok'] == 0): ?>
                        <small class="text-danger fw-semibold">
                            <i class="fas fa-times-circle me-1"></i> Stok habis
                        </small>
                        <?php elseif ($item['jumlah'] > $item['stok']): ?>
                        <small class="text-warning fw-semibold">
                            <i class="fas fa-exclamation-triangle me-1"></i> 
                            Stok hanya <?php echo $item['stok']; ?> <?php echo $item['satuan']; ?>
                        </small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quantity Stepper -->
                    <div style="flex-shrink: 0;">
                        <form method="POST" action="" id="updateForm<?php echo $item['id']; ?>">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id_keranjang" value="<?php echo $item['id']; ?>">
                            <div class="qty-stepper">
                                <button type="button" class="qty-btn" 
                                        onclick="updateCartQty(<?php echo $item['id']; ?>, -1, <?php echo $item['stok']; ?>)"
                                        <?php echo $item['stok'] == 0 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" name="jumlah" id="qtyInput<?php echo $item['id']; ?>" 
                                       class="qty-input" 
                                       value="<?php echo $item['jumlah']; ?>" 
                                       min="1" max="<?php echo max($item['stok'], 1); ?>" 
                                       readonly>
                                <button type="button" class="qty-btn" 
                                        onclick="updateCartQty(<?php echo $item['id']; ?>, 1, <?php echo $item['stok']; ?>)"
                                        <?php echo $item['stok'] == 0 || $item['jumlah'] >= $item['stok'] ? 'disabled' : ''; ?>>
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Subtotal -->
                    <div class="cart-item-subtotal" style="min-width: 110px;">
                        Rp <?php echo number_format($subtotal, 0, ',', '.'); ?>
                    </div>
                    
                    <!-- Delete Button -->
                    <div style="flex-shrink: 0;">
                        <form method="POST" action="" onsubmit="return confirmDelete('Hapus <?php echo $item['nama_produk']; ?> dari keranjang?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id_keranjang" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn-remove-item" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Lanjut Belanja -->
            <div class="mt-3">
                <a href="produk.php" class="btn btn-outline" style="border-radius: 10px;">
                    <i class="fas fa-arrow-left me-1"></i> Lanjut Belanja
                </a>
            </div>
        </div>
        
        <!-- ============================================
             RINGKASAN PESANAN (SIDEBAR)
             ============================================ -->
        <div class="col-lg-4">
            <div style="position: sticky; top: 100px;">
                <div class="card card-gradient-header" style="border-radius: var(--radius-lg); overflow: hidden;">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-receipt me-2"></i> Ringkasan Pesanan
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Detail Ringkasan -->
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Item</span>
                            <span class="fw-semibold"><?php echo count($keranjang_items); ?> item (<?php echo $total_item; ?> pcs)</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal Produk</span>
                            <span class="fw-semibold">Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">
                                <i class="fas fa-truck me-1"></i> Ongkos Kirim
                            </span>
                            <?php if ($ongkir == 0): ?>
                            <span class="text-success fw-bold">
                                <i class="fas fa-check-circle me-1"></i> Gratis
                            </span>
                            <?php else: ?>
                            <span class="fw-semibold">Rp <?php echo number_format($ongkir, 0, ',', '.'); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($ongkir > 0): ?>
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i> 
                                Gratis ongkir untuk pembelian min. Rp 150.000
                            </small>
                            <?php 
                            $sisa_gratis_ongkir = 150000 - $total_harga;
                            if ($sisa_gratis_ongkir > 0): 
                            ?>
                            <div class="progress mt-1" style="height: 6px; border-radius: 3px;">
                                <div class="progress-bar" style="width: <?php echo min(100, ($total_harga / 150000) * 100); ?>%; background: var(--gradient-primary); border-radius: 3px;"></div>
                            </div>
                            <small class="text-muted">
                                Belanja Rp <?php echo number_format($sisa_gratis_ongkir, 0, ',', '.'); ?> lagi untuk gratis ongkir!
                            </small>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <!-- Grand Total -->
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total Pembayaran</span>
                            <span style="font-size: 1.3rem; font-weight: 800; color: var(--color-primary);">
                                Rp <?php echo number_format($grand_total, 0, ',', '.'); ?>
                            </span>
                        </div>
                        
                        <!-- Checkout Button -->
                        <?php if (count($item_with_issues) > 0): ?>
                        <div class="alert alert-warning small mb-3" style="border-radius: 10px;">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Ada item yang perlu diperiksa sebelum checkout.
                        </div>
                        <button class="btn btn-secondary w-100 btn-lg" disabled style="border-radius: 12px;">
                            <i class="fas fa-times-circle me-2"></i> Tidak Dapat Checkout
                        </button>
                        <?php else: ?>
                        <a href="checkout.php" class="btn btn-primary w-100 btn-lg" style="border-radius: 12px; font-weight: 700;">
                            <i class="fas fa-shopping-cart me-2"></i> Proses Checkout
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <?php endif; ?>
                        
                        <!-- Keamanan -->
                        <p class="text-center text-muted small mt-3 mb-0">
                            <i class="fas fa-lock me-1"></i> Transaksi aman & terjamin
                        </p>
                        <p class="text-center text-muted small mt-1 mb-0">
                            <i class="fas fa-shield-alt me-1"></i> Data Anda dilindungi
                        </p>
                    </div>
                </div>
                
                <!-- Metode Pembayaran yang Tersedia -->
                <div class="card mt-3" style="border-radius: var(--radius-lg);">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 small">
                            <i class="fas fa-credit-card me-2"></i> Pembayaran Tersedia
                        </h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge" style="background: #EFF6FF; color: #3B82F6; padding: 6px 10px;">BCA</span>
                            <span class="badge" style="background: #FEF3C7; color: #92400E; padding: 6px 10px;">BRI</span>
                            <span class="badge" style="background: #ECFDF5; color: #065F46; padding: 6px 10px;">Mandiri</span>
                            <span class="badge" style="background: #FEF2F2; color: #991B1B; padding: 6px 10px;">BNI</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- ============================================
         KERANJANG KOSONG
         ============================================ -->
    <div class="card" style="border-radius: var(--radius-lg);">
        <div class="card-body text-center py-5">
            <!-- Empty Icon -->
            <div style="position: relative; display: inline-block; margin-bottom: 20px;">
                <i class="fas fa-shopping-cart" style="font-size: 6rem; color: #D1D5DB;"></i>
                <span style="position: absolute; top: -10px; right: -10px; font-size: 3rem;">😢</span>
            </div>
            
            <h3 class="fw-800 mb-2">Keranjang Kamu Kosong</h3>
            <p class="text-muted mb-4">
                Yuk, mulai belanja produk laundry favoritmu!<br>
                Kami punya banyak produk berkualitas dengan harga terbaik.
            </p>
            
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="produk.php" class="btn btn-primary btn-lg" style="border-radius: 12px;">
                    <i class="fas fa-store me-2"></i> Lihat Katalog
                </a>
                <a href="dashboard.php" class="btn btn-outline btn-lg" style="border-radius: 12px;">
                    <i class="fas fa-home me-2"></i> Ke Dashboard
                </a>
            </div>
            
            <!-- Rekomendasi Produk -->
            <div class="mt-5 text-start">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-star me-2" style="color: #F59E0B;"></i> Rekomendasi Untukmu
                </h5>
                <div class="row g-3">
                    <?php
                    // Ambil 4 produk random untuk rekomendasi
                    $stmt = $pdo->query("SELECT * FROM produk WHERE stok > 0 ORDER BY RAND() LIMIT 4");
                    $rekomendasi = $stmt->fetchAll();
                    foreach ($rekomendasi as $rek):
                    ?>
                    <div class="col-6 col-md-3">
                        <div class="product-card">
                            <div class="product-card-img-wrapper">
                                <?php if ($rek['gambar']): ?>
                                <img src="../assets/img/produk/<?php echo $rek['gambar']; ?>" alt="<?php echo $rek['nama_produk']; ?>" loading="lazy">
                                <?php else: ?>
                                <div class="product-card-img-placeholder"><i class="fas fa-box-open"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="product-card-body">
                                <div class="product-name"><?php echo $rek['nama_produk']; ?></div>
                                <div class="product-price-row">
                                    <span class="product-price-main">Rp <?php echo number_format($rek['harga'], 0, ',', '.'); ?></span>
                                </div>
                                <a href="detail_produk.php?id=<?php echo $rek['id']; ?>" class="btn btn-add-cart mt-2">
                                    <i class="fas fa-eye me-1"></i> Lihat
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ============================================
     JAVASCRIPT
     ============================================ -->
<script>
    /**
     * Update quantity di keranjang
     * @param {number} cartId - ID keranjang
     * @param {number} change - Perubahan jumlah (+1 atau -1)
     * @param {number} maxStok - Stok maksimal
     */
    function updateCartQty(cartId, change, maxStok) {
        const input = document.getElementById('qtyInput' + cartId);
        if (!input) return;
        
        let value = parseInt(input.value) + change;
        
        // Validasi
        if (value < 1) value = 1;
        if (value > maxStok) value = maxStok;
        
        // Update input value
        input.value = value;
        
        // Submit form
        const form = document.getElementById('updateForm' + cartId);
        if (form) {
            form.submit();
        }
    }
    
    // Highlight item dengan stok habis
    document.addEventListener('DOMContentLoaded', function() {
        const cartItems = document.querySelectorAll('.cart-item');
        cartItems.forEach(item => {
            const stockWarning = item.querySelector('.text-danger, .text-warning');
            if (stockWarning) {
                item.style.background = '#FFFBEB';
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>