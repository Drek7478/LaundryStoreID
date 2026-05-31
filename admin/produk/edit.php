<?php
require_once '../../config/db.php';
include '../../includes/header.php';
include '../../includes/navbar_admin.php';

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->execute([$id]);
$produk = $stmt->fetch();

if (!$produk) {
    redirect('index.php');
}

$stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori");
$kategori_list = $stmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = sanitize($_POST['nama_produk']);
    $deskripsi = sanitize($_POST['deskripsi']);
    $harga = (float)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $satuan = sanitize($_POST['satuan']);
    $id_kategori = $_POST['id_kategori'] ? (int)$_POST['id_kategori'] : null;
    
    $gambar = $produk['gambar'];
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['gambar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $_FILES['gambar']['size'] <= 2 * 1024 * 1024) {
            $gambar = 'produk_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['gambar']['tmp_name'], '../../assets/img/produk/' . $gambar);
        }
    }
    
    $stmt = $pdo->prepare("UPDATE produk SET nama_produk=?, deskripsi=?, harga=?, stok=?, satuan=?, gambar=?, id_kategori=? WHERE id=?");
    try {
        $stmt->execute([$nama_produk, $deskripsi, $harga, $stok, $satuan, $gambar, $id_kategori, $id]);
        setAlert('success', 'Produk berhasil diupdate!');
        redirect('index.php');
    } catch (PDOException $e) {
        $error = 'Gagal update produk: ' . $e->getMessage();
    }
}
?>

<!-- ============================================ -->
<!-- KONTEN EDIT PRODUK                          -->
<!-- ============================================ -->

<div class="mb-4">
    <a href="index.php" class="btn btn-outline btn-sm mb-3" style="border-radius: 8px;">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Produk
    </a>
    <h2 class="fw-800 mb-1"><i class="fas fa-edit me-2 text-primary"></i> Edit Produk</h2>
    <p class="text-muted">Edit informasi produk "<?php echo $produk['nama_produk']; ?>"</p>
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
                    <input type="text" name="nama_produk" class="form-control" value="<?php echo $produk['nama_produk']; ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"><?php echo $produk['deskripsi']; ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Harga *</label>
                    <input type="number" name="harga" class="form-control" value="<?php echo $produk['harga']; ?>" min="0" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Stok *</label>
                    <input type="number" name="stok" class="form-control stock-indicator" value="<?php echo $produk['stok']; ?>" min="0" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Satuan *</label>
                    <input type="text" name="satuan" class="form-control" value="<?php echo $produk['satuan']; ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kategori</label>
                    <select name="id_kategori" class="form-select">
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($kategori_list as $kat): ?>
                        <option value="<?php echo $kat['id']; ?>" <?php echo $produk['id_kategori'] == $kat['id'] ? 'selected' : ''; ?>>
                            <?php echo $kat['nama_kategori']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Gambar Produk</label>
                    <input type="file" name="gambar" class="form-control image-upload-input" accept="image/*" data-preview="previewImage">
                    <?php if ($produk['gambar']): ?>
                    <img id="previewImage" src="../../assets/img/produk/<?php echo $produk['gambar']; ?>" style="max-width: 200px; margin-top: 10px; border-radius: 10px;">
                    <?php else: ?>
                    <img id="previewImage" src="#" style="max-width: 200px; margin-top: 10px; border-radius: 10px; display: none;">
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" style="border-radius: 10px;"><i class="fas fa-save me-1"></i> Update Produk</button>
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