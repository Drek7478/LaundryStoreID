<?php
/**
 * LaundryStoreID - Checkout
 * 
 * Halaman checkout untuk memproses pesanan
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
// AMBIL DATA USER
// ============================================
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id_user]);
$user = $stmt->fetch();

// ============================================
// AMBIL DATA KERANJANG
// ============================================
$stmt = $pdo->prepare("
    SELECT k.*, p.nama_produk, p.harga, p.gambar, p.stok, p.satuan, p.berat
    FROM keranjang k 
    JOIN produk p ON k.id_produk = p.id 
    WHERE k.id_user = ?
");
$stmt->execute([$id_user]);
$keranjang_items = $stmt->fetchAll();

// Jika keranjang kosong, redirect
if (count($keranjang_items) === 0) {
    setAlert('warning', 'Keranjang Anda kosong! Silakan tambahkan produk terlebih dahulu.');
    redirect('keranjang.php');
}

// ============================================
// VALIDASI STOK SEBELUM CHECKOUT
// ============================================
$stok_issues = [];
foreach ($keranjang_items as $item) {
    if ($item['stok'] == 0) {
        $stok_issues[] = $item['nama_produk'] . ' - Stok habis';
    } elseif ($item['jumlah'] > $item['stok']) {
        $stok_issues[] = $item['nama_produk'] . ' - Stok hanya ' . $item['stok'];
    }
}

if (count($stok_issues) > 0) {
    $error_message = 'Beberapa produk memiliki masalah stok:<br>' . implode('<br>', $stok_issues);
    setAlert('danger', $error_message);
    redirect('keranjang.php');
}

// ============================================
// HITUNG TOTAL
// ============================================
$subtotal = 0;
$total_berat = 0;
foreach ($keranjang_items as $item) {
    $subtotal += $item['harga'] * $item['jumlah'];
    $total_berat += ($item['berat'] ?? 1) * $item['jumlah']; // Default berat 1 kg
}

// Ongkos kirim
$ongkir = $subtotal >= 150000 ? 0 : 15000;
$grand_total = $subtotal + $ongkir;

// ============================================
// PROSES CHECKOUT
// ============================================
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alamat_pengiriman = sanitize($_POST['alamat']);
    $metode_pembayaran = sanitize($_POST['metode_pembayaran']);
    $catatan = sanitize($_POST['catatan'] ?? '');
    $no_hp = sanitize($_POST['no_hp'] ?? $user['no_hp']);
    
    // Validasi
    $errors = [];
    if (empty($alamat_pengiriman)) {
        $errors[] = 'Alamat pengiriman wajib diisi!';
    } elseif (strlen($alamat_pengiriman) < 10) {
        $errors[] = 'Alamat minimal 10 karakter!';
    }
    
    if (empty($metode_pembayaran)) {
        $errors[] = 'Metode pembayaran wajib dipilih!';
    }
    
    if (empty($no_hp)) {
        $errors[] = 'Nomor HP wajib diisi!';
    }
    
    // Validasi ulang stok sebelum insert
    foreach ($keranjang_items as $item) {
        $stmt = $pdo->prepare("SELECT stok FROM produk WHERE id = ?");
        $stmt->execute([$item['id_produk']]);
        $current_stok = $stmt->fetchColumn();
        
        if ($current_stok < $item['jumlah']) {
            $errors[] = 'Stok ' . $item['nama_produk'] . ' tidak mencukupi! (Tersedia: ' . $current_stok . ')';
        }
    }
    
    if (empty($errors)) {
        try {
            // Mulai transaksi
            $pdo->beginTransaction();
            
            // Generate kode pesanan
            $tanggal = date('Ymd');
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pesanan WHERE DATE(created_at) = CURDATE()");
            $stmt->execute();
            $count = $stmt->fetch()['total'] + 1;
            $kode_pesanan = "ORD-" . $tanggal . "-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            
            // Update no_hp user jika berbeda
            if ($no_hp != $user['no_hp']) {
                $stmt = $pdo->prepare("UPDATE users SET no_hp = ?, alamat = ? WHERE id = ?");
                $stmt->execute([$no_hp, $alamat_pengiriman, $id_user]);
            }
            
            // Insert pesanan
            $stmt = $pdo->prepare("
                INSERT INTO pesanan (kode_pesanan, id_user, total_harga, status, metode_pembayaran, catatan, alamat_pengiriman) 
                VALUES (?, ?, ?, 'menunggu_pembayaran', ?, ?, ?)
            ");
            $stmt->execute([$kode_pesanan, $id_user, $grand_total, $metode_pembayaran, $catatan, $alamat_pengiriman]);
            $id_pesanan = $pdo->lastInsertId();
            
            // Insert detail pesanan & update stok
            foreach ($keranjang_items as $item) {
                $subtotal_item = $item['harga'] * $item['jumlah'];
                
                // Insert detail
                $stmt = $pdo->prepare("
                    INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, harga_satuan, subtotal) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$id_pesanan, $item['id_produk'], $item['jumlah'], $item['harga'], $subtotal_item]);
                
                // Kurangi stok
                $stmt = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id = ? AND stok >= ?");
                $stmt->execute([$item['jumlah'], $item['id_produk'], $item['jumlah']]);
                
                // Cek apakah stok berhasil dikurangi
                if ($stmt->rowCount() == 0) {
                    throw new Exception('Stok ' . $item['nama_produk'] . ' tidak mencukupi!');
                }
            }
            
            // Hapus keranjang
            $stmt = $pdo->prepare("DELETE FROM keranjang WHERE id_user = ?");
            $stmt->execute([$id_user]);
            
            // Commit transaksi
            $pdo->commit();
            
            // Simpan info pesanan ke session untuk halaman upload bukti
            $_SESSION['kode_pesanan_baru'] = $kode_pesanan;
            $_SESSION['id_pesanan_baru'] = $id_pesanan;
            $_SESSION['total_pesanan_baru'] = $grand_total;
            $_SESSION['metode_pembayaran_baru'] = $metode_pembayaran;
            
            // Redirect ke halaman upload bukti
            redirect('upload_bukti.php');
            
        } catch (Exception $e) {
            // Rollback jika ada error
            $pdo->rollBack();
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
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
                <li class="breadcrumb-item"><a href="keranjang.php"><i class="fas fa-shopping-cart me-1"></i> Keranjang</a></li>
                <li class="breadcrumb-item active">Checkout</li>
            </ol>
        </nav>
        <h2 class="fw-800 mb-1">
            <i class="fas fa-credit-card me-2" style="color: var(--color-primary);"></i>
            Checkout Pesanan
        </h2>
        <p class="text-muted mb-0">Lengkapi data pengiriman dan pilih metode pembayaran</p>
    </div>
</div>

<div class="container py-4">
    
    <!-- ============================================
         STEPPER
         ============================================ -->
    <div class="stepper mb-4">
        <div class="text-center">
            <div class="step-circle done"><i class="fas fa-check"></i></div>
            <div class="step-label">Keranjang</div>
        </div>
        <div class="step-line done"></div>
        <div class="text-center">
            <div class="step-circle active">2</div>
            <div class="step-label" style="color: var(--color-primary); font-weight: 600;">Checkout</div>
        </div>
        <div class="step-line"></div>
        <div class="text-center">
            <div class="step-circle pending">3</div>
            <div class="step-label">Pembayaran</div>
        </div>
    </div>
    
    <!-- Error Alert -->
    <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-start gap-2 mb-4" style="border-radius: 12px;">
        <i class="fas fa-exclamation-circle mt-1"></i>
        <span><?php echo $error; ?></span>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="" id="checkoutForm">
        <div class="row g-4">
            
            <!-- ============================================
                 LEFT COLUMN - FORM CHECKOUT
                 ============================================ -->
            <div class="col-lg-7">
                
                <!-- Informasi Pengiriman -->
                <div class="card mb-4" style="border-radius: var(--radius-lg);">
                    <div class="card-header" style="background: linear-gradient(135deg, #F5F3FF, #E0F2FE);">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-truck me-2"></i> Informasi Pengiriman
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nama Penerima <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" value="<?php echo $user['nama']; ?>" readonly style="background: #F8FAFC;">
                            <small class="text-muted">Nama sesuai akun</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nomor HP <span class="text-danger">*</span>
                            </label>
                            <div class="input-icon-wrapper">
                                <span class="input-icon"><i class="fas fa-phone"></i></span>
                                <input type="tel" name="no_hp" class="form-control ps-5" 
                                       value="<?php echo $user['no_hp']; ?>" 
                                       placeholder="Contoh: 08123456789" 
                                       required
                                       pattern="[0-9]{10,15}">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Alamat Lengkap <span class="text-danger">*</span>
                            </label>
                            <div class="input-icon-wrapper">
                                <span class="input-icon"><i class="fas fa-map-marker-alt"></i></span>
                                <textarea name="alamat" class="form-control ps-5" rows="3" 
                                          placeholder="Masukkan alamat lengkap pengiriman (jalan, nomor, RT/RW, kelurahan, kecamatan, kota, kode pos)" 
                                          required
                                          minlength="10"><?php echo $user['alamat']; ?></textarea>
                            </div>
                            <small class="text-muted">Pastikan alamat lengkap dan benar untuk memperlancar pengiriman</small>
                        </div>
                        
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" value="<?php echo $_SESSION['email']; ?>" readonly style="background: #F8FAFC;">
                        </div>
                    </div>
                </div>
                
                <!-- Metode Pembayaran -->
                <div class="card mb-4" style="border-radius: var(--radius-lg);">
                    <div class="card-header" style="background: linear-gradient(135deg, #F5F3FF, #E0F2FE);">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-credit-card me-2"></i> Metode Pembayaran
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Pilih salah satu metode pembayaran:</p>
                        
                        <!-- BCA -->
                        <div class="payment-option selected" onclick="selectPayment(this)">
                            <input type="radio" name="metode_pembayaran" value="Transfer Bank BCA" checked hidden>
                            <div style="width: 56px; height: 56px; border-radius: 12px; background: #E8F4FD; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #0066AE; font-size: 1.2rem; flex-shrink: 0;">
                                BCA
                            </div>
                            <div style="flex-grow: 1;">
                                <div class="fw-bold">Transfer Bank BCA</div>
                                <small class="text-muted">No. Rek: 1234567890 a/n LaundryStoreID</small>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i> Verifikasi manual 1-2 jam
                                    </small>
                                </div>
                            </div>
                            <span class="payment-check" style="font-size: 1.5rem;"><i class="fas fa-check-circle"></i></span>
                        </div>
                        
                        <!-- BRI -->
                        <div class="payment-option" onclick="selectPayment(this)">
                            <input type="radio" name="metode_pembayaran" value="Transfer Bank BRI" hidden>
                            <div style="width: 56px; height: 56px; border-radius: 12px; background: #E8F4FD; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #005098; font-size: 1.2rem; flex-shrink: 0;">
                                BRI
                            </div>
                            <div style="flex-grow: 1;">
                                <div class="fw-bold">Transfer Bank BRI</div>
                                <small class="text-muted">No. Rek: 0987654321 a/n LaundryStoreID</small>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i> Verifikasi manual 1-2 jam
                                    </small>
                                </div>
                            </div>
                            <span class="payment-check" style="font-size: 1.5rem;"><i class="fas fa-check-circle"></i></span>
                        </div>
                        
                        <!-- Mandiri -->
                        <div class="payment-option" onclick="selectPayment(this)">
                            <input type="radio" name="metode_pembayaran" value="Transfer Bank Mandiri" hidden>
                            <div style="width: 56px; height: 56px; border-radius: 12px; background: #E8F4FD; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #00448C; font-size: 1.2rem; flex-shrink: 0;">
                                MDR
                            </div>
                            <div style="flex-grow: 1;">
                                <div class="fw-bold">Transfer Bank Mandiri</div>
                                <small class="text-muted">No. Rek: 1122334455 a/n LaundryStoreID</small>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i> Verifikasi manual 1-2 jam
                                    </small>
                                </div>
                            </div>
                            <span class="payment-check" style="font-size: 1.5rem;"><i class="fas fa-check-circle"></i></span>
                        </div>
                    </div>
                </div>
                
                <!-- Catatan Pesanan -->
                <div class="card" style="border-radius: var(--radius-lg);">
                    <div class="card-header" style="background: linear-gradient(135deg, #F5F3FF, #E0F2FE);">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-sticky-note me-2"></i> Catatan Pesanan (Opsional)
                        </h5>
                    </div>
                    <div class="card-body">
                        <textarea name="catatan" class="form-control" rows="2" 
                                  placeholder="Contoh: Warna merah, ukuran XL, atau catatan khusus lainnya..."></textarea>
                    </div>
                </div>
            </div>
            
            <!-- ============================================
                 RIGHT COLUMN - ORDER SUMMARY
                 ============================================ -->
            <div class="col-lg-5">
                <div style="position: sticky; top: 100px;">
                    
                    <!-- Ringkasan Pesanan -->
                    <div class="card card-gradient-header mb-4" style="border-radius: var(--radius-lg); overflow: hidden;">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-receipt me-2"></i> Ringkasan Pesanan
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- List Item -->
                            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 12px;">
                                <?php foreach ($keranjang_items as $item): 
                                    $subtotal_item = $item['harga'] * $item['jumlah'];
                                ?>
                                <div class="d-flex gap-3 mb-3 pb-3" style="border-bottom: 1px solid #F1F5F9;">
                                    <div style="flex-shrink: 0;">
                                        <?php if ($item['gambar']): ?>
                                        <img src="../assets/img/produk/<?php echo $item['gambar']; ?>" 
                                             style="width: 56px; height: 56px; border-radius: 10px; object-fit: cover; border: 1px solid var(--color-border);">
                                        <?php else: ?>
                                        <div style="width: 56px; height: 56px; border-radius: 10px; background: linear-gradient(135deg, #F5F3FF, #CFFAFE); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                                            <i class="fas fa-box-open"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="flex-grow: 1; min-width: 0;">
                                        <div class="fw-semibold small" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo $item['nama_produk']; ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 12px;">
                                            <?php echo $item['jumlah']; ?> x Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="fw-semibold small flex-shrink-0">
                                        Rp <?php echo number_format($subtotal_item, 0, ',', '.'); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <hr>
                            
                            <!-- Subtotal -->
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal (<?php echo count($keranjang_items); ?> item)</span>
                                <span class="fw-semibold">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                            </div>
                            
                            <!-- Ongkir -->
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
                            
                            <!-- Estimasi Berat -->
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">
                                    <i class="fas fa-weight-hanging me-1"></i> Estimasi Berat
                                </span>
                                <span class="fw-semibold"><?php echo $total_berat; ?> kg</span>
                            </div>
                            
                            <hr>
                            
                            <!-- Grand Total -->
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold">Total Pembayaran</span>
                                <span style="font-size: 1.4rem; font-weight: 800; color: var(--color-primary);">
                                    Rp <?php echo number_format($grand_total, 0, ',', '.'); ?>
                                </span>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary w-100 btn-lg" id="checkoutButton" style="border-radius: 12px; font-weight: 700;">
                                <i class="fas fa-shopping-cart me-2"></i> Buat Pesanan Sekarang
                                <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                            
                            <p class="text-center text-muted small mt-3 mb-0">
                                <i class="fas fa-lock me-1"></i> Transaksi aman & terjamin
                            </p>
                            <p class="text-center text-muted small mt-1 mb-0">
                                <i class="fas fa-shield-alt me-1"></i> Data Anda dilindungi SSL
                            </p>
                        </div>
                    </div>
                    
                    <!-- Info Tambahan -->
                    <div class="card" style="border-radius: var(--radius-lg);">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3 small">
                                <i class="fas fa-info-circle me-2"></i> Informasi Penting
                            </h6>
                            <ul class="small text-muted mb-0" style="padding-left: 20px;">
                                <li class="mb-1">Pesanan akan diproses setelah pembayaran dikonfirmasi</li>
                                <li class="mb-1">Upload bukti pembayaran di halaman selanjutnya</li>
                                <li class="mb-1">Konfirmasi pembayaran maksimal 1x24 jam</li>
                                <li class="mb-1">Pengiriman dilakukan setelah pesanan dikonfirmasi</li>
                                <li>Biaya ongkir gratis untuk pembelian min. Rp 150.000</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ============================================
     JAVASCRIPT
     ============================================ -->
<script>
    /**
     * Pilih metode pembayaran
     */
    function selectPayment(element) {
        // Hapus class selected dari semua payment option
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        
        // Tambah class selected ke yang diklik
        element.classList.add('selected');
        
        // Check radio button
        const radio = element.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
        }
    }
    
    /**
     * Form submit dengan loading state
     */
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const button = document.getElementById('checkoutButton');
        const originalHTML = button.innerHTML;
        
        // Validasi form
        const alamat = this.querySelector('textarea[name="alamat"]').value.trim();
        const noHp = this.querySelector('input[name="no_hp"]').value.trim();
        
        if (!alamat || alamat.length < 10) {
            e.preventDefault();
            showToast('Alamat pengiriman wajib diisi (minimal 10 karakter)!', 'error');
            this.querySelector('textarea[name="alamat"]').focus();
            return;
        }
        
        if (!noHp || noHp.length < 10) {
            e.preventDefault();
            showToast('Nomor HP wajib diisi dengan benar!', 'error');
            this.querySelector('input[name="no_hp"]').focus();
            return;
        }
        
        // Tampilkan loading state
        button.classList.add('loading');
        button.disabled = true;
        button.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span>Memproses Pesanan...</span>
        `;
        
        // Reset setelah 5 detik jika ada masalah
        setTimeout(function() {
            if (button.disabled) {
                button.classList.remove('loading');
                button.disabled = false;
                button.innerHTML = originalHTML;
            }
        }, 10000);
    });
    
    /**
     * Toast notification (fallback jika main.js tidak ada)
     */
    function showToast(message, type = 'success') {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        
        const bgColor = type === 'success' ? 'linear-gradient(135deg, #059669, #10B981)' : 
                        type === 'error' ? 'linear-gradient(135deg, #DC2626, #EF4444)' : 
                        'linear-gradient(135deg, #D97706, #F59E0B)';
        
        const icon = type === 'success' ? 'fa-check-circle' : 
                     type === 'error' ? 'fa-times-circle' : 'fa-exclamation-triangle';
        
        toastContainer.innerHTML = `
            <div class="toast show border-0 shadow-lg" role="alert" 
                 style="border-radius: 12px; background: ${bgColor}; color: white; min-width: 280px;">
                <div class="d-flex align-items-center px-3 py-2">
                    <i class="fas ${icon}" style="font-size: 1.2rem; margin-right: 10px;"></i>
                    <div class="toast-body p-0 fw-medium">${message}</div>
                    <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        document.body.appendChild(toastContainer);
        
        const toastEl = toastContainer.querySelector('.toast');
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
        
        toastEl.addEventListener('hidden.bs.toast', function() {
            toastContainer.remove();
        });
    }
</script>

<?php include '../includes/footer.php'; ?>