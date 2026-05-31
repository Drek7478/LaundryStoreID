<?php
/**
 * LaundryStoreID - Footer Template
 * 
 * Digunakan oleh semua halaman user dan admin
 * Tema: Violet + Cyan + Slate
 */
?>

    </div><!-- /#app-wrapper -->

    <!-- ============================================
         FOOTER (Hanya untuk halaman User/Non-Admin)
         ============================================ -->
    <?php if (strpos($_SERVER['PHP_SELF'], '/admin/') === false): ?>
    <footer class="bg-dark text-white mt-5">
        <!-- Main Footer Content -->
        <div class="container py-5">
            <div class="row g-4">
                <!-- About Company -->
                <div class="col-lg-4">
                    <h5 class="mb-3 fw-bold">
                        <span style="color: #A78BFA;"><i class="fas fa-jug-detergent me-2"></i>Laundry</span><span style="color: #22D3EE;">StoreID</span>
                    </h5>
                    <p class="text-muted small mb-3" style="line-height: 1.7;">
                        Toko perlengkapan laundry terlengkap dan terpercaya di Indonesia. 
                        Menyediakan deterjen, pewangi, pemutih, dan alat laundry berkualitas 
                        dengan harga terbaik untuk kebutuhan rumah tangga maupun bisnis laundry Anda.
                    </p>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge" style="background: rgba(124,58,237,0.2); color: #A78BFA; padding: 6px 12px; border-radius: 6px;">
                            <i class="fas fa-check-circle me-1"></i> Produk Original
                        </span>
                        <span class="badge" style="background: rgba(6,182,212,0.2); color: #22D3EE; padding: 6px 12px; border-radius: 6px;">
                            <i class="fas fa-tags me-1"></i> Harga Terbaik
                        </span>
                        <span class="badge" style="background: rgba(16,185,129,0.2); color: #34D399; padding: 6px 12px; border-radius: 6px;">
                            <i class="fas fa-truck-fast me-1"></i> Pengiriman Cepat
                        </span>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-4">
                    <h6 class="text-white fw-bold mb-3">Menu Utama</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>/user/dashboard.php" class="text-muted text-decoration-none footer-link">
                                <i class="fas fa-home me-2" style="width: 16px;"></i> Dashboard
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>/user/produk.php" class="text-muted text-decoration-none footer-link">
                                <i class="fas fa-store me-2" style="width: 16px;"></i> Katalog Produk
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>/user/keranjang.php" class="text-muted text-decoration-none footer-link">
                                <i class="fas fa-shopping-cart me-2" style="width: 16px;"></i> Keranjang Belanja
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>/user/riwayat.php" class="text-muted text-decoration-none footer-link">
                                <i class="fas fa-history me-2" style="width: 16px;"></i> Riwayat Pesanan
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Categories -->
                <div class="col-lg-2 col-md-4">
                    <h6 class="text-white fw-bold mb-3">Kategori</h6>
                    <ul class="list-unstyled small">
                        <?php
                        // Ambil 5 kategori pertama untuk footer
                        if (isset($pdo)) {
                            try {
                                $stmt_footer = $pdo->query("SELECT * FROM kategori LIMIT 5");
                                $footer_kategori = $stmt_footer->fetchAll();
                                foreach ($footer_kategori as $kat):
                        ?>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>/user/produk.php?kategori=<?php echo $kat['id']; ?>" class="text-muted text-decoration-none footer-link">
                                <i class="fas fa-tag me-2" style="width: 16px;"></i> <?php echo $kat['nama_kategori']; ?>
                            </a>
                        </li>
                        <?php 
                                endforeach;
                            } catch (Exception $e) {
                                // Fallback jika query gagal
                        ?>
                        <li class="mb-2"><span class="text-muted"><i class="fas fa-tag me-2"></i> Deterjen</span></li>
                        <li class="mb-2"><span class="text-muted"><i class="fas fa-tag me-2"></i> Pewangi</span></li>
                        <li class="mb-2"><span class="text-muted"><i class="fas fa-tag me-2"></i> Pemutih</span></li>
                        <li class="mb-2"><span class="text-muted"><i class="fas fa-tag me-2"></i> Alat Laundry</span></li>
                        <?php } ?>
                        <?php } else { ?>
                        <li class="mb-2"><span class="text-muted"><i class="fas fa-tag me-2"></i> Deterjen</span></li>
                        <li class="mb-2"><span class="text-muted"><i class="fas fa-tag me-2"></i> Pewangi</span></li>
                        <li class="mb-2"><span class="text-muted"><i class="fas fa-tag me-2"></i> Pemutih</span></li>
                        <li class="mb-2"><span class="text-muted"><i class="fas fa-tag me-2"></i> Alat Laundry</span></li>
                        <li class="mb-2"><span class="text-muted"><i class="fas fa-tag me-2"></i> Kemasan</span></li>
                        <?php } ?>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-lg-2 col-md-4">
                    <h6 class="text-white fw-bold mb-3">Kontak Kami</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2 text-muted">
                            <i class="fas fa-envelope me-2" style="width: 16px;"></i> 
                            <a href="mailto:support@laundrystoreid.com" class="text-muted text-decoration-none footer-link">support@laundrystoreid.com</a>
                        </li>
                        <li class="mb-2 text-muted">
                            <i class="fas fa-phone me-2" style="width: 16px;"></i> 
                            <a href="tel:+6281234567890" class="text-muted text-decoration-none footer-link">0812-3456-7890</a>
                        </li>
                        <li class="mb-2 text-muted">
                            <i class="fas fa-clock me-2" style="width: 16px;"></i> 
                            Senin - Sabtu<br>
                            <span class="ms-4">08.00 - 21.00 WIB</span>
                        </li>
                        <li class="mb-2 text-muted">
                            <i class="fas fa-map-marker-alt me-2" style="width: 16px;"></i> 
                            Jakarta, Indonesia
                        </li>
                    </ul>
                </div>
                
                <!-- Social Media & Newsletter -->
                <div class="col-lg-2">
                    <h6 class="text-white fw-bold mb-3">Ikuti Kami</h6>
                    <div class="d-flex gap-2 mb-4">
                        <a href="#" class="btn btn-sm rounded-circle social-btn" title="Instagram" style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); transition: all 0.2s ease;">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="btn btn-sm rounded-circle social-btn" title="Facebook" style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); transition: all 0.2s ease;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="btn btn-sm rounded-circle social-btn" title="TikTok" style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); transition: all 0.2s ease;">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="#" class="btn btn-sm rounded-circle social-btn" title="WhatsApp" style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); transition: all 0.2s ease;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                    
                    <h6 class="text-white fw-bold mb-2 small">Pembayaran</h6>
                    <div class="d-flex gap-1 flex-wrap">
                        <span class="badge" style="background: rgba(255,255,255,0.1); color: #94A3B8; padding: 5px 8px; font-size: 10px;">BCA</span>
                        <span class="badge" style="background: rgba(255,255,255,0.1); color: #94A3B8; padding: 5px 8px; font-size: 10px;">BRI</span>
                        <span class="badge" style="background: rgba(255,255,255,0.1); color: #94A3B8; padding: 5px 8px; font-size: 10px;">Mandiri</span>
                        <span class="badge" style="background: rgba(255,255,255,0.1); color: #94A3B8; padding: 5px 8px; font-size: 10px;">BNI</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Bottom Bar -->
        <div style="border-top: 1px solid rgba(255,255,255,0.06);">
            <div class="container py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small class="text-muted">
                            &copy; <?php echo date('Y'); ?> <span style="color: #A78BFA;">LaundryStoreID</span>. 
                            All rights reserved.
                        </small>
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <small>
                            <a href="#" class="text-muted text-decoration-none me-3 footer-link">
                                <i class="fas fa-file-alt me-1"></i> Syarat & Ketentuan
                            </a>
                            <a href="#" class="text-muted text-decoration-none me-3 footer-link">
                                <i class="fas fa-shield-alt me-1"></i> Kebijakan Privasi
                            </a>
                            <a href="#" class="text-muted text-decoration-none footer-link">
                                <i class="fas fa-question-circle me-1"></i> Bantuan
                            </a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Footer CSS Styles -->
    <style>
        /* Footer Link Hover Effect */
        .footer-link {
            transition: all 0.2s ease;
            position: relative;
        }
        
        .footer-link:hover {
            color: #A78BFA !important;
            padding-left: 4px;
        }
        
        /* Social Media Button Hover */
        .social-btn:hover {
            background: rgba(124,58,237,0.3) !important;
            border-color: #A78BFA !important;
            color: #A78BFA !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(124,58,237,0.3);
        }
        
        /* Footer animation */
        footer {
            position: relative;
        }
        
        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #7C3AED, #06B6D4, #10B981);
        }
    </style>
    
    <?php else: ?>
    <!-- ============================================
         FOOTER ADMIN (Minimal)
         ============================================ -->
    <footer style="background: #0F172A; color: white; padding: 12px 24px; margin-left: 260px; border-top: 1px solid rgba(255,255,255,0.06);">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                &copy; <?php echo date('Y'); ?> <span style="color: #A78BFA;">LaundryStoreID</span> Admin Panel
            </small>
            <small class="text-muted">
                Version 3.0 • Violet + Cyan Theme
            </small>
        </div>
    </footer>
    
    <style>
        @media (max-width: 991.98px) {
            footer {
                margin-left: 0 !important;
            }
        }
    </style>
    <?php endif; ?>

    <!-- ============================================
         SCRIPTS
         ============================================ -->
    
    <!-- Bootstrap 5 JS Bundle (via CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript - LaundryStoreID -->
    <script src="/laundry-store/assets/js/main.js"></script>
    
    <!-- ============================================
         TOAST NOTIFICATION
         ============================================ -->
    <?php if (isset($_SESSION['alert'])): ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
        <div class="toast show border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true"
             style="border-radius: 12px; 
                    background: <?php 
                        echo $_SESSION['alert']['type'] === 'success' 
                            ? 'linear-gradient(135deg, #059669, #10B981)' 
                            : ($_SESSION['alert']['type'] === 'danger' || $_SESSION['alert']['type'] === 'error'
                                ? 'linear-gradient(135deg, #DC2626, #EF4444)' 
                                : ($_SESSION['alert']['type'] === 'warning'
                                    ? 'linear-gradient(135deg, #D97706, #F59E0B)'
                                    : 'linear-gradient(135deg, #0891B2, #06B6D4)'
                                )
                            ); 
                    ?>; 
                    color: white; min-width: 280px;">
            <div class="d-flex align-items-center px-3 py-2">
                <span style="font-size: 1.2rem; margin-right: 10px;">
                    <i class="fas fa-<?php 
                        echo $_SESSION['alert']['type'] === 'success' 
                            ? 'check-circle' 
                            : ($_SESSION['alert']['type'] === 'danger' || $_SESSION['alert']['type'] === 'error'
                                ? 'times-circle' 
                                : ($_SESSION['alert']['type'] === 'warning'
                                    ? 'exclamation-triangle'
                                    : 'info-circle'
                                )
                            ); 
                    ?>"></i>
                </span>
                <div class="toast-body p-0 fw-medium">
                    <?php 
                        echo $_SESSION['alert']['message'];
                        unset($_SESSION['alert']); // Hapus setelah ditampilkan
                    ?>
                </div>
                <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-show toast dan hilangkan setelah 4 detik
        document.addEventListener('DOMContentLoaded', function() {
            const toastEl = document.querySelector('.toast');
            if (toastEl) {
                const toast = new bootstrap.Toast(toastEl, {
                    delay: 4000,
                    animation: true
                });
                toast.show();
                
                // Hapus toast dari DOM setelah hidden
                toastEl.addEventListener('hidden.bs.toast', function() {
                    if (toastEl.parentElement) {
                        toastEl.parentElement.remove();
                    }
                });
            }
        });
    </script>
    <?php endif; ?>
    
    <!-- ============================================
         BACK TO TOP BUTTON
         ============================================ -->
    <button id="back-to-top" title="Kembali ke atas" 
            style="display: none; position: fixed; bottom: 30px; right: 30px; z-index: 999;
                   width: 44px; height: 44px; border-radius: 50%; border: none;
                   background: var(--gradient-primary); color: white; cursor: pointer;
                   box-shadow: 0 4px 15px rgba(124,58,237,0.4); transition: all 0.3s ease;
                   font-size: 1.2rem;">
        <i class="fas fa-chevron-up"></i>
    </button>
    
    <script>
        // Back to Top Button
        document.addEventListener('DOMContentLoaded', function() {
            const backToTopBtn = document.getElementById('back-to-top');
            
            if (backToTopBtn) {
                // Show/hide button based on scroll position
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 300) {
                        backToTopBtn.style.display = 'flex';
                        backToTopBtn.style.alignItems = 'center';
                        backToTopBtn.style.justifyContent = 'center';
                    } else {
                        backToTopBtn.style.display = 'none';
                    }
                });
                
                // Scroll to top on click
                backToTopBtn.addEventListener('click', function() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
                
                // Hover effect
                backToTopBtn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                    this.style.boxShadow = '0 8px 25px rgba(124,58,237,0.6)';
                });
                
                backToTopBtn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 4px 15px rgba(124,58,237,0.4)';
                });
            }
        });
    </script>
    
    <!-- ============================================
         PAGE LOAD COMPLETE INDICATOR
         ============================================ -->
    <script>
        // Tandai bahwa halaman sudah selesai dimuat
        window.addEventListener('load', function() {
            document.body.classList.add('page-loaded');
            
            // Log performance
            if (window.performance && window.performance.timing) {
                const loadTime = window.performance.timing.domContentLoadedEventEnd - window.performance.timing.navigationStart;
                if (loadTime > 0) {
                    console.log(`⏱️ Halaman dimuat dalam ${loadTime}ms`);
                }
            }
        });
    </script>
    
    <!-- ============================================
         ERROR HANDLING (Opsional)
         ============================================ -->
    <script>
        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.message);
            // Anda bisa menambahkan error reporting ke server di sini
        });
        
        // Unhandled promise rejection handler
        window.addEventListener('unhandledrejection', function(e) {
            console.error('Unhandled promise rejection:', e.reason);
        });
    </script>
</body>
</html>