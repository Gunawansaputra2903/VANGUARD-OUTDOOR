<?php
require_once __DIR__.'/../config/koneksi.php';require_once __DIR__.'/../includes/admin_auth.php';require_once __DIR__.'/../includes/csrf.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Metode tidak diizinkan.');}
verify_csrf();$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);if(!$id||$id<1){header('Location: produk.php');exit;}
$get=$conn->prepare("SELECT image FROM products WHERE id=?");$get->bind_param("i",$id);$get->execute();$row=$get->get_result()->fetch_assoc();$get->close();
if(!$row){$_SESSION['flash']='Produk tidak ditemukan.';header('Location: produk.php');exit;}
$del=$conn->prepare("DELETE FROM products WHERE id=?");$del->bind_param("i",$id);
if($del->execute()){if($row['image']&&is_file(__DIR__.'/../uploads/'.$row['image']))unlink(__DIR__.'/../uploads/'.$row['image']);$_SESSION['flash']='Produk berhasil dihapus.';}else{$_SESSION['flash']='Produk tidak dapat dihapus karena sudah digunakan dalam keranjang atau transaksi.';}
$del->close();header('Location: produk.php');exit;
