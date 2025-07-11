<?php
session_start();
$host = 'localhost';
$dbname = 'sessionlog';
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
    <?php

    if (!isset($_SESSION['user'])){
        echo '<form method="POST">
        <label>Identifiant</label>
        <input type="text" name="identifiant">
        <label>Password</label>
        <input type="password" name="password">
        <input type="submit" name="submitConnexion" value="Se connecter">
    </form> ';
    }
    else{
        echo '<form method="POST">
        <input type="submit" name="deconnexion" value="Se déconnecter">
        </form>';
        echo "Bonjour, " . htmlspecialchars($_SESSION['user']['nom_user']) . " " . htmlspecialchars($_SESSION['user']['prenom_user']) . " . Vous êtes connecté . ";
        // echo '<br><a href="dashboard.php">Accéder au panneau de configuration</a>';
        include 'dashboard.php';
    }
    ?>
    
    

    <?php

        if (isset($_POST['submitConnexion'])){
            $mail = ($_POST['identifiant']);
            $password = ($_POST['password']);

            $sql ="SELECT * FROM `user` WHERE adresse_mail_user = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$mail]);
            $results= $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    if($results){
        if ($password == $results[0]["password_user"]){
            $_SESSION['user'] = [
                "id_user" => $results[0]["id_user"] ,
                "nom_user" => $results[0]["nom_user"] ,
                "prenom_user" => $results[0]["prenom_user"] ,
                "age_user" => $results[0]["age_user"],
                "adresse_mail_user" => $results[0]["adresse_mail_user"] ,
            ];
                header("Location: index.php");
        }
        else{
            echo "Mot de passe incorrect";
        }
    }
    else{
        echo "Utilisateur inconnu";
    }
        }

        if (isset($_POST['deconnexion'])){
            session_destroy();
            header("Location: index.php");

        }
    ?>

</body>
</html>