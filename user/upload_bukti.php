<?php
/**
 * LaundryStoreID - Upload Bukti Pembayaran
 * 
 * Halaman untuk upload bukti pembayaran setelah checkout
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
// HANDLE UPLOAD BUKTI PEMBAYARAN
// ============================================
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pesanan'])) {
    $id_pesanan = (int)$_POST['id_pesanan'];
    
    // Cek kepemilikan pesanan
    $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id = ? AND id_user = ? AND status = 'menunggu_pembayaran'");
    $stmt->execute([$id_pesanan, $id_user]);
    $pesanan = $stmt->fetch();
    
    if (!$pesanan) {
        $error = 'Pesanan tidak ditemukan atau tidak dalam status menunggu pembayaran!';
    } elseif (!isset($_FILES['bukti_pembayaran']) || $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Pilih file bukti pembayaran terlebih dahulu!';
    } elseif ($_FILES['bukti_pembayaran']['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas server!',
            UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas form!',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian!',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan!',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk!',
        ];
        $error = $upload_errors[$_FILES['bukti_pembayaran']['error']] ?? 'Error upload tidak diketahui!';
    } else {
        $file = $_FILES['bukti_pembayaran'];
        
        // Validasi ekstensi
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            $error = 'Format file tidak diizinkan! Hanya JPG, JPEG, PNG, dan GIF.';
        }
        // Validasi ukuran (maksimal 2MB)
        elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = 'Ukuran file maksimal 2MB! Ukuran file Anda: ' . round($file['size'] / 1024 / 1024, 2) . 'MB';
        }
        // Validasi tipe MIME
        else {
            $allowed_mimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime_type, $allowed_mimes)) {
                $error = 'Tipe file tidak valid! Hanya file gambar yang diizinkan.';
            } else {
                // Generate nama file unik
                $new_filename = 'bukti_' . $id_pesanan . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_extension;
                $upload_dir = '../uploads/bukti/';
                
                // Buat folder jika belum ada
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                    
                    // Buat .htaccess untuk keamanan
                    $htaccess = $upload_dir . '.htaccess';
                    if (!file_exists($htaccess)) {
                        file_put_contents($htaccess, "Deny from all\n<FilesMatch \"\.(jpg|jpeg|png|gif)$\">\n    Allow from all\n</FilesMatch>");
                    }
                    
                    // Buat index.html untuk mencegah listing
                    $index_file = $upload_dir . 'index.html';
                    if (!file_exists($index_file)) {
                        file_put_contents($index_file, '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>Forbidden</h1></body></html>');
                    }
                }
                
                $upload_path = $upload_dir . $new_filename;
                
                // Upload file
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    try {
                        // Update database
                        $stmt = $pdo->prepare("UPDATE pesanan SET bukti_pembayaran = ?, status = 'menunggu_konfirmasi', updated_at = NOW() WHERE id = ? AND id_user = ?");
                        $stmt->execute([$new_filename, $id_pesanan, $id_user]);
                        
                        $success = 'Bukti pembayaran berhasil diupload! 🎉 Pesanan Anda sedang menunggu konfirmasi dari admin.';
                        
                        // Hapus session pesanan baru jika ini pesanan yang sama
                        if (isset($_SESSION['id_pesanan_baru']) && $_SESSION['id_pesanan_baru'] == $id_pesanan) {
                            unset($_SESSION['kode_pesanan_baru']);
                            unset($_SESSION['id_pesanan_baru']);
                            unset($_SESSION['total_pesanan_baru']);
                            unset($_SESSION['metode_pembayaran_baru']);
                        }
                    } catch (PDOException $e) {
                        // Hapus file jika update database gagal
                        if (file_exists($upload_path)) {
                            unlink($upload_path);
                        }
                        $error = 'Gagal menyimpan data pembayaran: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Gagal mengupload file! Silakan coba lagi.';
                }
            }
        }
    }
}

// ============================================
// AMBIL DATA PESANAN
// ============================================

// Pesanan yang perlu upload bukti (menunggu_pembayaran)
$stmt = $pdo->prepare("
    SELECT * FROM pesanan 
    WHERE id_user = ? AND status = 'menunggu_pembayaran' 
    ORDER BY created_at DESC
");
$stmt->execute([$id_user]);
$pesanan_pending = $stmt->fetchAll();

// Pesanan yang sudah upload bukti (menunggu_konfirmasi)
$stmt = $pdo->prepare("
    SELECT * FROM pesanan 
    WHERE id_user = ? AND status = 'menunggu_konfirmasi' 
    ORDER BY updated_at DESC
    LIMIT 5
");
$stmt->execute([$id_user]);
$pesanan_confirming = $stmt->fetchAll();

// Pesanan yang sudah dikonfirmasi/selesai (info saja)
$stmt = $pdo->prepare("
    SELECT * FROM pesanan 
    WHERE id_user = ? AND status IN ('dikonfirmasi', 'selesai') 
    ORDER BY updated_at DESC 
    LIMIT 3
");
$stmt->execute([$id_user]);
$pesanan_done = $stmt->fetchAll();

// Cek apakah ada pesanan baru dari session (setelah checkout)
$show_new_order = isset($_SESSION['kode_pesanan_baru']);
?>

<!-- ============================================
     HEADER
     ============================================ -->
<div style="background: linear-gradient(135deg, #F5F3FF, #E0F2FE); padding: 32px 0;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-2">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home me-1"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="riwayat.php"><i class="fas fa-history me-1"></i> Riwayat</a></li>
                <li class="breadcrumb-item active">Upload Bukti Pembayaran</li>
            </ol>
        </nav>
        <h2 class="fw-800 mb-1">
            <i class="fas fa-upload me-2" style="color: var(--color-primary);"></i>
            Upload Bukti Pembayaran
        </h2>
        <p class="text-muted mb-0">Upload bukti transfer untuk menyelesaikan pesanan Anda</p>
    </div>
</div>

<div class="container py-4">
    
    <!-- ============================================
         ALERT SUKSES
         ============================================ -->
    <?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-start gap-3 mb-4" style="border-radius: 12px; padding: 20px;">
        <i class="fas fa-check-circle" style="font-size: 2rem; color: #10B981;"></i>
        <div>
            <h5 class="fw-bold mb-1">Berhasil!</h5>
            <p class="mb-2"><?php echo $success; ?></p>
            <a href="riwayat.php" class="btn btn-sm" style="background: #10B981; color: white; border-radius: 8px;">
                <i class="fas fa-history me-1"></i> Lihat Riwayat Pesanan
            </a>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- ============================================
         ALERT ERROR
         ============================================ -->
    <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-start gap-3 mb-4" style="border-radius: 12px;">
        <i class="fas fa-times-circle mt-1"></i>
        <span><?php echo $error; ?></span>
    </div>
    <?php endif; ?>
    
    <!-- ============================================
         PESANAN BARU (SETELAH CHECKOUT)
         ============================================ -->
    <?php if ($show_new_order): ?>
    <div class="card mb-4 border-success" style="border-radius: var(--radius-lg); border-width: 2px;">
        <div class="card-header" style="background: linear-gradient(135deg, #D1FAE5, #A7F3D0); border-bottom: 1px solid #6EE7B7;">
            <h5 class="fw-bold mb-0 text-success">
                <i class="fas fa-check-circle me-2"></i> Pesanan Berhasil Dibuat! 🎉
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3" style="background: #F8FAFC; border-radius: 10px;">
                        <small class="text-muted d-block mb-1">Kode Pesanan</small>
                        <strong style="font-size: 1.2rem; font-family: 'Courier New', monospace;">
                            <?php echo $_SESSION['kode_pesanan_baru']; ?>
                        </strong>
                        <button class="btn btn-sm ms-2" style="background: #EFF6FF; color: #3B82F6; border-radius: 6px; font-size: 11px;" 
                                onclick="copyToClipboard('<?php echo $_SESSION['kode_pesanan_baru']; ?>')">
                            <i class="fas fa-copy me-1"></i> Salin
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3" style="background: #F8FAFC; border-radius: 10px;">
                        <small class="text-muted d-block mb-1">Total Pembayaran</small>
                        <strong style="color: var(--color-primary);">
                            Rp <?php echo number_format($_SESSION['total_pesanan_baru'], 0, ',', '.'); ?>
                        </strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3" style="background: #F8FAFC; border-radius: 10px;">
                        <small class="text-muted d-block mb-1">Metode Pembayaran</small>
                        <strong><?php echo $_SESSION['metode_pembayaran_baru']; ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info mt-3 mb-0" style="border-radius: 10px; font-size: 14px;">
                <i class="fas fa-info-circle me-2"></i>
                Silakan transfer sesuai nominal di atas dan upload bukti pembayaran Anda di bawah ini.
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- ============================================
         DAFTAR PESANAN MENUNGGU PEMBAYARAN
         ============================================ -->
    <?php if (count($pesanan_pending) > 0): ?>
    <div class="mb-4">
        <h4 class="fw-bold mb-3">
            <i class="fas fa-clock me-2" style="color: #F59E0B;"></i>
            Pesanan Menunggu Pembayaran
            <span class="badge bg-warning ms-2"><?php echo count($pesanan_pending); ?></span>
        </h4>
        
        <?php foreach ($pesanan_pending as $pesanan): ?>
        <div class="card mb-3" style="border-radius: var(--radius-lg);">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: #FFFBEB; border-bottom: 1px solid #FCD34D;">
                <div>
                    <strong>
                        <i class="fas fa-hashtag me-1"></i> 
                        <span style="font-family: 'Courier New', monospace;"><?php echo $pesanan['kode_pesanan']; ?></span>
                    </strong>
                    <span class="badge badge-status badge-pending ms-2">
                        <span class="status-dot"></span> Menunggu Pembayaran
                    </span>
                </div>
                <small class="text-muted">
                    <i class="fas fa-calendar me-1"></i> 
                    <?php echo date('d M Y H:i', strtotime($pesanan['created_at'])); ?>
                </small>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Info Pesanan -->
                    <div class="col-md-6">
                        <div class="p-3" style="background: #F8FAFC; border-radius: 10px;">
                            <table class="small" style="width: 100%;">
                                <tr>
                                    <td class="text-muted" style="padding: 4px 8px;">Total</td>
                                    <td class="fw-bold" style="padding: 4px 8px;">Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted" style="padding: 4px 8px;">Metode</td>
                                    <td style="padding: 4px 8px;"><?php echo $pesanan['metode_pembayaran']; ?></td>
                                </tr>
                                <?php if ($pesanan['catatan']): ?>
                                <tr>
                                    <td class="text-muted" style="padding: 4px 8px;">Catatan</td>
                                    <td style="padding: 4px 8px;"><?php echo $pesanan['catatan']; ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Info Rekening -->
                    <div class="col-md-6">
                        <div class="p-3" style="background: #EFF6FF; border-radius: 10px; border: 1px dashed #93C5FD;">
                            <strong><i class="fas fa-university me-2"></i>Rekening Tujuan:</strong>
                            <div class="mt-2">
                                <?php if ($pesanan['metode_pembayaran'] == 'Transfer Bank BCA'): ?>
                                <div class="fw-bold">Bank BCA</div>
                                <div style="font-family: 'Courier New', monospace; font-size: 1.1rem;">1234567890</div>
                                <small class="text-muted">a/n LaundryStoreID</small>
                                <?php elseif ($pesanan['metode_pembayaran'] == 'Transfer Bank BRI'): ?>
                                <div class="fw-bold">Bank BRI</div>
                                <div style="font-family: 'Courier New', monospace; font-size: 1.1rem;">0987654321</div>
                                <small class="text-muted">a/n LaundryStoreID</small>
                                <?php else: ?>
                                <div class="fw-bold">Bank Mandiri</div>
                                <div style="font-family: 'Courier New', monospace; font-size: 1.1rem;">1122334455</div>
                                <small class="text-muted">a/n LaundryStoreID</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Upload -->
                <form method="POST" action="" enctype="multipart/form-data" class="mt-3">
                    <input type="hidden" name="id_pesanan" value="<?php echo $pesanan['id']; ?>">
                    
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">
                                <i class="fas fa-image me-1"></i> Upload Bukti Pembayaran
                            </label>
                            <input type="file" name="bukti_pembayaran" class="form-control" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif" 
                                   required
                                   onchange="previewImage(this, 'preview_<?php echo $pesanan['id']; ?>')">
                            <small class="text-muted">
                                Format: JPG, PNG, GIF | Maks: 2MB
                            </small>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px;">
                                <i class="fas fa-upload me-2"></i> Upload Bukti
                            </button>
                        </div>
                    </div>
                    
                    <!-- Preview Gambar -->
                    <div class="mt-3" id="preview_container_<?php echo $pesanan['id']; ?>" style="display: none;">
                        <img id="preview_<?php echo $pesanan['id']; ?>" src="#" 
                             style="max-width: 300px; max-height: 200px; border-radius: 10px; border: 2px solid var(--color-border);">
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- ============================================
         PESANAN MENUNGGU KONFIRMASI
         ============================================ -->
    <?php if (count($pesanan_confirming) > 0): ?>
    <div class="mb-4">
        <h4 class="fw-bold mb-3">
            <i class="fas fa-search me-2" style="color: #3B82F6;"></i>
            Pesanan Menunggu Konfirmasi
            <span class="badge bg-info ms-2"><?php echo count($pesanan_confirming); ?></span>
        </h4>
        
        <?php foreach ($pesanan_confirming as $pesanan): ?>
        <div class="card mb-3" style="border-radius: var(--radius-lg); opacity: 0.8;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: #EFF6FF; border-bottom: 1px solid #93C5FD;">
                <div>
                    <strong>
                        <span style="font-family: 'Courier New', monospace;"><?php echo $pesanan['kode_pesanan']; ?></span>
                    </strong>
                    <span class="badge badge-status badge-confirming ms-2">
                        <span class="status-dot"></span> Menunggu Konfirmasi
                    </span>
                </div>
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i> 
                    <?php echo date('d M Y H:i', strtotime($pesanan['updated_at'])); ?>
                </small>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-check-circle" style="font-size: 2rem; color: #3B82F6;"></i>
                    <div>
                        <strong>Bukti pembayaran sudah diupload!</strong>
                        <p class="text-muted mb-0 small">Tim kami sedang memverifikasi pembayaran Anda. Proses verifikasi maksimal 1x24 jam.</p>
                    </div>
                    <?php if ($pesanan['bukti_pembayaran']): ?>
                    <a href="../uploads/bukti/<?php echo $pesanan['bukti_pembayaran']; ?>" 
                       target="_blank" 
                       class="btn btn-sm ms-auto" 
                       style="background: #EFF6FF; color: #3B82F6; border-radius: 8px;">
                        <i class="fas fa-eye me-1"></i> Lihat Bukti
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- ============================================
         PESANAN SELESAI (INFO)
         ============================================ -->
    <?php if (count($pesanan_done) > 0 && count($pesanan_pending) == 0): ?>
    <div class="mb-4">
        <h4 class="fw-bold mb-3">
            <i class="fas fa-check-circle me-2" style="color: #10B981;"></i>
            Pesanan Terbaru
        </h4>
        
        <?php foreach ($pesanan_done as $pesanan): 
            $badge_class = $pesanan['status'] == 'selesai' ? 'badge-done' : 'badge-confirmed';
            $icon = $pesanan['status'] == 'selesai' ? 'fa-trophy' : 'fa-check-circle';
            $status_text = $pesanan['status'] == 'selesai' ? 'Selesai' : 'Dikonfirmasi';
        ?>
        <div class="card mb-2" style="border-radius: var(--radius-lg);">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span style="font-family: 'Courier New', monospace;"><?php echo $pesanan['kode_pesanan']; ?></span>
                    <span class="badge badge-status <?php echo $badge_class; ?> ms-2">
                        <span class="status-dot"></span>
                        <i class="fas <?php echo $icon; ?> me-1"></i> <?php echo $status_text; ?>
                    </span>
                </div>
                <div>
                    <strong>Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></strong>
                    <a href="riwayat.php" class="btn btn-sm ms-2" style="background: var(--color-primary-50); color: var(--color-primary); border-radius: 8px;">
                        <i class="fas fa-eye me-1"></i> Detail
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- ============================================
         TIDAK ADA PESANAN
         ============================================ -->
    <?php if (count($pesanan_pending) == 0 && count($pesanan_confirming) == 0 && !$show_new_order): ?>
    <div class="card" style="border-radius: var(--radius-lg);">
        <div class="card-body text-center py-5">
            <i class="fas fa-check-circle" style="font-size: 5rem; color: #10B981;"></i>
            <h4 class="fw-bold mt-3">Tidak Ada Pesanan Pending</h4>
            <p class="text-muted mb-3">Semua pesanan Anda sudah dibayar atau tidak ada pesanan yang perlu diupload bukti pembayaran.</p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="riwayat.php" class="btn btn-primary" style="border-radius: 10px;">
                    <i class="fas fa-history me-1"></i> Lihat Riwayat
                </a>
                <a href="produk.php" class="btn btn-outline" style="border-radius: 10px;">
                    <i class="fas fa-store me-1"></i> Belanja Lagi
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- ============================================
         TIPS PEMBAYARAN
         ============================================ -->
    <div class="card mt-4" style="border-radius: var(--radius-lg);">
        <div class="card-header" style="background: linear-gradient(135deg, #F5F3FF, #E0F2FE);">
            <h5 class="fw-bold mb-0">
                <i class="fas fa-lightbulb me-2" style="color: #F59E0B;"></i> Tips Pembayaran
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <i class="fas fa-mobile-alt" style="font-size: 2rem; color: var(--color-primary); margin-bottom: 10px;"></i>
                        <h6 class="fw-bold">1. Transfer via Mobile Banking</h6>
                        <small class="text-muted">Gunakan mobile banking atau ATM untuk transfer ke rekening tujuan.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <i class="fas fa-camera" style="font-size: 2rem; color: var(--color-accent); margin-bottom: 10px;"></i>
                        <h6 class="fw-bold">2. Screenshot/Foto Bukti</h6>
                        <small class="text-muted">Ambil screenshot atau foto bukti transfer yang jelas dan tidak blur.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <i class="fas fa-upload" style="font-size: 2rem; color: var(--color-success); margin-bottom: 10px;"></i>
                        <h6 class="fw-bold">3. Upload & Tunggu Konfirmasi</h6>
                        <small class="text-muted">Upload bukti transfer dan tunggu konfirmasi dari admin (maks 1x24 jam).</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================
     JAVASCRIPT
     ============================================ -->
<script>
    /**
     * Preview gambar sebelum upload
     */
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const container = document.getElementById('preview_container_' + previewId.split('_')[1]);
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                if (container) {
                    container.style.display = 'block';
                }
                preview.style.display = 'block';
            };
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '#';
            if (container) {
                container.style.display = 'none';
            }
            preview.style.display = 'none';
        }
    }
    
    /**
     * Validasi ukuran file sebelum upload
     */
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const maxSize = 2 * 1024 * 1024; // 2MB
                if (file.size > maxSize) {
                    alert('Ukuran file maksimal 2MB! Ukuran file Anda: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
                    this.value = ''; // Reset input
                }
                
                // Validasi ekstensi
                const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                const extension = file.name.split('.').pop().toLowerCase();
                if (!allowedExtensions.includes(extension)) {
                    alert('Format file tidak diizinkan! Hanya JPG, JPEG, PNG, dan GIF.');
                    this.value = '';
                }
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>