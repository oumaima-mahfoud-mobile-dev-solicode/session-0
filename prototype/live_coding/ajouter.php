<?php
require_once'connexion.php';
if($_SERVER['REQUEST_METHOD']=='POST') {
    $name = isset($_POST['name']) ?$_POST['name'] : '';
    $description = isset($_POST['description']) ?$_POST['description'] : '';
    $image = isset($_FILES['image']) ?$_FILES['image'] : '';
    $collection = isset($_POST['collection']) ?$_POST['collection'] : '';
    $price = isset($_POST['price']) ?$_POST['price'] : '';
    $stock = isset($_POST['stock']) ? $_POST['stock'] : '';
    if($name===''||$description==='' || $price==='' || $stock===''|| $image['error']!==0 || $collection==='' ){
        echo "Veuillez rem plire tout les champ";
    exit;
    }
    
    $chemin_image = $image['name'];
    move_uploaded_file($image['tmp_name'], "images/" . $image['name']);
    $sql=$pdo->prepare("SELECT id_collection FROM collections WHERE id_collection=?");
    $sql->execute([$collection]);
    $id_collection=$sql->fetch(PDO::FETCH_ASSOC);
    $sql=$pdo->query("SELECT id_customer FROM customers LIMIT 1");
    $id_customer=$sql->fetch(PDO::FETCH_ASSOC);
    
    $sql=$pdo->prepare("INSERT INTO products (name , description , image , price , stock , id_collection , id_customer) 
    VALUES (? , ? , ? , ? , ? , ? , ?)");
    $sql->execute([
        $name , $description , $chemin_image , $price , $stock , $id_collection['id_collection'] , $id_customer['id_customer']
    ]);
    header('location:index.php');
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter produit</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
   <div class='Ajouter'>
    <form action='ajouter.php' method="post" enctype='multipart/form-data'>
    <label for='name'>name Product</label>
<input id='name' name='name' type='text' placeholder='Short'><br>
<label for='price'>price Product</label>
<input id='price' name='price' type='number' placeholder='400dh'><br>
<label for='description'>Description</label>
<textarea id='description' name='description'></textarea><br>
<label for="stock">Stock</label>
<input id="stock" name="stock" type="number" placeholder="10"><br>
<label for='image'>Image</label>
<input id='image' name='image' type='file'><br>
<label for="collection">Collection</label>
<select name='collection' id='collection'>
    <option value=''>Choisir une collection</option>
    <option value='1'>Ete 2026</option>
    <option value='2'>Automne 2026</option>
</select><br>
<button type='submit'>Ajouter</button>
<a href='index.php'>Annuler</a>
    </form>
   </div>
</body>
</html>