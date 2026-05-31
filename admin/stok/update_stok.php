<?php
require_once '../../config/db.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = (int)$_POST['id_produk'];
    $stok = (int)$_POST['stok'];
    
    $stmt = $pdo->prepare("UPDATE produk SET stok = ? WHERE id = ?");
    $stmt->execute([$stok, $id_produk]);
    
    setAlert('success', 'Stok berhasil diupdate!');
}

redirect('../produk/index.php');
?>