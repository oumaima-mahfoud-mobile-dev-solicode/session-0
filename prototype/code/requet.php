<?php
require_once'connexion.php';

$sql=$pdo->query("SELECT p.name, p.description, p.image, p.price, c.name as name_collection FROM products as p
JOIN collections as c ON p.id_collection = c.id_collection");
$products=$sql->fetchAll(PDO::FETCH_ASSOC);
?>