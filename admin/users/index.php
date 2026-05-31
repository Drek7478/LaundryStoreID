<?php
require_once '../../config/db.php';
include '../../includes/header.php';
include '../../includes/navbar_admin.php';

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$user_list = $stmt->fetchAll();
?>

<!-- ============================================ -->
<!-- KONTEN MANAJEMEN USERS                      -->
<!-- ============================================ -->

<div class="mb-4">
    <h2 class="fw-800 mb-1"><i class="fas fa-users me-2 text-primary"></i> Manajemen Users</h2>
    <p class="text-muted">Daftar semua pengguna terdaftar</p>
</div>

<div class="card" style="border-radius: var(--radius-lg); overflow: hidden;">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>No. HP</th>
                    <th>Alamat</th>
                    <th>Tanggal Daftar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($user_list) > 0): ?>
                    <?php foreach ($user_list as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm" style="background: <?php echo $user['role'] == 'admin' ? 'linear-gradient(135deg, #DC2626, #EF4444)' : 'var(--gradient-primary)'; ?>;">
                                    <?php echo strtoupper(substr($user['nama'], 0, 1)); ?>
                                </div>
                                <strong><?php echo $user['nama']; ?></strong>
                            </div>
                        </td>
                        <td><?php echo $user['email']; ?></td>
                        <td>
                            <span class="badge" style="background: <?php echo $user['role'] == 'admin' ? '#FEE2E2' : 'var(--color-primary-light)'; ?>; color: <?php echo $user['role'] == 'admin' ? '#991B1B' : 'var(--color-primary)'; ?>; padding: 4px 10px; border-radius: 100px; font-size: 12px;">
                                <i class="fas fa-<?php echo $user['role'] == 'admin' ? 'shield-alt' : 'user'; ?> me-1"></i>
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo $user['no_hp']; ?></td>
                        <td><small><?php echo substr($user['alamat'], 0, 50) . '...'; ?></small></td>
                        <td><small><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-users-slash me-2"></i> Belum ada user terdaftar
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================ -->
<!-- PENUTUP DIV (WAJIB!)                         -->
<!-- ============================================ -->
</div> <!-- Tutup div.p-4 -->
</div> <!-- Tutup div.admin-main -->

<?php include '../../includes/footer.php'; ?>