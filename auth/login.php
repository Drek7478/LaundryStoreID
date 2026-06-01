<?php

/**
 * LaundryStoreID - Halaman Login (Neubrutalism Redesign)
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
    $password = $_POST['password'];
    $email_value = $email;

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
            error_log('Login error: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LaundryStoreID</title>

    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🧺</text></svg>">

    <!-- Phosphor Icons (Sesuai UI-UX.md) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">

    <style>
        .auth-layout {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            margin: 0;
            padding: 40px;
            background: var(--color-accent);
            /* Aksen kuning terang untuk login */
        }

        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .auth-header i {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .auth-header h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .btn-toggle-pass {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: var(--color-black);
            padding: 0;
            z-index: 5;
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
                <i class="ph ph-washing-machine"></i>
                <h1>Masuk Sistem</h1>
                <p>Silakan login untuk mulai mengelola laundry.</p>
            </div>

            <!-- Error Alert -->
            <?php if ($error): ?>
                <div class="alert-custom alert-error">
                    <i class="ph ph-warning-circle" style="font-size: 24px;"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm" novalidate>

                <!-- Email Field -->
                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email</label>
                    <div class="input-wrapper">
                        <i class="ph ph-envelope-simple"></i>
                        <input type="email" name="email" id="email" class="form-control" placeholder="nama@email.com" value="<?php echo htmlspecialchars($email_value); ?>" required autofocus>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group mb-4">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="ph ph-lock-key"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                        <button type="button" class="btn-toggle-pass" id="togglePassword">
                            <i class="ph ph-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-bottom: 24px;" id="loginButton">
                    <span class="spinner"></span>
                    <span class="btn-text">Masuk Sekarang <i class="ph ph-arrow-right fw-bold"></i></span>
                </button>

                <div style="text-align: center; font-weight: 500;">
                    Belum punya akun? <a href="register.php" style="color: var(--color-primary);">Daftar Disini</a>
                </div>
            </form>

        </div>
    </div>

    <script>
        // Password Toggle
        const togglePasswordBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePasswordBtn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('ph-eye');
                icon.classList.add('ph-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('ph-eye-slash');
                icon.classList.add('ph-eye');
            }
        });

        // Loading State Submit
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');

        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            if (!email || !password) return;
            loginButton.classList.add('loading');
        });
    </script>
</body>

</html>