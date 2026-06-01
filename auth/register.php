<?php

/**
 * LaundryStoreID - Halaman Registrasi (Neubrutalism Redesign)
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

$form_data = [
    'nama' => '',
    'email' => '',
    'alamat' => '',
    'no_hp' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $alamat = sanitize($_POST['alamat']);
    $no_hp = sanitize($_POST['no_hp']);

    $form_data = [
        'nama' => $nama,
        'email' => $email,
        'alamat' => $alamat,
        'no_hp' => $no_hp
    ];

    $errors = [];

    // Validasi
    if (empty($nama)) $errors[] = 'Nama lengkap wajib diisi!';
    elseif (strlen($nama) < 3) $errors[] = 'Nama minimal 3 karakter!';
    elseif (strlen($nama) > 100) $errors[] = 'Nama maksimal 100 karakter!';

    if (empty($email)) $errors[] = 'Email wajib diisi!';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid!';
    elseif (strlen($email) > 150) $errors[] = 'Email maksimal 150 karakter!';

    if (empty($password)) $errors[] = 'Password wajib diisi!';
    elseif (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter!';
    elseif (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password harus mengandung minimal 1 huruf besar!';
    elseif (!preg_match('/[0-9]/', $password)) $errors[] = 'Password harus mengandung minimal 1 angka!';

    if (empty($konfirmasi_password)) $errors[] = 'Konfirmasi password wajib diisi!';
    elseif ($password !== $konfirmasi_password) $errors[] = 'Konfirmasi password tidak cocok!';

    if (empty($alamat)) $errors[] = 'Alamat wajib diisi!';
    elseif (strlen($alamat) < 10) $errors[] = 'Alamat minimal 10 karakter!';

    if (empty($no_hp)) $errors[] = 'Nomor HP wajib diisi!';
    elseif (!preg_match('/^[0-9]{10,15}$/', $no_hp)) $errors[] = 'Nomor HP harus berupa angka 10-15 digit!';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $errors[] = 'Email sudah terdaftar! Silakan gunakan email lain atau <a href="login.php">login disini</a>.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
                $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role, alamat, no_hp) VALUES (?, ?, ?, 'user', ?, ?)");
                $stmt->execute([$nama, $email, $hashed_password, $alamat, $no_hp]);

                $success = 'Registrasi berhasil! Silakan <a href="login.php">login disini</a>.';
                $form_data = ['nama' => '', 'email' => '', 'alamat' => '', 'no_hp' => ''];
            }
        } catch (PDOException $e) {
            $errors[] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            error_log('Register error: ' . $e->getMessage());
        }
    }

    if (!empty($errors)) $error = implode('<br>', $errors);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - LaundryStoreID</title>

    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🧺</text></svg>">

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">

    <style>
        .auth-layout {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        .auth-card {
            width: 100%;
            max-width: 600px;
            margin: 0;
            padding: 40px;
            background: var(--color-bg);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .auth-header h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }

        /* Custom Grid Form */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-grid.full {
            grid-column: 1 / -1;
        }

        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Password Strength Neubrutalism */
        .password-strength {
            margin-top: 8px;
            height: 8px;
            border: 2px solid var(--color-black);
            background: var(--color-bg);
            border-radius: 0;
            overflow: hidden;
            display: none;
        }

        .password-strength-bar {
            height: 100%;
            transition: width 0.2s ease, background 0.2s ease;
            width: 0%;
            border-right: 2px solid var(--color-black);
        }

        .strength-weak {
            background: var(--color-danger);
            width: 33%;
        }

        .strength-medium {
            background: var(--color-accent);
            width: 66%;
        }

        .strength-strong {
            background: var(--color-success);
            width: 100%;
            border-right: none;
        }

        .password-hint {
            font-size: 13px;
            font-weight: 600;
            margin-top: 4px;
        }

        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 3px solid var(--color-black);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .btn.loading .spinner {
            display: inline-block;
        }

        .btn.loading .btn-text {
            display: none;
        }
    </style>
</head>

