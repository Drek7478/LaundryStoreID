<?php
/**
 * LaundryStoreID - Riwayat Transaksi
 * 
 * Halaman riwayat pesanan user dengan filter status dan detail
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

$id_user = $_SESSION['user_id'];

// ============================================
// FILTER STATUS
// ============================================
$where = "";
$params = [$id_user];
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$allowed_statuses = ['menunggu_pembayaran', 'menunggu_konfirmasi', 'dikonfirmasi', 'selesai', 'dibatalkan'];

if ($status_filter != '' && in_array($status_filter, $allowed_statuses)) {
    $where = " AND p.status = ?";
    $params[] = $status_filter;
}

// ============================================
// AMBIL DATA PESANAN
// ============================================
$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM detail_pesanan WHERE id_pesanan = p.id) as total_item
    FROM pesanan p 
    WHERE p.id_user = ? $where 
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$pesanan_list = $stmt->fetchAll();

// ============================================
// HITUNG JUMLAH PER STATUS (UNTUK BADGE)
// ============================================
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) as total 
    FROM pesanan 
    WHERE id_user = ? 
    GROUP BY status
");
$stmt->execute([$id_user]);
$status_counts = [];
while ($row = $stmt->fetch()) {
    $status_counts[$row['status']] = $row['total'];
}
$total_all = array_sum($status_counts);

// ============================================
// AMBIL DETAIL PESANAN (Jika ada parameter detail)
// ============================================
$detail_pesanan = null;
$detail_items = [];

if (isset($_GET['detail']) && (int)$_GET['detail'] > 0) {
    $detail_id = (int)$_GET['detail'];
    
    $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id = ? AND id_user = ?");
    $stmt->execute([$detail_id, $id_user]);
    $detail_pesanan = $stmt->fetch();
    
    if ($detail_pesanan) {
        $stmt = $pdo->prepare("
            SELECT dp.*, p.nama_produk, p.gambar, p.satuan 
            FROM detail_pesanan dp 
            JOIN produk p ON dp.id_produk = p.id 
            WHERE dp.id_pesanan = ?
        ");
        $stmt->execute([$detail_id]);
        $detail_items = $stmt->fetchAll();
    }
}
?>

<!-- ============================================
     HEADER
     ============================================ -->
<div style="background: linear-gradient(135deg, #F5F3FF, #E0F2FE); padding: 32px 0;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-2">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home me-1"></i> Home</a></li>
                <li class="breadcrumb-item active">Riwayat Transaksi</li>
            </ol>
        </nav>
        <h2 class="fw-800 mb-1">
            <i class="fas fa-history me-2" style="color: var(--color-primary);"></i>
            Riwayat Transaksi
        </h2>
        <p class="text-muted mb-0">
            Semua pesanan dan status pembayaran Anda
            <?php if ($total_all > 0): ?>
            · <strong><?php echo $total_all; ?></strong> total pesanan
            <?php endif; ?>
        </p>
    </div>
</div>

<div class="container py-4">
    
    <!-- ============================================
         DETAIL PESANAN (MODAL INLINE)
         ============================================ -->
    <?php if ($detail_pesanan): ?>
    <div class="card mb-4 border-primary" style="border-radius: var(--radius-lg); border-width: 2px;">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #F5F3FF, #E0F2FE);">
            <h5 class="fw-bold mb-0">
                <i class="fas fa-file-invoice me-2"></i> Detail Pesanan
            </h5>
            <a href="riwayat.php<?php echo $status_filter != '' ? '?status=' . $status_filter : ''; ?>" class="btn btn-sm" style="background: white; border-radius: 8px;">
                <i class="fas fa-times me-1"></i> Tutup
            </a>
        </div>
        <div class="card-body">
            <!-- Info Pesanan -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="p-3" style="background: #F8FAFC; border-radius: 10px;">
                        <small class="text-muted d-block">Kode Pesanan</small>
                        <strong style="font-family: 'Courier New', monospace;"><?php echo $detail_pesanan['kode_pesanan']; ?></strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3" style="background: #F8FAFC; border-radius: 10px;">
                        <small class="text-muted d-block">Tanggal Pesanan</small>
                        <strong><?php echo date('d M Y H:i', strtotime($detail_pesanan['created_at'])); ?></strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3" style="background: #F8FAFC; border-radius: 10px;">
                        <small class="text-muted d-block">Status</small>
                        <?php
                        $badge_class = ''; $icon = '';
                        switch ($detail_pesanan['status']) {
                            case 'menunggu_pembayaran': $badge_class = 'badge-pending'; $icon = 'fa-clock'; break;
                            case 'menunggu_konfirmasi': $badge_class = 'badge-confirming'; $icon = 'fa-search'; break;
                            case 'dikonfirmasi': $badge_class = 'badge-confirmed'; $icon = 'fa-check-circle'; break;
                            case 'selesai': $badge_class = 'badge-done'; $icon = 'fa-trophy'; break;
                            case 'dibatalkan': $badge_class = 'badge-cancelled'; $icon = 'fa-times-circle'; break;
                        }
                        ?>
                        <span class="badge badge-status <?php echo $badge_class; ?>">
                            <span class="status-dot"></span>
                            <i class="fas <?php echo $icon; ?> me-1"></i>
                            <?php echo ucwords(str_replace('_', ' ', $detail_pesanan['status'])); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Tabel Detail Item -->
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detail_items as $d): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <?php if ($d['gambar']): ?>
                                    <img src="../assets/img/produk/<?php echo $d['gambar']; ?>" 
                                         style="width: 48px; height: 48px; border-radius: 10px; object-fit: cover; border: 1px solid var(--color-border);">
                                    <?php else: ?>
                                    <div style="width: 48px; height: 48px; border-radius: 10px; background: linear-gradient(135deg, #F5F3FF, #CFFAFE); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo $d['nama_produk']; ?></strong>
                                        <br><small class="text-muted"><?php echo $d['satuan']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>Rp <?php echo number_format($d['harga_satuan'], 0, ',', '.'); ?></td>
                            <td><?php echo $d['jumlah']; ?></td>
                            <td><strong>Rp <?php echo number_format($d['subtotal'], 0, ',', '.'); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: #F8FAFC;">
                            <td colspan="3" class="text-end"><strong>Total</strong></td>
                            <td><strong style="font-size: 1.1rem; color: var(--color-primary);">Rp <?php echo number_format($detail_pesanan['total_harga'], 0, ',', '.'); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Info Tambahan -->
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <div class="p-3" style="background: #F8FAFC; border-radius: 10px;">
                        <small class="text-muted d-block">Metode Pembayaran</small>
                        <strong><?php echo $detail_pesanan['metode_pembayaran']; ?></strong>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3" style="background: #F8FAFC; border-radius: 10px;">
                        <small class="text-muted d-block">Catatan</small>
                        <strong><?php echo $detail_pesanan['catatan'] ?: '-'; ?></strong>
                    </div>
                </div>
            </div>
            
            <!-- Bukti Pembayaran -->
            <?php if ($detail_pesanan['bukti_pembayaran']): ?>
            <div class="mt-3">
                <small class="text-muted d-block mb-2">Bukti Pembayaran:</small>
                <img src="../uploads/bukti/<?php echo $detail_pesanan['bukti_pembayaran']; ?>" 
                     style="max-width: 300px; border-radius: 10px; cursor: pointer; border: 2px solid var(--color-border);"
                     onclick="enlargeImage('../uploads/bukti/<?php echo $detail_pesanan['bukti_pembayaran']; ?>')"
                     alt="Bukti Pembayaran">
            </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="mt-3 d-flex gap-2">
                <?php if ($detail_pesanan['status'] == 'menunggu_pembayaran'): ?>
                <a href="upload_bukti.php" class="btn btn-primary" style="border-radius: 10px;">
                    <i class="fas fa-upload me-1"></i> Upload Bukti
                </a>
                <?php endif; ?>
                <a href="riwayat.php<?php echo $status_filter != '' ? '?status=' . $status_filter : ''; ?>" class="btn btn-outline" style="border-radius: 10px;">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- ============================================
         FILTER STATUS (PILLS)
         ============================================ -->
    <div class="d-flex flex-wrap gap-2 mb-4 filter-pills-wrapper" style="overflow-x: auto; padding-bottom: 4px;">
        <a href="riwayat.php" class="filter-pill <?php echo $status_filter == '' ? 'active' : ''; ?>">
            <i class="fas fa-list me-1"></i> Semua
            <span class="badge bg-secondary ms-1" style="font-size: 10px;"><?php echo $total_all; ?></span>
        </a>
        <a href="?status=menunggu_pembayaran" class="filter-pill <?php echo $status_filter == 'menunggu_pembayaran' ? 'active' : ''; ?>">
            <i class="fas fa-clock me-1"></i> Menunggu Bayar
            <span class="badge bg-warning ms-1" style="font-size: 10px;"><?php echo $status_counts['menunggu_pembayaran'] ?? 0; ?></span>
        </a>
        <a href="?status=menunggu_konfirmasi" class="filter-pill <?php echo $status_filter == 'menunggu_konfirmasi' ? 'active' : ''; ?>">
            <i class="fas fa-search me-1"></i> Menunggu Konfirmasi
            <span class="badge bg-info ms-1" style="font-size: 10px;"><?php echo $status_counts['menunggu_konfirmasi'] ?? 0; ?></span>
        </a>
        <a href="?status=dikonfirmasi" class="filter-pill <?php echo $status_filter == 'dikonfirmasi' ? 'active' : ''; ?>">
            <i class="fas fa-check-circle me-1"></i> Dikonfirmasi
            <span class="badge bg-primary ms-1" style="font-size: 10px;"><?php echo $status_counts['dikonfirmasi'] ?? 0; ?></span>
        </a>
        <a href="?status=selesai" class="filter-pill <?php echo $status_filter == 'selesai' ? 'active' : ''; ?>">
            <i class="fas fa-trophy me-1"></i> Selesai
            <span class="badge bg-success ms-1" style="font-size: 10px;"><?php echo $status_counts['selesai'] ?? 0; ?></span>
        </a>
        <a href="?status=dibatalkan" class="filter-pill <?php echo $status_filter == 'dibatalkan' ? 'active' : ''; ?>">
            <i class="fas fa-times-circle me-1"></i> Dibatalkan
            <span class="badge bg-danger ms-1" style="font-size: 10px;"><?php echo $status_counts['dibatalkan'] ?? 0; ?></span>
        </a>
    </div>
    
    <!-- ============================================
         TABEL PESANAN (DESKTOP)
         ============================================ -->
    <?php if (count($pesanan_list) > 0): ?>
    <div class="card d-none d-md-block" style="border-radius: var(--radius-lg); overflow: hidden;">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Kode Pesanan</th>
                        <th>Tanggal</th>
                        <th>Item</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pesanan_list as $p): 
                        $badge_class = ''; $icon = '';
                        switch ($p['status']) {
                            case 'menunggu_pembayaran': $badge_class = 'badge-pending'; $icon = 'fa-clock'; break;
                            case 'menunggu_konfirmasi': $badge_class = 'badge-confirming'; $icon = 'fa-search'; break;
                            case 'dikonfirmasi': $badge_class = 'badge-confirmed'; $icon = 'fa-check-circle'; break;
                            case 'selesai': $badge_class = 'badge-done'; $icon = 'fa-trophy'; break;
                            case 'dibatalkan': $badge_class = 'badge-cancelled'; $icon = 'fa-times-circle'; break;
                        }
                    ?>
                    <tr style="<?php echo in_array($p['status'], ['menunggu_pembayaran', 'menunggu_konfirmasi']) ? 'background: #FFFBEB;' : ''; ?>">
                        <!-- Kode Pesanan -->
                        <td>
                            <span style="font-family: 'Courier New', monospace; background: #F5F3FF; padding: 4px 10px; border-radius: 6px; font-size: 13px; cursor: pointer;" 
                                  onclick="copyToClipboard('<?php echo $p['kode_pesanan']; ?>')"
                                  title="Klik untuk menyalin kode">
                                <i class="fas fa-copy me-1"></i> <?php echo $p['kode_pesanan']; ?>
                            </span>
                        </td>
                        
                        <!-- Tanggal -->
                        <td>
                            <small>
                                <i class="fas fa-calendar me-1"></i> 
                                <?php echo date('d/m/Y', strtotime($p['created_at'])); ?>
                            </small>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo date('H:i', strtotime($p['created_at'])); ?>
                            </small>
                        </td>
                        
                        <!-- Jumlah Item -->
                        <td>
                            <span class="badge" style="background: #F1F5F9; color: var(--color-text);">
                                <?php echo $p['total_item']; ?> item
                            </span>
                        </td>
                        
                        <!-- Total -->
                        <td>
                            <strong>Rp <?php echo number_format($p['total_harga'], 0, ',', '.'); ?></strong>
                        </td>
                        
                        <!-- Metode -->
                        <td>
                            <small><i class="fas fa-university me-1"></i> <?php echo $p['metode_pembayaran']; ?></small>
                        </td>
                        
                        <!-- Status -->
                        <td>
                            <span class="badge badge-status <?php echo $badge_class; ?>">
                                <span class="status-dot"></span>
                                <i class="fas <?php echo $icon; ?> me-1"></i>
                                <?php echo ucwords(str_replace('_', ' ', $p['status'])); ?>
                            </span>
                        </td>
                        
                        <!-- Aksi -->
                        <td>
                            <div class="d-flex gap-1">
                                <a href="?detail=<?php echo $p['id']; ?><?php echo $status_filter != '' ? '&status=' . $status_filter : ''; ?>" 
                                   class="btn btn-sm" 
                                   style="background: var(--color-primary-50); color: var(--color-primary); border-radius: 8px;"
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($p['status'] == 'menunggu_pembayaran'): ?>
                                <a href="upload_bukti.php" 
                                   class="btn btn-sm" 
                                   style="background: #FEF3C7; color: #92400E; border-radius: 8px;"
                                   title="Upload Bukti">
                                    <i class="fas fa-upload"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- ============================================
         CARD PESANAN (MOBILE)
         ============================================ -->
    <div class="d-md-none">
        <?php foreach ($pesanan_list as $p): 
            $badge_class = ''; $icon = '';
            switch ($p['status']) {
                case 'menunggu_pembayaran': $badge_class = 'badge-pending'; $icon = 'fa-clock'; break;
                case 'menunggu_konfirmasi': $badge_class = 'badge-confirming'; $icon = 'fa-search'; break;
                case 'dikonfirmasi': $badge_class = 'badge-confirmed'; $icon = 'fa-check-circle'; break;
                case 'selesai': $badge_class = 'badge-done'; $icon = 'fa-trophy'; break;
                case 'dibatalkan': $badge_class = 'badge-cancelled'; $icon = 'fa-times-circle'; break;
            }
        ?>
        <div class="card mb-3" style="border-radius: var(--radius-lg); <?php echo in_array($p['status'], ['menunggu_pembayaran', 'menunggu_konfirmasi']) ? 'background: #FFFBEB;' : ''; ?>">
            <div class="card-body">
                <!-- Header Card -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span style="font-family: 'Courier New', monospace; background: #F5F3FF; padding: 3px 10px; border-radius: 6px; font-size: 12px;">
                            <?php echo $p['kode_pesanan']; ?>
                        </span>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i> <?php echo date('d M Y', strtotime($p['created_at'])); ?>
                        </small>
                    </div>
                    <span class="badge badge-status <?php echo $badge_class; ?>" style="font-size: 11px;">
                        <span class="status-dot"></span>
                        <i class="fas <?php echo $icon; ?> ms-1"></i>
                    </span>
                </div>
                
                <!-- Info -->
                <div class="row g-2 small">
                    <div class="col-6">
                        <span class="text-muted">Total:</span>
                        <strong class="d-block">Rp <?php echo number_format($p['total_harga'], 0, ',', '.'); ?></strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">Item:</span>
                        <strong class="d-block"><?php echo $p['total_item']; ?> item</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">Metode:</span>
                        <span class="d-block"><?php echo $p['metode_pembayaran']; ?></span>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="d-flex gap-2 mt-3">
                    <a href="?detail=<?php echo $p['id']; ?><?php echo $status_filter != '' ? '&status=' . $status_filter : ''; ?>" 
                       class="btn btn-sm flex-grow-1" 
                       style="background: var(--color-primary-50); color: var(--color-primary); border-radius: 8px;">
                        <i class="fas fa-eye me-1"></i> Detail
                    </a>
                    <?php if ($p['status'] == 'menunggu_pembayaran'): ?>
                    <a href="upload_bukti.php" 
                       class="btn btn-sm" 
                       style="background: #FEF3C7; color: #92400E; border-radius: 8px;">
                        <i class="fas fa-upload me-1"></i> Bayar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- ============================================
         PAGINATION (Jika diperlukan)
         ============================================ -->
    <?php
    /*
    $total_pesanan = count($pesanan_list);
    $per_page = 10;
    $total_pages = ceil($total_pesanan / $per_page);
    $current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    
    if ($total_pages > 1):
    ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo $status_filter != '' ? '&status=' . $status_filter : ''; ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $status_filter != '' ? '&status=' . $status_filter : ''; ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo $status_filter != '' ? '&status=' . $status_filter : ''; ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; 
    */
    ?>
    
    <?php else: ?>
    <!-- ============================================
         EMPTY STATE
         ============================================ -->
    <div class="card" style="border-radius: var(--radius-lg);">
        <div class="card-body text-center py-5">
            <?php if ($status_filter != ''): ?>
            <!-- Filter kosong -->
            <i class="fas fa-search" style="font-size: 5rem; color: var(--color-text-muted); opacity: 0.5;"></i>
            <h4 class="fw-bold mt-3">Tidak Ada Pesanan</h4>
            <p class="text-muted mb-3">
                Tidak ada pesanan dengan status "<strong><?php echo ucwords(str_replace('_', ' ', $status_filter)); ?></strong>"
            </p>
            <a href="riwayat.php" class="btn btn-primary" style="border-radius: 10px;">
                <i class="fas fa-redo me-1"></i> Tampilkan Semua
            </a>
            <?php else: ?>
            <!-- Belum ada transaksi -->
            <i class="fas fa-clipboard-list" style="font-size: 5rem; color: var(--color-text-muted); opacity: 0.5;"></i>
            <h4 class="fw-bold mt-3">Belum Ada Transaksi</h4>
            <p class="text-muted mb-3">Anda belum melakukan pemesanan. Yuk, mulai belanja sekarang!</p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="produk.php" class="btn btn-primary" style="border-radius: 10px;">
                    <i class="fas fa-store me-1"></i> Mulai Belanja
                </a>
                <a href="dashboard.php" class="btn btn-outline" style="border-radius: 10px;">
                    <i class="fas fa-home me-1"></i> Dashboard
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ============================================
     STYLE TAMBAHAN
     ============================================ -->
<style>
    /* Sticky filter pills di mobile */
    @media (max-width: 767.98px) {
        .filter-pills-wrapper {
            position: sticky;
            top: 56px;
            z-index: 10;
            background: var(--color-bg);
            padding: 10px 0;
            margin: -10px 0 10px 0;
        }
    }
    
    /* Animasi hover pada row tabel */
    .table-custom tbody tr {
        transition: all 0.2s ease;
    }
    
    .table-custom tbody tr:hover {
        background: #FAFAFE !important;
    }
    
    /* Kode pesanan hover */
    .table-custom td span[style*="cursor: pointer"]:hover {
        background: #EDE9FE !important;
    }
</style>

<?php include '../includes/footer.php'; ?>