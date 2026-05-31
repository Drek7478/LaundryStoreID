<?php
require_once '../../config/db.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("DELETE FROM produk WHERE id = ?");
$stmt->execute([$id]);

setAlert('success', 'Produk berhasil dihapus!');
redirect('index.php');
?>