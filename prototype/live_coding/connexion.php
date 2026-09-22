<?php
$dbname='gestion_produits';
$host='localhost';
$username='root';
$password='';
try{
    $pdo=new PDO ("mysql:host=$host;dbname=$dbname",$username,$password);
}catch(PDOException $e){
    echo "erreur" .$e->getMessage();
}