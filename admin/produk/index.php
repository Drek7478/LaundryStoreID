<?php
require_once '../../config/db.php';
include '../../includes/header.php';
include '../../includes/navbar_admin.php';

// Ambil semua produk
$stmt = $pdo->query("SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.id_kategori = k.id ORDER BY p.created_at DESC");
$produk_list = $stmt->fetchAll();

$total_produk = count($produk_list);
$total_stok = array_sum(array_column($produk_list, 'stok'));
?>

<!-- ============================================ -->
<!-- KONTEN MANAJEMEN PRODUK                     -->
<!-- ============================================ -->

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-800 mb-1"><i class="fas fa-boxes me-2 text-primary"></i> Manajemen Produk</h2>
        <p class="text-muted mb-0">
            <i class="fas fa-cubes me-1"></i> <?php echo $total_produk; ?> produk · 
            <i class="fas fa-warehouse me-1"></i> Total stok: <?php echo $total_stok; ?>
        </p>
    </div>
    <a href="tambah.php" class="btn btn-primary" style="border-radius: 10px;">
        <i class="fas fa-plus-circle me-1"></i> Tambah Produk
    </a>
</div>

<!-- Products Table -->
<div class="card" style="border-radius: var(--radius-lg); overflow: hidden;">
    <?php if (count($produk_list) > 0): ?>
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Satuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produk_list as $produk): ?>
                <tr>
                    <td>
                        <?php if ($produk['gambar']): ?>
                        <img src="../../assets/img/produk/<?php echo $produk['gambar']; ?>" style="width: 48px; height: 48px; border-radius: 10px; object-fit: cover; border: 2px solid var(--color-border);">
                        <?php else: ?>
                        <div style="width: 48px; height: 48px; border-radius: 10px; background: linear-gradient(135deg, #F5F3FF, #CFFAFE); display: flex; align-items: center; justify-content: center; color: var(--color-primary);"><i class="fas fa-box-open"></i></div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo $produk['nama_produk']; ?></strong></td>
                    <td>
                        <span style="background: var(--color-accent-light); color: var(--color-accent-dark); padding: 3px 10px; border-radius: 100px; font-size: 12px; font-weight: 600;">
                            <?php echo $produk['nama_kategori'] ?? 'Uncategorized'; ?>
                        </span>
                    </td>
                    <td><span style="font-weight: 700; color: var(--color-primary);">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></span></td>
                    <td>
                        <?php if ($produk['stok'] > 20): ?>
                        <span style="color: var(--color-success); font-weight: 600;"><?php echo $produk['stok']; ?></span>
                        <?php elseif ($produk['stok'] >= 5): ?>
                        <span style="color: var(--color-warning); font-weight: 600;"><?php echo $produk['stok']; ?></span>
                        <?php else: ?>
                        <span style="color: var(--color-danger); font-weight: 600;"><?php echo $produk['stok']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $produk['satuan']; ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="edit.php?id=<?php echo $produk['id']; ?>" class="btn btn-sm" style="background: #EFF6FF; color: #3B82F6; border-radius: 8px;" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="hapus.php?id=<?php echo $produk['id']; ?>" class="btn btn-sm" style="background: #FEF2F2; color: #EF4444; border-radius: 8px;" onclick="return confirmDelete('Hapus produk ini?')" title="Hapus"><i class="fas fa-trash-alt"></i></a>
                            <button class="btn btn-sm" style="background: #F0FDF4; color: #10B981; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#stokModal<?php echo $produk['id']; ?>" title="Update Stok"><i class="fas fa-boxes"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-box-open" style="font-size: 5rem; color: var(--color-text-muted);"></i>
        <h4 class="fw-bold mt-3">Belum Ada Produk</h4>
        <p class="text-muted">Tambahkan produk pertama Anda sekarang</p>
        <a href="tambah.php" class="btn btn-primary mt-2" style="border-radius: 10px;"><i class="fas fa-plus-circle me-1"></i> Tambah Produk</a>
    </div>
    <?php endif; ?>
</div>

<!-- ============================================ -->
<!-- PENUTUP DIV (WAJIB!)                         -->
<!-- ============================================ -->
</div> <!-- Tutup div.p-4 -->
</div> <!-- Tutup div.admin-main -->

<?php include '../../includes/footer.php'; ?>