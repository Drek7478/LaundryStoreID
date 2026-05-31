<?php
/**
 * LaundryStoreID - Database Configuration
 * 
 * Koneksi PDO + Helper Functions
 */

// ============================================
// ERROR REPORTING (Development)
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 0); // Jangan tampilkan error ke user
ini_set('log_errors', 1); // Log error ke file

// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'laundry_store_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
} catch (PDOException $e) {
    // Log error dan tampilkan pesan user-friendly
    error_log('Database connection failed: ' . $e->getMessage());
    die("Koneksi database gagal. Silakan coba beberapa saat lagi.");
}

// ============================================
// SESSION - HARUS DIMULAI SEBELUM OUTPUT APAPUN
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    // Konfigurasi session
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    
    session_start();
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Cek apakah user sudah login
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Cek apakah user adalah admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirect ke URL tertentu
 * Menggunakan output buffering untuk mencegah error "headers already sent"
 * 
 * @param string $url URL tujuan
 * @return void
 */
function redirect($url) {
    // Jika headers sudah terkirim, gunakan JavaScript redirect
    if (headers_sent()) {
        echo '<script>window.location.href = "' . addslashes($url) . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url) . '"></noscript>';
        echo '<p>Redirecting... <a href="' . htmlspecialchars($url) . '">Click here</a> if not redirected.</p>';
        exit();
    }
    
    // Jika headers belum terkirim, gunakan PHP header()
    header("Location: $url");
    exit();
}

/**
 * Sanitasi input
 * @param string $input
 * @return string
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Set alert untuk ditampilkan di halaman berikutnya
 * @param string $type success|danger|warning|info
 * @param string $message Pesan alert
 * @return void
 */
function setAlert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get alert dan hapus dari session
 * @return array|null
 */
function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

/**
 * Generate CSRF token untuk keamanan form
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validasi CSRF token
 * @param string $token Token dari form
 * @return bool
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash message (one-time message)
 * @param string $key
 * @param mixed $value
 * @return void
 */
function flash($key, $value = null) {
    if ($value !== null) {
        $_SESSION['flash_' . $key] = $value;
    } else {
        $value = $_SESSION['flash_' . $key] ?? null;
        unset($_SESSION['flash_' . $key]);
        return $value;
    }
}

/**
 * Log aktivitas (opsional)
 * @param string $action
 * @param string $description
 * @return void
 */
function logActivity($action, $description = '') {
    $user_id = $_SESSION['user_id'] ?? 0;
    $user_name = $_SESSION['nama'] ?? 'Guest';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_message = "[$timestamp] User: $user_name (ID: $user_id) | IP: $ip | Action: $action | $description" . PHP_EOL;
    
    // Simpan ke file log
    $log_file = __DIR__ . '/../logs/activity.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

/**
 * Debug function (hanya untuk development)
 * @param mixed $data
 * @return void
 */
function debug($data) {
    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        echo '<pre style="background: #1E293B; color: #10B981; padding: 16px; border-radius: 8px; font-size: 13px; overflow: auto; max-height: 400px;">';
        print_r($data);
        echo '</pre>';
    }
}

/**
 * Get base URL
 * @return string
 */
function baseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . $script . '/';
}
?>