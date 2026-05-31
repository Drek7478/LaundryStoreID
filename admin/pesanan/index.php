<?php
require_once '../../config/db.php';
include '../../includes/header.php';
include '../../includes/navbar_admin.php';

// Filter status
$where = "";
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

if ($status_filter != '') {
    $where = "WHERE p.status = '$status_filter'";
}

$stmt = $pdo->query("SELECT p.*, u.nama as nama_user FROM pesanan p JOIN users u ON p.id_user = u.id $where ORDER BY p.created_at DESC");
$pesanan_list = $stmt->fetchAll();

// Hitung per status
$stmt = $pdo->query("SELECT status, COUNT(*) as total FROM pesanan GROUP BY status");
$status_counts = [];
while ($row = $stmt->fetch()) {
    $status_counts[$row['status']] = $row['total'];
}
$total_all = array_sum($status_counts);
?>

<!-- ============================================ -->
<!-- KONTEN MANAJEMEN PESANAN                    -->
<!-- ============================================ -->

<div class="mb-4">
    <h2 class="fw-800 mb-1"><i class="fas fa-shopping-cart me-2 text-primary"></i> Manajemen Pesanan</h2>
    <p class="text-muted">Kelola semua pesanan pelanggan</p>
</div>

<!-- Filter Status -->
<div class="d-flex flex-wrap gap-2 mb-4 filter-pills-wrapper">
    <a href="index.php" class="filter-pill <?php echo $status_filter == '' ? 'active' : ''; ?>">
        <i class="fas fa-list me-1"></i> Semua 
        <span class="badge bg-secondary ms-1"><?php echo $total_all; ?></span>
    </a>
    <a href="?status=menunggu_pembayaran" class="filter-pill <?php echo $status_filter == 'menunggu_pembayaran' ? 'active' : ''; ?>">
        <i class="fas fa-clock me-1"></i> Menunggu Bayar 
        <span class="badge bg-warning ms-1"><?php echo $status_counts['menunggu_pembayaran'] ?? 0; ?></span>
    </a>
    <a href="?status=menunggu_konfirmasi" class="filter-pill <?php echo $status_filter == 'menunggu_konfirmasi' ? 'active' : ''; ?>">
        <i class="fas fa-search me-1"></i> Menunggu Konfirmasi 
        <span class="badge bg-info ms-1"><?php echo $status_counts['menunggu_konfirmasi'] ?? 0; ?></span>
    </a>
    <a href="?status=dikonfirmasi" class="filter-pill <?php echo $status_filter == 'dikonfirmasi' ? 'active' : ''; ?>">
        <i class="fas fa-check-circle me-1"></i> Dikonfirmasi 
        <span class="badge bg-primary ms-1"><?php echo $status_counts['dikonfirmasi'] ?? 0; ?></span>
    </a>
    <a href="?status=selesai" class="filter-pill <?php echo $status_filter == 'selesai' ? 'active' : ''; ?>">
        <i class="fas fa-trophy me-1"></i> Selesai 
        <span class="badge bg-success ms-1"><?php echo $status_counts['selesai'] ?? 0; ?></span>
    </a>
    <a href="?status=dibatalkan" class="filter-pill <?php echo $status_filter == 'dibatalkan' ? 'active' : ''; ?>">
        <i class="fas fa-times-circle me-1"></i> Dibatalkan 
        <span class="badge bg-danger ms-1"><?php echo $status_counts['dibatalkan'] ?? 0; ?></span>
    </a>
</div>

<!-- Pesanan Table -->
<div class="card" style="border-radius: var(--radius-lg); overflow: hidden;">
    <?php if (count($pesanan_list) > 0): ?>
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Tanggal</th>
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
                    <td><small><?php echo $p['metode_pembayaran']; ?></small></td>
                    <td>
                        <span class="badge badge-status <?php echo $badge_class; ?>">
                            <span class="status-dot"></span>
                            <i class="fas <?php echo $icon; ?> me-1"></i> 
                            <?php echo ucwords(str_replace('_', ' ', $p['status'])); ?>
                        </span>
                    </td>
                    <td><small><?php echo date('d/m/Y H:i', strtotime($p['created_at'])); ?></small></td>
                    <td>
                        <a href="detail.php?id=<?php echo $p['id']; ?>" class="btn btn-sm" style="background: var(--color-primary-50); color: var(--color-primary); border-radius: 8px;">
                            <i class="fas fa-eye me-1"></i> Detail
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-inbox" style="font-size: 5rem; color: var(--color-text-muted);"></i>
        <h4 class="fw-bold mt-3">Tidak Ada Pesanan</h4>
        <p class="text-muted">Belum ada pesanan dengan filter ini</p>
        <a href="index.php" class="btn btn-outline mt-2" style="border-radius: 10px;"><i class="fas fa-redo me-1"></i> Reset Filter</a>
    </div>
    <?php endif; ?>
</div>

<!-- ============================================ -->
<!-- PENUTUP DIV (WAJIB!)                         -->
<!-- ============================================ -->
</div> <!-- Tutup div.p-4 -->
</div> <!-- Tutup div.admin-main -->

<?php include '../../includes/footer.php'; ?>