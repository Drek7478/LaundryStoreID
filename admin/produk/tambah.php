<?php
require_once '../../config/db.php';
include '../../includes/header.php';
include '../../includes/navbar_admin.php';

// Ambil kategori
$stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori");
$kategori_list = $stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = sanitize($_POST['nama_produk']);
    $deskripsi = sanitize($_POST['deskripsi']);
    $harga = (float)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $satuan = sanitize($_POST['satuan']);
    $id_kategori = $_POST['id_kategori'] ? (int)$_POST['id_kategori'] : null;
    
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['gambar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $error = 'Format gambar tidak diizinkan! (jpg, jpeg, png, gif)';
        } elseif ($_FILES['gambar']['size'] > 2 * 1024 * 1024) {
            $error = 'Ukuran gambar maksimal 2MB!';
        } else {
            $gambar = 'produk_' . time() . '.' . $ext;
            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], '../../assets/img/produk/' . $gambar)) {
                $error = 'Gagal upload gambar!';
            }
        }
    }
    
    if (empty($error)) {
        $stmt = $pdo->prepare("INSERT INTO produk (nama_produk, deskripsi, harga, stok, satuan, gambar, id_kategori) VALUES (?, ?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$nama_produk, $deskripsi, $harga, $stok, $satuan, $gambar, $id_kategori]);
            setAlert('success', 'Produk berhasil ditambahkan!');
            redirect('index.php');
        } catch (PDOException $e) {
            $error = 'Gagal menambahkan produk: ' . $e->getMessage();
        }
    }
}
?>

<!-- ============================================ -->
<!-- KONTEN TAMBAH PRODUK                        -->
<!-- ============================================ -->

<div class="mb-4">
    <a href="index.php" class="btn btn-outline btn-sm mb-3" style="border-radius: 8px;">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Produk
    </a>
    <h2 class="fw-800 mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i> Tambah Produk Baru</h2>
    <p class="text-muted">Isi form di bawah untuk menambahkan produk baru</p>
</div>

<?php if ($error): ?>
<div class="alert alert-danger" style="border-radius: 10px;"><i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="card" style="border-radius: var(--radius-lg); max-width: 800px;">
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Nama Produk *</label>
                    <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Deterjen Cair Rinso" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi produk..."></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Harga *</label>
                    <input type="number" name="harga" class="form-control" placeholder="0" min="0" step="100" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Stok *</label>
                    <input type="number" name="stok" class="form-control stock-indicator" placeholder="0" min="0" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Satuan *</label>
                    <input type="text" name="satuan" class="form-control" placeholder="botol, kg, pcs" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kategori</label>
                    <select name="id_kategori" class="form-select">
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($kategori_list as $kat): ?>
                        <option value="<?php echo $kat['id']; ?>"><?php echo $kat['nama_kategori']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Gambar Produk</label>
                    <input type="file" name="gambar" class="form-control image-upload-input" accept="image/*" data-preview="previewImage">
                    <img id="previewImage" src="#" style="max-width: 200px; margin-top: 10px; border-radius: 10px; display: none;">
                </div>
                <div class="col-12">
                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" style="border-radius: 10px;"><i class="fas fa-save me-1"></i> Simpan Produk</button>
                        <a href="index.php" class="btn btn-outline" style="border-radius: 10px;">Batal</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ============================================ -->
<!-- PENUTUP DIV (WAJIB!)                         -->
<!-- ============================================ -->
</div> <!-- Tutup div.p-4 -->
</div> <!-- Tutup div.admin-main -->

<?php include '../../includes/footer.php'; ?>