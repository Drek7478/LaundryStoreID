<?php
require_once '../config/db.php';
include '../includes/header.php';
include '../includes/navbar_admin.php';

// Statistik
$stmt = $pdo->query("SELECT COUNT(*) as total FROM produk");
$total_produk = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM pesanan WHERE DATE(created_at) = CURDATE()");
$pesanan_hari_ini = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM pesanan WHERE status IN ('menunggu_pembayaran', 'menunggu_konfirmasi')");
$pesanan_pending = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COALESCE(SUM(total_harga), 0) as total FROM pesanan WHERE status = 'selesai' AND MONTH(created_at) = MONTH(CURDATE())");
$revenue_bulan_ini = $stmt->fetch()['total'];

// 5 pesanan terbaru
$stmt = $pdo->query("SELECT p.*, u.nama as nama_user FROM pesanan p JOIN users u ON p.id_user = u.id ORDER BY p.created_at DESC LIMIT 5");
$pesanan_terbaru = $stmt->fetchAll();
?>

<!-- ============================================ -->
<!-- KONTEN DASHBOARD ADMIN                      -->
<!-- ============================================ -->

<!-- Header -->
<div class="mb-4">
    <h2 class="fw-800 mb-1"><i class="fas fa-chart-pie me-2 text-primary"></i> Dashboard</h2>
    <p class="text-muted">Ringkasan bisnis laundry Anda hari ini</p>
</div>

<!-- Stat Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="admin-stat-card violet">
            <div class="stat-bg-glow"></div>
            <div class="stat-icon"><i class="fas fa-boxes"></i></div>
            <div class="stat-value"><?php echo $total_produk; ?></div>
            <div class="stat-label">Total Produk</div>
            <small style="opacity: 0.7;">Produk aktif</small>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="admin-stat-card cyan">
            <div class="stat-bg-glow"></div>
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-value"><?php echo $pesanan_hari_ini; ?></div>
            <div class="stat-label">Pesanan Hari Ini</div>
            <small style="opacity: 0.7;"><?php echo date('d F Y'); ?></small>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="admin-stat-card amber">
            <div class="stat-bg-glow"></div>
            <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="stat-value"><?php echo $pesanan_pending; ?></div>
            <div class="stat-label">Pending</div>
            <small style="opacity: 0.7;">Menunggu konfirmasi</small>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="admin-stat-card emerald">
            <div class="stat-bg-glow"></div>
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value">Rp <?php echo number_format($revenue_bulan_ini, 0, ',', '.'); ?></div>
            <div class="stat-label">Revenue Bulan Ini</div>
            <small style="opacity: 0.7;">Pesanan selesai</small>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="produk/tambah.php" class="text-decoration-none">
            <div class="card card-hover" style="border-radius: var(--radius-lg);">
                <div class="card-body text-center py-4">
                    <i class="fas fa-plus-circle" style="font-size: 2rem; color: var(--color-primary);"></i>
                    <h6 class="mt-2 mb-0">Tambah Produk Baru</h6>
                    <small class="text-muted">Tambahkan produk ke katalog</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="pesanan/index.php?status=menunggu_konfirmasi" class="text-decoration-none">
            <div class="card card-hover" style="border-radius: var(--radius-lg);">
                <div class="card-body text-center py-4">
                    <i class="fas fa-check-circle" style="font-size: 2rem; color: var(--color-success);"></i>
                    <h6 class="mt-2 mb-0">Konfirmasi Pembayaran</h6>
                    <small class="text-muted"><?php echo $pesanan_pending; ?> pesanan pending</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="../user/produk.php" target="_blank" class="text-decoration-none">
            <div class="card card-hover" style="border-radius: var(--radius-lg);">
                <div class="card-body text-center py-4">
                    <i class="fas fa-store" style="font-size: 2rem; color: var(--color-accent);"></i>
                    <h6 class="mt-2 mb-0">Lihat Website</h6>
                    <small class="text-muted">Buka halaman user</small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Recent Orders -->
<div class="card" style="border-radius: var(--radius-lg); overflow: hidden;">
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
        <h5 class="fw-bold mb-0"><i class="fas fa-shopping-cart me-2 text-primary"></i> Pesanan Terbaru</h5>
        <a href="pesanan/index.php" style="color: var(--color-primary); font-size: 14px; font-weight: 600;">Lihat Semua <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Kode Pesanan</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pesanan_terbaru) > 0): ?>
                    <?php foreach ($pesanan_terbaru as $p): 
                        $badge_class = ''; $icon = '';
                        switch ($p['status']) {
                            case 'menunggu_pembayaran': $badge_class = 'badge-pending'; $icon = 'fa-clock'; break;
                            case 'menunggu_konfirmasi': $badge_class = 'badge-confirming'; $icon = 'fa-search'; break;
                            case 'dikonfirmasi': $badge_class = 'badge-confirmed'; $icon = 'fa-check-circle'; break;
                            case 'selesai': $badge_class = 'badge-done'; $icon = 'fa-trophy'; break;
                            case 'dibatalkan': $badge_class = 'badge-cancelled'; $icon = 'fa-times-circle'; break;
                        }
                    ?>
                    <tr>
                        <td>
                            <span style="font-family: 'Courier New', monospace; font-size: 13px; background: #F5F3FF; padding: 4px 8px; border-radius: 6px;">
                                <?php echo $p['kode_pesanan']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm"><?php echo strtoupper(substr($p['nama_user'], 0, 1)); ?></div>
                                <span><?php echo $p['nama_user']; ?></span>
                            </div>
                        </td>
                        <td><strong>Rp <?php echo number_format($p['total_harga'], 0, ',', '.'); ?></strong></td>
                        <td>
                            <span class="badge badge-status <?php echo $badge_class; ?>">
                                <span class="status-dot"></span>
                                <i class="fas <?php echo $icon; ?> me-1"></i> 
                                <?php echo ucwords(str_replace('_', ' ', $p['status'])); ?>
                            </span>
                        </td>
                        <td><small><?php echo date('d/m/Y H:i', strtotime($p['created_at'])); ?></small></td>
                        <td>
                            <a href="pesanan/detail.php?id=<?php echo $p['id']; ?>" class="btn btn-sm" style="background: var(--color-primary-50); color: var(--color-primary); border-radius: 8px;">
                                <i class="fas fa-eye me-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox me-2"></i> Belum ada pesanan
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================ -->
<!-- PENUTUP DIV (WAJIB!)                         -->
<!-- ============================================ -->
</div> <!-- Tutup div.p-4 -->
</div> <!-- Tutup div.admin-main -->

<?php include '../includes/footer.php'; ?>