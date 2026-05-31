<?php
/**
 * LaundryStoreID - Halaman Login
 * 
 * Tema: Violet + Cyan + Slate
 * Fitur: Login dengan email & password, validasi, redirect otomatis
 */

require_once '../config/db.php';

// ============================================
// REDIRECT JIKA SUDAH LOGIN
// ============================================
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('../admin/dashboard.php');
    } else {
        redirect('../user/dashboard.php');
    }
}

// ============================================
// PROSES LOGIN
// ============================================
$error = '';
$email_value = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password']; // Password tidak perlu sanitize karena akan diverifikasi
    $email_value = $email; // Simpan untuk mengisi ulang form
    
    // Validasi input
    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek user di database
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login berhasil - set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Regenerate session ID untuk keamanan
                session_regenerate_id(true);
                
                // Redirect berdasarkan role
                if ($user['role'] === 'admin') {
                    redirect('../admin/dashboard.php');
                } else {
                    redirect('../user/dashboard.php');
                }
            } else {
                $error = 'Email atau password salah!';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            // Log error untuk debugging (jangan tampilkan ke user)
            error_log('Login error: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Login ke LaundryStoreID - Toko Perlengkapan Laundry Terlengkap. Masuk ke akun Anda untuk berbelanja.">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#7C3AED">
    
    <title>Login - LaundryStoreID</title>
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🧺</text></svg>">
    
    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    
    <!-- Google Fonts - Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <!-- Inline Critical CSS -->
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0F172A 0%, #4C1D95 60%, #0891B2 100%);
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background particles */
        .bg-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }
        
        .particle {
            position: absolute;
            border-radius: 50%;
            animation: floatParticle 8s infinite ease-in-out;
        }
        
        @keyframes floatParticle {
            0%, 100% { transform: translateY(0) translateX(0) scale(1); opacity: 0.3; }
            25% { transform: translateY(-40px) translateX(20px) scale(1.2); opacity: 0.5; }
            50% { transform: translateY(-20px) translateX(-10px) scale(0.8); opacity: 0.2; }
            75% { transform: translateY(-60px) translateX(-30px) scale(1.1); opacity: 0.4; }
        }
        
        /* Glow effects */
        .glow-1, .glow-2, .glow-3 {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
        }
        
        .glow-1 {
            width: 500px;
            height: 500px;
            background: rgba(124, 58, 237, 0.3);
            top: -150px;
            right: -150px;
            animation: moveGlow1 10s ease-in-out infinite;
        }
        
        .glow-2 {
            width: 400px;
            height: 400px;
            background: rgba(6, 182, 212, 0.25);
            bottom: -100px;
            left: -100px;
            animation: moveGlow2 12s ease-in-out infinite;
        }
        
        .glow-3 {
            width: 300px;
            height: 300px;
            background: rgba(124, 58, 237, 0.2);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: moveGlow3 8s ease-in-out infinite;
        }
        
        @keyframes moveGlow1 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-30px, 30px); }
        }
        
        @keyframes moveGlow2 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(40px, -20px); }
        }
        
        @keyframes moveGlow3 {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(-50%, -50%) scale(1.3); }
        }
        
        /* Card Styles */
        .login-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Logo */
        .login-logo {
            font-size: 3rem;
            color: #7C3AED;
            text-align: center;
            margin-bottom: 8px;
        }
        
        .login-brand {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 4px;
        }
        
        .login-brand .brand-laundry {
            color: #7C3AED;
        }
        
        .login-brand .brand-store {
            color: #06B6D4;
        }
        
        .login-tagline {
            text-align: center;
            color: #64748B;
            font-size: 13px;
            margin-bottom: 28px;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label-custom {
            font-weight: 600;
            font-size: 13px;
            color: #1E293B;
            margin-bottom: 6px;
            display: block;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 16px;
            z-index: 2;
            pointer-events: none;
            transition: color 0.2s ease;
        }
        
        .input-wrapper input {
            width: 100%;
            height: 50px;
            padding: 0 44px 0 44px;
            border: 2px solid #E2E8F0;
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #FFFFFF;
            color: #1E293B;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: #7C3AED;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }
        
        .input-wrapper input:focus + .input-icon,
        .input-wrapper input:focus ~ .input-icon {
            color: #7C3AED;
        }
        
        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
            z-index: 2;
            transition: color 0.2s ease;
        }
        
        .password-toggle:hover {
            color: #7C3AED;
        }
        
        /* Submit Button */
        .btn-login {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, #7C3AED 0%, #6D28D9 50%, #06B6D4 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
            margin-bottom: 16px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(124, 58, 237, 0.4);
        }
        
        .btn-login:active {
            transform: scale(0.98);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Loading Spinner */
        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .btn-login.loading .spinner {
            display: inline-block;
        }
        
        .btn-login.loading .btn-text {
            display: none;
        }
        
        /* Register Link */
        .register-link {
            text-align: center;
            font-size: 14px;
            color: #64748B;
        }
        
        .register-link a {
            color: #7C3AED;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .register-link a:hover {
            color: #6D28D9;
            text-decoration: underline;
        }
        
        /* Alert */
        .alert-custom {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shakeAlert 0.4s ease;
        }
        
        @keyframes shakeAlert {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-6px); }
            40% { transform: translateX(6px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }
        
        .alert-custom .alert-icon {
            font-size: 18px;
            flex-shrink: 0;
        }
        
        /* Demo Account Info */
        .demo-info {
            margin-top: 16px;
            padding: 12px 16px;
            background: #F5F3FF;
            border-radius: 10px;
            border: 1px dashed #C4B5FD;
            font-size: 12px;
            color: #6D28D9;
            text-align: center;
        }
        
        .demo-info strong {
            display: block;
            margin-bottom: 4px;
            font-size: 13px;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .login-card {
                padding: 24px;
                margin: 16px;
                border-radius: 20px;
            }
            
            .login-brand {
                font-size: 1.5rem;
            }
            
            .btn-login {
                height: 48px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Background Effects -->
    <div class="glow-1"></div>
    <div class="glow-2"></div>
    <div class="glow-3"></div>
    
    <!-- Floating Particles -->
    <div class="bg-particles">
        <div class="particle" style="width: 6px; height: 6px; background: #A78BFA; top: 15%; left: 10%; animation-delay: 0s; animation-duration: 7s;"></div>
        <div class="particle" style="width: 4px; height: 4px; background: #22D3EE; top: 25%; right: 15%; animation-delay: 1s; animation-duration: 9s;"></div>
        <div class="particle" style="width: 8px; height: 8px; background: #7C3AED; bottom: 20%; left: 20%; animation-delay: 2s; animation-duration: 8s;"></div>
        <div class="particle" style="width: 5px; height: 5px; background: #06B6D4; top: 60%; right: 10%; animation-delay: 0.5s; animation-duration: 10s;"></div>
        <div class="particle" style="width: 3px; height: 3px; background: #A78BFA; bottom: 30%; right: 25%; animation-delay: 3s; animation-duration: 6s;"></div>
        <div class="particle" style="width: 7px; height: 7px; background: #22D3EE; top: 40%; left: 5%; animation-delay: 1.5s; animation-duration: 11s;"></div>
    </div>
    
    <!-- Login Card -->
    <div class="login-card">
        
        <!-- Logo -->
        <div class="login-logo">
            <i class="fas fa-jug-detergent"></i>
        </div>
        
        <!-- Brand -->
        <div class="login-brand">
            <span class="brand-laundry">Laundry</span><span class="brand-store">StoreID</span>
        </div>
        
        <!-- Tagline -->
        <p class="login-tagline">Toko Perlengkapan Laundry Terlengkap</p>
        
        <!-- Error Alert -->
        <?php if ($error): ?>
        <div class="alert-custom">
            <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST" action="" id="loginForm" novalidate>
            
            <!-- Email Field -->
            <div class="form-group">
                <label class="form-label-custom" for="email">
                    <i class="fas fa-envelope me-1" style="font-size: 12px;"></i> Email Address
                </label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-envelope"></i></span>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        placeholder="nama@email.com" 
                        value="<?php echo htmlspecialchars($email_value); ?>"
                        required 
                        autofocus
                        autocomplete="email"
                    >
                </div>
            </div>
            
            <!-- Password Field -->
            <div class="form-group">
                <label class="form-label-custom" for="password">
                    <i class="fas fa-lock me-1" style="font-size: 12px;"></i> Password
                </label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        placeholder="Masukkan password" 
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="password-toggle" id="togglePassword" title="Lihat password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="btn-login" id="loginButton">
                <span class="spinner"></span>
                <span class="btn-text">
                    <i class="fas fa-sign-in-alt me-2"></i> Masuk
                </span>
            </button>
            
            <!-- Register Link -->
            <div class="register-link">
                Belum punya akun? 
                <a href="register.php">Daftar Sekarang</a>
            </div>
        </form>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // ============================================
        // PASSWORD TOGGLE
        // ============================================
        const togglePasswordBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePasswordBtn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('title', 'Sembunyikan password');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('title', 'Lihat password');
            }
        });
        
        // ============================================
        // FORM SUBMIT - LOADING STATE
        // ============================================
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        
        loginForm.addEventListener('submit', function(e) {
            // Validasi sederhana
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                // Fokus ke field yang kosong
                if (!email) {
                    document.getElementById('email').focus();
                } else {
                    document.getElementById('password').focus();
                }
                return;
            }
            
            // Tampilkan loading state
            loginButton.classList.add('loading');
            loginButton.disabled = true;
        });
        
        // ============================================
        // FOCUS EFFECT PADA INPUT
        // ============================================
        document.querySelectorAll('.input-wrapper input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.input-icon').style.color = '#7C3AED';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('.input-icon').style.color = '#94A3B8';
            });
        });
        
        // ============================================
        // ENTER KEY TO SUBMIT
        // ============================================
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && document.activeElement.tagName === 'INPUT') {
                const form = document.activeElement.closest('form');
                if (form) {
                    form.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            }
        });
        
        // ============================================
        // AUTO FOCUS PADA EMAIL
        // ============================================
        window.addEventListener('load', function() {
            document.getElementById('email').focus();
        });
        
        // ============================================
        // PREVENT BACK BUTTON AFTER LOGIN
        // ============================================
        if (window.history && window.history.pushState) {
            window.history.pushState('forward', null, '');
            window.onpopstate = function() {
                window.history.pushState('forward', null, '');
            };
        }
    </script>
</body>
</html>