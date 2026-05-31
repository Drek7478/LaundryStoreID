<?php
require_once '../../config/db.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$id = (int)$_GET['id'];
$status = sanitize($_GET['status']);

$allowed_status = ['dikonfirmasi', 'selesai', 'dibatalkan'];

if (in_array($status, $allowed_status)) {
    $stmt = $pdo->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    setAlert('success', 'Status pesanan berhasil diupdate!');
}

redirect('detail.php?id=' . $id);
?>