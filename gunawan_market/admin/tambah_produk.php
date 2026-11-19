<?php
require_once __DIR__.'/../config/koneksi.php';require_once __DIR__.'/../includes/admin_auth.php';require_once __DIR__.'/../includes/csrf.php';
$error='';$p=[];
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$name=trim($_POST['product_name']??'');$cat=(int)($_POST['category_id']??0);$desc=trim($_POST['description']??'');$price=filter_var($_POST['price']??null,FILTER_VALIDATE_FLOAT);$stock=filter_var($_POST['stock']??null,FILTER_VALIDATE_INT);$imageName=null;
if($name===''||$cat<1||$price===false||$price<0||$stock===false||$stock<0){$error='Periksa kembali nama, kategori, harga, dan stok.';}
else{
 $check=$conn->prepare("SELECT id FROM categories WHERE id=?");$check->bind_param("i",$cat);$check->execute();$catExists=$check->get_result()->num_rows>0;$check->close();
 if(!$catExists)$error='Kategori tidak ditemukan.';
 if(!$error&&!empty($_FILES['image']['name'])){$file=$_FILES['image'];if($file['error']!==UPLOAD_ERR_OK||$file['size']>2*1024*1024)$error='Upload gagal atau ukuran gambar melebihi 2 MB.';else{$info=@getimagesize($file['tmp_name']);$mime=$info['mime']??'';$allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];if(!$info||!isset($allowed[$mime]))$error='Format gambar harus JPG, PNG, atau WEBP.';else{$imageName=bin2hex(random_bytes(16)).'.'.$allowed[$mime];if(!move_uploaded_file($file['tmp_name'],__DIR__.'/../uploads/'.$imageName))$error='Gagal menyimpan gambar.';}}}
 if(!$error){$stmt=$conn->prepare("INSERT INTO products(category_id,product_name,description,price,stock,image) VALUES(?,?,?,?,?,?)");$stmt->bind_param("issdis",$cat,$name,$desc,$price,$stock,$imageName);if($stmt->execute()){$_SESSION['flash']='Produk berhasil ditambahkan.';header('Location: produk.php');exit;}else{$error='Gagal menyimpan produk.';if($imageName&&is_file(__DIR__.'/../uploads/'.$imageName))unlink(__DIR__.'/../uploads/'.$imageName);}$stmt->close();}
}}
$categories=$conn->query("SELECT id,category_name FROM categories ORDER BY category_name");$pageTitle='Tambah Produk';require_once __DIR__.'/layout.php';?>
<h1 class="h3 mb-3">Tambah Produk</h1><?php if($error):?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif;?>
<div class="card shadow-sm"><div class="card-body"><form method="post" enctype="multipart/form-data"><?php echo csrf_field();require __DIR__.'/form_produk.php';?><button class="btn btn-primary">Simpan Produk</button> <a class="btn btn-secondary" href="produk.php">Batal</a></form></div></div>
<?php require_once __DIR__.'/layout_footer.php';?>
