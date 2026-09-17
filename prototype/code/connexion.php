<?php
$dbname='gestion_produits';
$host='localhost';
$username='root';
$password='Solicode@2';

try {
    $pdo= new PDO("mysql:host=$host;dbname=$dbname" , $username , $password);
} catch (PDOException $e) {
    echo "ERREUR DE CONNEXION :" .$e->getMessage();
}