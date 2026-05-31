<?php
/**
 * LaundryStoreID - Halaman Registrasi
 * 
 * Tema: Violet + Cyan + Slate
 * Fitur: Registrasi user baru dengan validasi lengkap
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
// PROSES REGISTRASI
// ============================================
$error = '';
$success = '';

// Nilai default untuk form (dipertahankan jika error)
$form_data = [
    'nama' => '',
    'email' => '',
    'alamat' => '',
    'no_hp' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan sanitasi input
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $alamat = sanitize($_POST['alamat']);
    $no_hp = sanitize($_POST['no_hp']);
    
    // Simpan nilai form (kecuali password)
    $form_data = [
        'nama' => $nama,
        'email' => $email,
        'alamat' => $alamat,
        'no_hp' => $no_hp
    ];
    
    // ============================================
    // VALIDASI SERVER-SIDE
    // ============================================
    $errors = [];
    
    // Validasi nama
    if (empty($nama)) {
        $errors[] = 'Nama lengkap wajib diisi!';
    } elseif (strlen($nama) < 3) {
        $errors[] = 'Nama minimal 3 karakter!';
    } elseif (strlen($nama) > 100) {
        $errors[] = 'Nama maksimal 100 karakter!';
    }
    
    // Validasi email
    if (empty($email)) {
        $errors[] = 'Email wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid!';
    } elseif (strlen($email) > 150) {
        $errors[] = 'Email maksimal 150 karakter!';
    }
    
    // Validasi password
    if (empty($password)) {
        $errors[] = 'Password wajib diisi!';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter!';
    } elseif (strlen($password) > 50) {
        $errors[] = 'Password maksimal 50 karakter!';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password harus mengandung minimal 1 huruf besar!';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password harus mengandung minimal 1 angka!';
    }
    
    // Validasi konfirmasi password
    if (empty($konfirmasi_password)) {
        $errors[] = 'Konfirmasi password wajib diisi!';
    } elseif ($password !== $konfirmasi_password) {
        $errors[] = 'Konfirmasi password tidak cocok!';
    }
    
    // Validasi alamat
    if (empty($alamat)) {
        $errors[] = 'Alamat wajib diisi!';
    } elseif (strlen($alamat) < 10) {
        $errors[] = 'Alamat minimal 10 karakter!';
    }
    
    // Validasi no HP
    if (empty($no_hp)) {
        $errors[] = 'Nomor HP wajib diisi!';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $no_hp)) {
        $errors[] = 'Nomor HP harus berupa angka 10-15 digit!';
    }
    
    // Jika tidak ada error validasi
    if (empty($errors)) {
        try {
            // Cek apakah email sudah terdaftar
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Email sudah terdaftar! Silakan gunakan email lain atau <a href="login.php" style="color: #7C3AED; font-weight: 600;">login disini</a>.';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
                
                // Insert user baru
                $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role, alamat, no_hp) VALUES (?, ?, ?, 'user', ?, ?)");
                $stmt->execute([$nama, $email, $hashed_password, $alamat, $no_hp]);
                
                // Registrasi berhasil
                $success = 'Registrasi berhasil! Silakan <a href="login.php" style="color: #059669; font-weight: 700;">login disini</a> untuk melanjutkan.';
                
                // Reset form data
                $form_data = ['nama' => '', 'email' => '', 'alamat' => '', 'no_hp' => ''];
            }
        } catch (PDOException $e) {
            $errors[] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            // Log error untuk debugging
            error_log('Register error: ' . $e->getMessage());
        }
    }
    
    // Gabungkan semua error
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
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
    <meta name="description" content="Daftar akun LaundryStoreID - Toko Perlengkapan Laundry Terlengkap. Buat akun baru dan mulai berbelanja.">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#7C3AED">
    
    <title>Daftar - LaundryStoreID</title>
    
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
            overflow-x: hidden;
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
            background: rgba(16, 185, 129, 0.2);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: pulseGlow 4s ease-in-out infinite;
        }
        
        @keyframes moveGlow1 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-30px, 30px); }
        }
        
        @keyframes moveGlow2 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(40px, -20px); }
        }
        
        @keyframes pulseGlow {
            0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.6; }
            50% { transform: translate(-50%, -50%) scale(1.4); opacity: 1; }
        }
        
        /* Card Styles */
        .register-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
            padding: 36px 40px;
            width: 100%;
            max-width: 520px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            max-height: 90vh;
            overflow-y: auto;
            margin: 20px;
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
        
        /* Scrollbar Custom */
        .register-card::-webkit-scrollbar {
            width: 4px;
        }
        
        .register-card::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .register-card::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 2px;
        }
        
        /* Logo */
        .register-logo {
            font-size: 2.8rem;
            color: #7C3AED;
            text-align: center;
            margin-bottom: 8px;
        }
        
        .register-brand {
            text-align: center;
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 4px;
        }
        
        .register-brand .brand-laundry {
            color: #7C3AED;
        }
        
        .register-brand .brand-store {
            color: #06B6D4;
        }
        
        .register-tagline {
            text-align: center;
            color: #64748B;
            font-size: 13px;
            margin-bottom: 24px;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 16px;
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
            font-size: 15px;
            z-index: 2;
            pointer-events: none;
            transition: color 0.2s ease;
        }
        
        .input-wrapper textarea ~ .input-icon {
            top: 16px;
            transform: none;
        }
        
        .input-wrapper input,
        .input-wrapper textarea {
            width: 100%;
            padding: 0 14px 0 42px;
            border: 2px solid #E2E8F0;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #FFFFFF;
            color: #1E293B;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        
        .input-wrapper input {
            height: 48px;
        }
        
        .input-wrapper textarea {
            padding-top: 12px;
            padding-left: 42px;
            min-height: 80px;
            resize: vertical;
        }
        
        .input-wrapper input:focus,
        .input-wrapper textarea:focus {
            outline: none;
            border-color: #7C3AED;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }
        
        .input-wrapper input:focus ~ .input-icon,
        .input-wrapper textarea:focus ~ .input-icon {
            color: #7C3AED;
        }
        
        /* Password Strength Indicator */
        .password-strength {
            margin-top: 6px;
            height: 4px;
            border-radius: 2px;
            background: #E2E8F0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .password-strength-bar {
            height: 100%;
            border-radius: 2px;
            transition: all 0.3s ease;
            width: 0%;
        }
        
        .strength-weak { background: #EF4444; width: 33%; }
        .strength-medium { background: #F59E0B; width: 66%; }
        .strength-strong { background: #10B981; width: 100%; }
        
        .password-hint {
            font-size: 11px;
            color: #94A3B8;
            margin-top: 4px;
        }
        
        /* Submit Button */
        .btn-register {
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
            margin-top: 8px;
            margin-bottom: 16px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(124, 58, 237, 0.4);
        }
        
        .btn-register:active {
            transform: scale(0.98);
        }
        
        .btn-register:disabled {
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
        
        .btn-register.loading .spinner {
            display: inline-block;
        }
        
        .btn-register.loading .btn-text {
            display: none;
        }
        
        /* Login Link */
        .login-link {
            text-align: center;
            font-size: 14px;
            color: #64748B;
        }
        
        .login-link a {
            color: #7C3AED;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .login-link a:hover {
            color: #6D28D9;
            text-decoration: underline;
        }
        
        /* Alert */
        .alert-custom {
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            animation: shakeAlert 0.4s ease;
        }
        
        .alert-error {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
        }
        
        .alert-success {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #6EE7B7;
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
            margin-top: 1px;
        }
        
        /* Terms Text */
        .terms-text {
            text-align: center;
            font-size: 12px;
            color: #94A3B8;
            margin-bottom: 4px;
            line-height: 1.5;
        }
        
        .terms-text a {
            color: #7C3AED;
            text-decoration: none;
        }
        
        .terms-text a:hover {
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .register-card {
                padding: 24px 20px;
                margin: 12px;
                border-radius: 20px;
                max-height: 85vh;
            }
            
            .register-brand {
                font-size: 1.4rem;
            }
            
            .btn-register {
                height: 48px;
                font-size: 15px;
            }
            
            .row {
                flex-direction: column;
            }
            
            .col-half {
                width: 100% !important;
            }
        }
        
        /* Two column layout helper */
        .row {
            display: flex;
            gap: 16px;
        }
        
        .col-half {
            flex: 1;
            min-width: 0;
        }
        
        @media (max-width: 576px) {
            .row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Background Effects -->
    <div class="glow-1"></div>
    <div class="glow-2"></div>
    <div class="glow-3"></div>
    
    <!-- Register Card -->
    <div class="register-card">
        
        <!-- Logo -->
        <div class="register-logo">
            <i class="fas fa-jug-detergent"></i>
        </div>
        
        <!-- Brand -->
        <div class="register-brand">
            <span class="brand-laundry">Laundry</span><span class="brand-store">StoreID</span>
        </div>
        
        <!-- Tagline -->
        <p class="register-tagline">Buat akun baru dan mulai berbelanja</p>
        <!-- Error Alert -->
        <?php if ($error): ?>
        <div class="alert-custom alert-error">
            <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Success Alert -->
        <?php if ($success): ?>
        <div class="alert-custom alert-success">
            <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
            <span><?php echo $success; ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Register Form -->
        <?php if (!$success): ?>
        <form method="POST" action="" id="registerForm" novalidate>
            
            <!-- Nama Lengkap -->
            <div class="form-group">
                <label class="form-label-custom" for="nama">
                    <i class="fas fa-user me-1" style="font-size: 12px;"></i> Nama Lengkap
                </label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-user"></i></span>
                    <input 
                        type="text" 
                        name="nama" 
                        id="nama" 
                        placeholder="Masukkan nama lengkap" 
                        value="<?php echo htmlspecialchars($form_data['nama']); ?>"
                        required 
                        autofocus
                        minlength="3"
                        maxlength="100"
                    >
                </div>
            </div>
            
            <!-- Email -->
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
                        value="<?php echo htmlspecialchars($form_data['email']); ?>"
                        required
                        autocomplete="email"
                    >
                </div>
            </div>
            
            <!-- Password & Konfirmasi (2 Kolom) -->
            <div class="row">
                <div class="col-half">
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
                                placeholder="Minimal 6 karakter" 
                                required
                                minlength="6"
                                autocomplete="new-password"
                            >
                        </div>
                        <!-- Password Strength -->
                        <div class="password-strength" id="passwordStrength" style="display: none;">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>
                        <div class="password-hint" id="passwordHint"></div>
                    </div>
                </div>
                
                <div class="col-half">
                    <div class="form-group">
                        <label class="form-label-custom" for="konfirmasi_password">
                            <i class="fas fa-check-circle me-1" style="font-size: 12px;"></i> Konfirmasi Password
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon"><i class="fas fa-check-circle"></i></span>
                            <input 
                                type="password" 
                                name="konfirmasi_password" 
                                id="konfirmasi_password" 
                                placeholder="Ulangi password" 
                                required
                                autocomplete="new-password"
                            >
                        </div>
                        <div class="password-hint" id="passwordMatchHint"></div>
                    </div>
                </div>
            </div>
            
            <!-- Alamat -->
            <div class="form-group">
                <label class="form-label-custom" for="alamat">
                    <i class="fas fa-map-marker-alt me-1" style="font-size: 12px;"></i> Alamat Lengkap
                </label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-map-marker-alt"></i></span>
                    <textarea 
                        name="alamat" 
                        id="alamat" 
                        placeholder="Masukkan alamat lengkap pengiriman" 
                        required
                        minlength="10"
                    ><?php echo htmlspecialchars($form_data['alamat']); ?></textarea>
                </div>
            </div>
            
            <!-- No HP -->
            <div class="form-group">
                <label class="form-label-custom" for="no_hp">
                    <i class="fas fa-phone me-1" style="font-size: 12px;"></i> Nomor HP
                </label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-phone"></i></span>
                    <input 
                        type="tel" 
                        name="no_hp" 
                        id="no_hp" 
                        placeholder="Contoh: 08123456789" 
                        value="<?php echo htmlspecialchars($form_data['no_hp']); ?>"
                        required
                        pattern="[0-9]{10,15}"
                    >
                </div>
            </div>
            
            <!-- Terms -->
            <p class="terms-text">
                Dengan mendaftar, Anda menyetujui <a href="#">Syarat & Ketentuan</a> dan 
                <a href="#">Kebijakan Privasi</a> kami.
            </p>
            
            <!-- Submit Button -->
            <button type="submit" class="btn-register" id="registerButton">
                <span class="spinner"></span>
                <span class="btn-text">
                    <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                </span>
            </button>
            
            <!-- Login Link -->
            <div class="login-link">
                Sudah punya akun? 
                <a href="login.php">Login Disini</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // ============================================
        // PASSWORD STRENGTH CHECKER
        // ============================================
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const passwordHint = document.getElementById('passwordHint');
        const konfirmasiPassword = document.getElementById('konfirmasi_password');
        const passwordMatchHint = document.getElementById('passwordMatchHint');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
                if (password.length > 0) {
                    passwordStrength.style.display = 'block';
                    
                    let strength = 0;
                    let hint = '';
                    let className = '';
                    
                    // Check criteria
                    const hasLength = password.length >= 6;
                    const hasUpperCase = /[A-Z]/.test(password);
                    const hasNumber = /[0-9]/.test(password);
                    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
                    
                    if (hasLength) strength++;
                    if (hasUpperCase) strength++;
                    if (hasNumber) strength++;
                    if (hasSpecialChar) strength++;
                    
                    // Determine strength level
                    if (strength <= 1) {
                        className = 'strength-weak';
                        hint = '⚠️ Password lemah. ';
                        if (!hasLength) hint += 'Minimal 6 karakter. ';
                        if (!hasUpperCase) hint += 'Tambahkan huruf besar. ';
                    } else if (strength <= 2) {
                        className = 'strength-medium';
                        hint = '🔶 Password sedang. ';
                        if (!hasNumber) hint += 'Tambahkan angka. ';
                        if (!hasSpecialChar) hint += 'Tambahkan karakter spesial. ';
                    } else if (strength >= 3) {
                        className = 'strength-strong';
                        hint = '✅ Password kuat!';
                    }
                    
                    // Update UI
                    passwordStrengthBar.className = 'password-strength-bar ' + className;
                    passwordHint.textContent = hint;
                } else {
                    passwordStrength.style.display = 'none';
                    passwordHint.textContent = '';
                }
                
                // Check password match
                checkPasswordMatch();
            });
        }
        
        // ============================================
        // PASSWORD MATCH CHECKER
        // ============================================
        if (konfirmasiPassword) {
            konfirmasiPassword.addEventListener('input', checkPasswordMatch);
        }
        
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirm = konfirmasiPassword.value;
            
            if (confirm.length > 0) {
                if (password === confirm) {
                    passwordMatchHint.textContent = '✅ Password cocok!';
                    passwordMatchHint.style.color = '#10B981';
                } else {
                    passwordMatchHint.textContent = '❌ Password tidak cocok!';
                    passwordMatchHint.style.color = '#EF4444';
                }
            } else {
                passwordMatchHint.textContent = '';
            }
        }
        
        // ============================================
        // FOCUS EFFECT PADA INPUT
        // ============================================
        document.querySelectorAll('.input-wrapper input, .input-wrapper textarea').forEach(input => {
            input.addEventListener('focus', function() {
                const icon = this.parentElement.querySelector('.input-icon');
                if (icon) icon.style.color = '#7C3AED';
            });
            
            input.addEventListener('blur', function() {
                const icon = this.parentElement.querySelector('.input-icon');
                if (icon) icon.style.color = '#94A3B8';
            });
        });
        
        // ============================================
        // FORM SUBMIT - LOADING STATE
        // ============================================
        const registerForm = document.getElementById('registerForm');
        const registerButton = document.getElementById('registerButton');
        
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                // Cek password match
                const password = passwordInput.value;
                const confirm = konfirmasiPassword.value;
                
                if (password !== confirm) {
                    e.preventDefault();
                    passwordMatchHint.textContent = '❌ Password tidak cocok!';
                    passwordMatchHint.style.color = '#EF4444';
                    konfirmasiPassword.focus();
                    return;
                }
                
                // Tampilkan loading state
                registerButton.classList.add('loading');
                registerButton.disabled = true;
            });
        }
        
        // ============================================
        // AUTO FOCUS
        // ============================================
        window.addEventListener('load', function() {
            const namaInput = document.getElementById('nama');
            if (namaInput && !namaInput.value) {
                namaInput.focus();
            }
        });
        
        // ============================================
        // PREVENT BACK BUTTON
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