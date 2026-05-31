<?php
require_once '../../config/db.php';
include '../../includes/header.php';
include '../../includes/navbar_admin.php';

$id = (int)$_GET['id'];

// Ambil data pesanan
$stmt = $pdo->prepare("SELECT p.*, u.nama as nama_user, u.alamat as alamat_user, u.no_hp, u.email FROM pesanan p JOIN users u ON p.id_user = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    redirect('index.php');
}

// Ambil detail pesanan
$stmt = $pdo->prepare("SELECT dp.*, pr.nama_produk, pr.gambar, pr.satuan FROM detail_pesanan dp JOIN produk pr ON dp.id_produk = pr.id WHERE dp.id_pesanan = ?");
$stmt->execute([$id]);
$detail_list = $stmt->fetchAll();

// Status badge
$badge_class = ''; $icon = '';
switch ($pesanan['status']) {
    case 'menunggu_pembayaran': $badge_class = 'badge-pending'; $icon = 'fa-clock'; break;
    case 'menunggu_konfirmasi': $badge_class = 'badge-confirming'; $icon = 'fa-search'; break;
    case 'dikonfirmasi': $badge_class = 'badge-confirmed'; $icon = 'fa-check-circle'; break;
    case 'selesai': $badge_class = 'badge-done'; $icon = 'fa-trophy'; break;
    case 'dibatalkan': $badge_class = 'badge-cancelled'; $icon = 'fa-times-circle'; break;
}
?>

<!-- ============================================ -->
<!-- KONTEN DETAIL PESANAN                       -->
<!-- ============================================ -->

<div class="mb-4">
    <a href="index.php" class="btn btn-outline btn-sm mb-3" style="border-radius: 8px;">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Pesanan
    </a>
    <h2 class="fw-800 mb-1"><i class="fas fa-file-invoice me-2 text-primary"></i> Detail Pesanan</h2>
    <p class="text-muted">
        Kode: <span style="font-family: 'Courier New', monospace; background: #F5F3FF; padding: 4px 10px; border-radius: 6px;">
            <?php echo $pesanan['kode_pesanan']; ?>
        </span>
    </p>
</div>

<div class="row g-4">
    <!-- Detail Produk -->
    <div class="col-lg-8">
        <div class="card mb-4" style="border-radius: var(--radius-lg);">
            <div style="padding: 16px 24px; border-bottom: 1px solid var(--color-border); background: linear-gradient(135deg, #F5F3FF, #E0F2FE);">
                <h5 class="fw-bold mb-0"><i class="fas fa-boxes me-2"></i> Detail Produk</h5>
            </div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detail_list as $d): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <?php if ($d['gambar']): ?>
                                    <img src="../../assets/img/produk/<?php echo $d['gambar']; ?>" style="width: 48px; height: 48px; border-radius: 10px; object-fit: cover;">
                                    <?php else: ?>
                                    <div style="width: 48px; height: 48px; border-radius: 10px; background: linear-gradient(135deg, #F5F3FF, #CFFAFE); display: flex; align-items: center; justify-content: center; color: var(--color-primary);"><i class="fas fa-box-open"></i></div>
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
                            <td><strong style="font-size: 1.2rem; color: var(--color-primary);">Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Data Pembeli -->
        <div class="card mb-4" style="border-radius: var(--radius-lg);">
            <div style="padding: 16px 24px; border-bottom: 1px solid var(--color-border); background: linear-gradient(135deg, #F5F3FF, #E0F2FE);">
                <h5 class="fw-bold mb-0"><i class="fas fa-user me-2"></i> Data Pembeli</h5>
            </div>
            <div class="card-body">
                <p><strong>Nama:</strong> <?php echo $pesanan['nama_user']; ?></p>
                <p><strong>Email:</strong> <?php echo $pesanan['email']; ?></p>
                <p><strong>No. HP:</strong> <?php echo $pesanan['no_hp']; ?></p>
                <p><strong>Alamat:</strong> <?php echo $pesanan['alamat_user']; ?></p>
                <p><strong>Metode Bayar:</strong> <?php echo $pesanan['metode_pembayaran']; ?></p>
                <?php if ($pesanan['catatan']): ?>
                <p><strong>Catatan:</strong> <?php echo $pesanan['catatan']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Status & Bukti -->
        <div class="card mb-4" style="border-radius: var(--radius-lg);">
            <div style="padding: 16px 24px; border-bottom: 1px solid var(--color-border); background: linear-gradient(135deg, #F5F3FF, #E0F2FE);">
                <h5 class="fw-bold mb-0"><i class="fas fa-chart-line me-2"></i> Status Pesanan</h5>
            </div>
            <div class="card-body">
                <span class="badge badge-status <?php echo $badge_class; ?>" style="font-size: 14px; padding: 8px 16px;">
                    <span class="status-dot"></span>
                    <i class="fas <?php echo $icon; ?> me-1"></i> 
                    <?php echo ucwords(str_replace('_', ' ', $pesanan['status'])); ?>
                </span>
                
                <?php if ($pesanan['bukti_pembayaran']): ?>
                <div class="mt-3">
                    <strong>Bukti Pembayaran:</strong><br>
                    <img src="../../uploads/bukti/<?php echo $pesanan['bukti_pembayaran']; ?>" class="img-fluid mt-2" style="border-radius: 10px; cursor: pointer;" onclick="enlargeImage('../../uploads/bukti/<?php echo $pesanan['bukti_pembayaran']; ?>')">
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Konfirmasi -->
        <div class="card" style="border-radius: var(--radius-lg);">
            <div style="padding: 16px 24px; border-bottom: 1px solid var(--color-border); background: linear-gradient(135deg, #F5F3FF, #E0F2FE);">
                <h5 class="fw-bold mb-0"><i class="fas fa-check-circle me-2"></i> Konfirmasi</h5>
            </div>
            <div class="card-body">
                <?php if ($pesanan['status'] == 'menunggu_konfirmasi'): ?>
                <a href="konfirmasi.php?id=<?php echo $pesanan['id']; ?>&status=dikonfirmasi" class="btn w-100 mb-2" style="background: var(--gradient-primary); color: white; border-radius: 10px; font-weight: 600;">
                    <i class="fas fa-check-circle me-2"></i> Konfirmasi Pembayaran
                </a>
                <?php endif; ?>
                
                <?php if (in_array($pesanan['status'], ['dikonfirmasi'])): ?>
                <a href="konfirmasi.php?id=<?php echo $pesanan['id']; ?>&status=selesai" class="btn w-100 mb-2" style="background: var(--gradient-success); color: white; border-radius: 10px; font-weight: 600;">
                    <i class="fas fa-trophy me-2"></i> Tandai Selesai
                </a>
                <?php endif; ?>
                
                <?php if (!in_array($pesanan['status'], ['selesai', 'dibatalkan'])): ?>
                <a href="konfirmasi.php?id=<?php echo $pesanan['id']; ?>&status=dibatalkan" class="btn w-100" style="background: #FEF2F2; color: var(--color-danger); border: 1px solid #FCA5A5; border-radius: 10px; font-weight: 600;" onclick="return confirm('Batalkan pesanan ini?')">
                    <i class="fas fa-times-circle me-2"></i> Batalkan Pesanan
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- PENUTUP DIV (WAJIB!)                         -->
<!-- ============================================ -->
</div> <!-- Tutup div.p-4 -->
</div> <!-- Tutup div.admin-main -->

<?php include '../../includes/footer.php'; ?>