<body>

    <div class="auth-layout">
        <div class="card auth-card card-accent-blue">

            <div class="auth-header">
                <i class="ph ph-user-plus" style="font-size: 48px; margin-bottom:16px;"></i>
                <h1>Buat Akun Baru</h1>
                <p>Bergabung dan rasakan mudahnya laundry online.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-custom alert-error">
                    <i class="ph ph-warning-circle" style="font-size: 24px;"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert-custom alert-success">
                    <i class="ph ph-check-circle" style="font-size: 24px;"></i>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" action="" id="registerForm" novalidate>

                    <div class="form-grid">
                        <!-- Nama Lengkap -->
                        <div class="form-group">
                            <label class="form-label" for="nama">Nama Lengkap</label>
                            <div class="input-wrapper">
                                <i class="ph ph-user"></i>
                                <input type="text" name="nama" id="nama" class="form-control" placeholder="Nama Anda" value="<?php echo htmlspecialchars($form_data['nama']); ?>" required autofocus>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label class="form-label" for="email">Alamat Email</label>
                            <div class="input-wrapper">
                                <i class="ph ph-envelope-simple"></i>
                                <input type="email" name="email" id="email" class="form-control" placeholder="nama@email.com" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-wrapper">
                                <i class="ph ph-lock-key"></i>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Min. 6 karakter" required>
                            </div>
                            <div class="password-strength" id="passwordStrength">
                                <div class="password-strength-bar" id="passwordStrengthBar"></div>
                            </div>
                            <div class="password-hint" id="passwordHint"></div>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="form-group">
                            <label class="form-label" for="konfirmasi_password">Konfirmasi Password</label>
                            <div class="input-wrapper">
                                <i class="ph ph-check-square-offset"></i>
                                <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" placeholder="Ulangi password" required>
                            </div>
                            <div class="password-hint" id="passwordMatchHint"></div>
                        </div>

                        <!-- No HP -->
                        <div class="form-group full">
                            <label class="form-label" for="no_hp">Nomor HP</label>
                            <div class="input-wrapper">
                                <i class="ph ph-phone"></i>
                                <input type="tel" name="no_hp" id="no_hp" class="form-control" placeholder="Contoh: 08123456789" value="<?php echo htmlspecialchars($form_data['no_hp']); ?>" required>
                            </div>
                        </div>

                        <!-- Alamat -->
                        <div class="form-group full">
                            <label class="form-label" for="alamat">Alamat Lengkap</label>
                            <div class="input-wrapper">
                                <i class="ph ph-map-pin"></i>
                                <textarea name="alamat" id="alamat" class="form-control" placeholder="Masukkan alamat pengiriman" required style="min-height: 80px; padding-top:12px;"><?php echo htmlspecialchars($form_data['alamat']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 8px; margin-bottom: 24px;" id="registerButton">
                        <span class="spinner"></span>
                        <span class="btn-text">Daftar Sekarang <i class="ph ph-arrow-right fw-bold"></i></span>
                    </button>

                    <div style="text-align: center; font-weight: 500;">
                        Sudah punya akun? <a href="login.php" style="color: var(--color-primary);">Masuk Disini</a>
                    </div>
                </form>
            <?php endif; ?>

        </div>
    </div>

    <script>
        // Password Strength
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

                    const hasLength = password.length >= 6;
                    const hasUpperCase = /[A-Z]/.test(password);
                    const hasNumber = /[0-9]/.test(password);
                    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

                    if (hasLength) strength++;
                    if (hasUpperCase) strength++;
                    if (hasNumber) strength++;
                    if (hasSpecialChar) strength++;

                    if (strength <= 1) {
                        className = 'strength-weak';
                        hint = 'Lemah! Min 6 kar + Huruf Besar.';
                    } else if (strength <= 2) {
                        className = 'strength-medium';
                        hint = 'Sedang. Tambah angka/karakter khusus.';
                    } else {
                        className = 'strength-strong';
                        hint = 'Kuat!';
                    }

                    passwordStrengthBar.className = 'password-strength-bar ' + className;
                    passwordHint.textContent = hint;
                    passwordHint.style.color = (strength >= 3) ? 'var(--color-success)' : 'var(--color-black)';
                } else {
                    passwordStrength.style.display = 'none';
                    passwordHint.textContent = '';
                }
                checkPasswordMatch();
            });
        }

        if (konfirmasiPassword) {
            konfirmasiPassword.addEventListener('input', checkPasswordMatch);
        }

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirm = konfirmasiPassword.value;
            if (confirm.length > 0) {
                if (password === confirm) {
                    passwordMatchHint.textContent = 'Cocok!';
                    passwordMatchHint.style.color = 'var(--color-success)';
                } else {
                    passwordMatchHint.textContent = 'Tidak cocok!';
                    passwordMatchHint.style.color = 'var(--color-danger)';
                }
            } else {
                passwordMatchHint.textContent = '';
            }
        }

        const registerForm = document.getElementById('registerForm');
        const registerButton = document.getElementById('registerButton');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                if (passwordInput.value !== konfirmasiPassword.value) {
                    e.preventDefault();
                    konfirmasiPassword.focus();
                    return;
                }
                registerButton.classList.add('loading');
            });
        }
    </script>
</body>

</html>