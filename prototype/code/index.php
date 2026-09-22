<?php
require_once'requet.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gestion de produits</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="title">
        <h1>Gestion de produits</h1>
    </div>
   <div class="produits" >
    <a href=ajouter.php>Ajouter produit</a>
 <?php foreach($products as $product) : ?>
    <div class="card">
        <h1><?php echo $product['name'] ?></h1>
        <img src="images/<?= htmlspecialchars($product['image']) ?>" alt="">
        <h2><?php echo  $product['price']?></h2>
        <p><?php echo $product['description']?></p>
        <h3><?php echo $product['name_collection']?></h3>
    </div>
    <?php endforeach; ?>
   </div>
</body>
</html>