<?php
session_start();
$host = 'localhost';
$dbname = 'projetbibli';
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
    <title>Bibliothéque</title>
</head>
<body>

    <form method="POST">
        <h2>Ajouter un livre </h2>
        <br>
        <label>Titre</label>
        <input type="text" name="titre" required>
        <br>
        <label>Genre</label>
        <input type="text" name="genre" required>
        <br>
        <label>Année</label>
        <input type="number" name="annee" min="1900" max="2025" required>
        <br>
        <label>Auteur</label>
        <input type="text" name="nomAuteur" placeholder="Nom" required>
        <input type="text" name="prenomAuteur" placeholder="Prénom" required>
        <br>
        <input type=submit name="submitLivre" value="Ajouter le livre">
    </form>
        
    <?php
    
        if (isset($_POST['submitLivre'])) {
         // Récupération des données du formulaire
         $titre = $_POST['titre'];
         $genreNom = $_POST['genre'];
         $annee = $_POST['annee'];
         $nomAuteur = $_POST['nomAuteur'];
         $prenomAuteur = $_POST['prenomAuteur'];

        try {
         $pdo->beginTransaction();

         // 1. Vérifier ou ajouter l’auteur
         $stmt = $pdo->prepare("SELECT id_ecrivains FROM ecrivains WHERE nomAuteur = ? AND prenomAuteur = ?");
         $stmt->execute([$nomAuteur, $prenomAuteur]);
         $auteur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($auteur) {
            $id_auteur = $auteur['id_ecrivains'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO ecrivains (nomAuteur, prenomAuteur) VALUES (?, ?)");
            $stmt->execute([$nomAuteur, $prenomAuteur]);
            $id_auteur = $pdo->lastInsertId();
        }

         //  2. Vérifier ou ajouter le genre
         $stmt = $pdo->prepare("SELECT id_genre FROM genres WHERE nomGenre = ?");
         $stmt->execute([$genreNom]);
         $genre = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($genre) {
            $id_genre = $genre['id_genre'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO genres (nomGenre) VALUES (?)");
            $stmt->execute([$genreNom]);
            $id_genre = $pdo->lastInsertId();
        }

         //  3. Ajouter le livre
         $stmt = $pdo->prepare("INSERT INTO livres (titre, annee) VALUES (?, ?)");
         $stmt->execute([$titre, $annee]);
         $id_livre = $pdo->lastInsertId();

         //  4. Lier le livre à l’auteur (table ecrir)
         $stmt = $pdo->prepare("INSERT INTO ecrir (id_ecrivains, id_livres) VALUES (?, ?)");
         $stmt->execute([$id_auteur, $id_livre]);

         //  5. Lier le livre au genre (table estDeGenre)
         $stmt = $pdo->prepare("INSERT INTO estDeGenre (id_livres, id_genre) VALUES (?, ?)");
         $stmt->execute([$id_livre, $id_genre]);

         $pdo->commit();

        echo "<p style='color: green;'> Livre ajouté avec succès !</p>";

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<p style='color: red;'> Erreur : " . $e->getMessage() . "</p>";
    }
}

    ?>

</body>
</html>