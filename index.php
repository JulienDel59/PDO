<?php
// 1. Connexion à la base de données
$host = 'localhost';
$dbname = 'concession';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie à la base de données.<br><br>";
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="POST">
        <input type="text" name="colorName">
        <input type="submit" name="sumbmitColor" value="Couleur pour la BDD">

    </form>
</body>
</html>

<?php

    if(isset($_POST['sumbmitColor'])){

        $color = $_POST['colorName'];
        $sql = "INSERT INTO `couleur_`( `nomCouleur`) VALUES ('$color')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    
    echo "data envoyées en bdd";
    }
?>