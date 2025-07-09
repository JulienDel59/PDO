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

$sqlAll = "SELECT * FROM vehicule";
$stmtAll =$pdo->prepare($sqlAll);
$stmtAll ->execute();

$resultsAll = $stmtAll->fetchAll(PDO::FETCH_ASSOC);



$sqlCouleur = "SELECT * FROM `couleur_`";
$stmtCouleur = $pdo->prepare($sqlCouleur);
$stmtCouleur->execute();

$resultsCouleur = $stmtCouleur->fetchAll(PDO::FETCH_ASSOC);

$sqlType = "SELECT * FROM `type_vehicule`";
$stmtType = $pdo->prepare($sqlType);
$stmtType->execute();

$resultsType = $stmtType->fetchAll(PDO::FETCH_ASSOC);

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
        <input type="submit" name="submitColor" value="Couleur pour la BDD">
        <br>
        <input type="text" name="typeName">
        <input type="submit" name="submitType" value="Type pour la BDD">

    </form>

    <form method="POST">
        <input type="text" name="immatriculation">
        <select name="selectAddCouleur">
            <?php
                foreach ($resultsCouleur as $key => $value) {
                   echo "<option value='" . $value['IdCouleur_'] . "'>". $value['nomCouleur'] ." </option>";
                }           
            ?>
        </select>
        <select name="selectAddType">
            <?php
                foreach ($resultsType as $key => $value) {
                   echo "<option value='" . $value['IdType'] . "'>". $value['nomType'] ." </option>";
                }           
            ?>
        </select>
        <input type ="submit" name="submitVehicule" value="Ajouter un vehicule">
    </form>
    
    <hr>
   
     <?php

        foreach ($resultsAll as $key => $value) {
            $idASupprimer = $value['idVehicule'];
            echo"<form method='POST'>";
            echo"<input type='hidden' name='idDelete' value='$idASupprimer'>";

            foreach ($value as $key =>$value2) {
                echo $key . " : " . $value2 . " - ";
            }
            echo '<a href="?id=' . $idASupprimer . '">Modifier</a>';
            echo '<input type="submit" name="submitDelete" value="Supprimer"><br>';
            echo "</form>";
        }

        if(isset($_POST['submitDelete'])){
            $idToDelete = $_POST['idDelete'];
            $sqlDelete ="DELETE FROM vehicule WHERE idVehicule = '$idToDelete'";
            $stmtDelete = $pdo->prepare($sqlDelete);
            $stmtDelete->execute();
        }

     ?>
    
</body>
</html>

<?php
    if(isset($_POST['submitVehicule'])){
        $color = $_POST['selectAddCouleur'];
        $immatriculation = $_POST['immatriculation'];
        $type = $_POST['selectAddType'];
        echo $immatriculation;
        echo $color;
        echo $type;

        $sql = "INSERT INTO `vehicule`( `immatriculation_`, `IdType`, `IdCouleur_`) VALUES ('$immatriculation','$type','$color')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        echo "data Vehicule envoyées en bdd";
       
    }

    if(isset($_POST['submitColor'])){

        $color = $_POST['colorName'];
        $sql = "INSERT INTO `couleur_`( `nomCouleur`) VALUES ('$color')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    
    echo "data couleur envoyées en bdd";
    }

    if (isset($_POST['submitType'])){

        $type = $_POST['typeName'];
        $sql = "INSERT INTO `type_vehicule`( `nomType`) VALUES ('$type')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

    echo "data type vehicule envoyées en bdd";
    }

?>

<hr>

<?php 

    if(isset($_GET["id"])){
        $id= $_GET['id'];
        $sqlId = "SELECT * FROM `vehicule`WHERE idVehicule = '$id'";

        $stmtId= $pdo->prepare($sqlId);
        $stmtId->execute();

        $resultsId = $stmtId->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<form method="POST">
               <input type="text" name="idUpdate" value="' . $resultsId[0]['idVehicule'] . '">
               <input type="text" name="immatriculationUpdate" value="' . $resultsId[0]['immatriculation_'] . '">
               <input type="text" name="typeUpdate" value="' . $resultsId[0]['IdType'] . '">
               <input type="text" name="couleurUpdate" value="' . $resultsId[0]['IdCouleur_'] . '">
               <input type="submit" name="submitUpdate" value="Metre a jour la BDD">
              </form>';

              var_dump($resultsId);
         
    }

      if (isset($_POST['submitUpdate'])){

        $idUpdate = $_POST['idUpdate'];
        $immatriculation = $_POST['immatriculationUpdate'];
        $type = $_POST['typeUpdate'];
        $couleur = $_POST['couleurUpdate'];


        $sqlUpdate = "UPDATE `vehicule` SET `immatriculation_`='  $immatriculation ',`IdType`='$type',`IdCouleur_`='$couleur' WHERE idVehicule='$idUpdate'";

        $stmtUpdate= $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute();
      
      }


    ?>

   