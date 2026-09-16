<?php 

require_once "config/database.php"; 

 require_once("includes/auth.php"); 

$nom = "Admin NPM"; 
$email = "admin@npm.com"; 
$motDePasse = password_hash("123456", PASSWORD_DEFAULT); 
$role = "admin"; 

$sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, role) 
        VALUES (?, ?, ?, ?)"; 

$stmt = $pdo->prepare($sql); 

$stmt->execute([ 
    $nom, 
    $email, 
    $motDePasse, 
    $role 
]); 

echo "Administrateur créé avec succès !";