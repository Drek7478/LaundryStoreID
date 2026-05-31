    <!-- Footer Admin -->
    <footer class="bg-dark text-white py-3 mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-2 d-none d-md-block"></div>
                <div class="col-md-10">
                    <p class="text-muted mb-0 small">&copy; <?php echo date('Y'); ?> LaundryStoreID Admin Panel. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS - Path absolut dari root -->
    <script src="/laundry-store/assets/js/main.js"></script>
    
    <?php if (isset($_SESSION['alert'])): ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
        <div class="toast show <?php echo $_SESSION['alert']['type'] === 'success' ? 'toast-success' : ($_SESSION['alert']['type'] === 'danger' ? 'toast-error' : 'toast-warning'); ?> border-0" role="alert">
            <div class="d-flex align-items-center px-3 py-2">
                <span style="font-size: 1.2rem; margin-right: 10px;">
                    <?php 
                        if ($_SESSION['alert']['type'] === 'success') echo '✓';
                        elseif ($_SESSION['alert']['type'] === 'danger') echo '✕';
                        else echo '⚠';
                    ?>
                </span>
                <div class="toast-body p-0">
                    <?php 
                        echo $_SESSION['alert']['message'];
                        unset($_SESSION['alert']);
                    ?>
                </div>
                <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>