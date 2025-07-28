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
    <br>
    <br>
        
    <?php
    
        if (isset($_POST['submitLivre'])) {
         // Récupération des données du formulaire
         $titre = trim($_POST['titre']);
         $genreNom = trim($_POST['genre']);
         $annee = $_POST['annee'];
         $nomAuteur = trim($_POST['nomAuteur']);
         $prenomAuteur = trim($_POST['prenomAuteur']);

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

    <?php

        if (isset($_POST['deleteLivre']) && isset($_POST['id_livre'])) {
        $id_livre = $_POST['id_livre'];
        try {
        $pdo->beginTransaction();

        // Supprimer les relations
        $stmt = $pdo->prepare("DELETE FROM ecrir WHERE id_livres = ?");
        $stmt->execute([$id_livre]);

        $stmt = $pdo->prepare("DELETE FROM estDeGenre WHERE id_livres = ?");
        $stmt->execute([$id_livre]);

        // Supprimer le livre
        $stmt = $pdo->prepare("DELETE FROM livres WHERE id_livres = ?");
        $stmt->execute([$id_livre]);

        $pdo->commit();
        echo "<p style='color: green;'> Livre supprimé avec succès !</p>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<p style='color: red;'> Erreur lors de la suppression : " . $e->getMessage() . "</p>";
    }
    }

    ?>

    <h2>Liste des livres</h2>
    <table border="1" cellpadding="5">
    <tr>
        <th>Titre</th>
        <th>Année</th>
        <th>Auteur</th>
        <th>Genre</th>
        <th>Action</th>
    </tr>

    <?php
    $stmt = $pdo->query("
        SELECT l.id_livres, l.titre, l.annee, 
               e.nomAuteur, e.prenomAuteur, 
               g.nomGenre
        FROM livres l
        LEFT JOIN ecrir ec ON l.id_livres = ec.id_livres
        LEFT JOIN ecrivains e ON ec.id_ecrivains = e.id_ecrivains
        LEFT JOIN estDeGenre edg ON l.id_livres = edg.id_livres
        LEFT JOIN genres g ON edg.id_genre = g.id_genre
        ORDER BY l.titre
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['titre']) . "</td>";
    echo "<td>" . htmlspecialchars($row['annee']) . "</td>";
    echo "<td>" . htmlspecialchars($row['prenomAuteur']) . " " . htmlspecialchars($row['nomAuteur']) . "</td>";
    echo "<td>" . htmlspecialchars($row['nomGenre']) . "</td>";
    echo "<td>
            <form method='POST' action='modifier_livre.php' style='display:inline;'>
                <input type='hidden' name='id_livre' value='" . $row['id_livres'] . "'>
                <input type='submit' name='editLivre' value='Modifier'>
            </form>
            <form method='POST' onsubmit='return confirm(\"Confirmer la suppression de ce livre ?\");' style='display:inline;'>
                <input type='hidden' name='id_livre' value='" . $row['id_livres'] . "'>
                <input type='submit' name='deleteLivre' value='Supprimer'>
            </form>
          </td>";
    echo "</tr>";
}

    ?>
    </table>

<h2>Gestion des genres</h2>

<!-- Formulaire d'ajout -->
<form method="POST">
    <label>Nom du genre :</label>
    <input type="text" name="nouveauGenre" required>
    <input type="submit" name="ajouterGenre" value="Ajouter genre">
</form>

<br>

<!-- Tableau des genres -->
<table border="1" cellpadding="5">
    <tr>
       
        <th>Nom du genre</th>
        <th>Actions</th>
    </tr>

<?php
// 1. Ajout d’un genre
if (isset($_POST['ajouterGenre'])) {
    $nomGenre = trim($_POST['nouveauGenre']);
    if (!empty($nomGenre)) {
        $stmt = $pdo->prepare("SELECT * FROM genres WHERE nomGenre = ?");
        $stmt->execute([$nomGenre]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO genres (nomGenre) VALUES (?)");
            $stmt->execute([$nomGenre]);
            echo "<p style='color: green;'>Genre ajouté avec succès.</p>";
        } else {
            echo "<p style='color: orange;'>Ce genre existe déjà.</p>";
        }
    }
}

// 2. Suppression d’un genre
if (isset($_POST['supprimerGenre'])) {
    $id_genre = $_POST['id_genre'];
    try {
        $stmt = $pdo->prepare("DELETE FROM estDeGenre WHERE id_genre = ?");
        $stmt->execute([$id_genre]);

        $stmt = $pdo->prepare("DELETE FROM genres WHERE id_genre = ?");
        $stmt->execute([$id_genre]);
        echo "<p style='color: green;'>Genre supprimé avec succès.</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Erreur lors de la suppression : " . $e->getMessage() . "</p>";
    }
}

// 3. Modification d’un genre
if (isset($_POST['modifierGenre'])) {
    $id_genre = $_POST['id_genre'];
    $nomGenre = trim($_POST['nomGenre']);

    if (!empty($nomGenre)) {
        $stmt = $pdo->prepare("UPDATE genres SET nomGenre = ? WHERE id_genre = ?");
        $stmt->execute([$nomGenre, $id_genre]);
        echo "<p style='color: green;'>Genre modifié avec succès.</p>";
    }
}

// 4. Affichage de tous les genres
$stmt = $pdo->query("SELECT * FROM genres ORDER BY nomGenre");

while ($genre = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<form method='POST'>";
    echo "<td><input type='text' name='nomGenre' value='" . htmlspecialchars($genre['nomGenre']) . "'></td>";
    echo "<td>
            <input type='hidden' name='id_genre' value='" . $genre['id_genre'] . "'>
            <input type='submit' name='modifierGenre' value='Modifier'>
            <input type='submit' name='supprimerGenre' value='Supprimer' onclick=\"return confirm('Supprimer ce genre ?');\">
          </td>";
    echo "</form>";
    echo "</tr>";
}
?>
</table>

<h2>Gestion des auteurs</h2>

<!-- Formulaire d'ajout -->
<form method="POST">
    <label>Nom :</label>
    <input type="text" name="nomAuteur" required>

    <label>Prénom :</label>
    <input type="text" name="prenomAuteur" required>

    <input type="submit" name="ajouterAuteur" value="Ajouter auteur">
</form>

<br>

<!-- Tableau des auteurs -->
<table border="1" cellpadding="5">
    <tr>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Actions</th>
    </tr>

<?php
// 1. Ajout d’un auteur
if (isset($_POST['ajouterAuteur'])) {
    $nom = trim($_POST['nomAuteur']);
    $prenom = trim($_POST['prenomAuteur']);

    if (!empty($nom) && !empty($prenom)) {
        $stmt = $pdo->prepare("SELECT * FROM ecrivains WHERE nomAuteur = ? AND prenomAuteur = ?");
        $stmt->execute([$nom, $prenom]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO ecrivains (nomAuteur, prenomAuteur) VALUES (?, ?)");
            $stmt->execute([$nom, $prenom]);
            echo "<p style='color: green;'>Auteur ajouté avec succès.</p>";
        } else {
            echo "<p style='color: orange;'>Cet auteur existe déjà.</p>";
        }
    }
}

// 2. Suppression d’un auteur
if (isset($_POST['supprimerAuteur'])) {
    $id = $_POST['id_ecrivains'];
    try {
        // Supprimer d’abord les relations avec les livres
        $stmt = $pdo->prepare("DELETE FROM ecrir WHERE id_ecrivains = ?");
        $stmt->execute([$id]);

        // Supprimer l’auteur
        $stmt = $pdo->prepare("DELETE FROM ecrivains WHERE id_ecrivains = ?");
        $stmt->execute([$id]);
        echo "<p style='color: green;'>Auteur supprimé avec succès.</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Erreur lors de la suppression : " . $e->getMessage() . "</p>";
    }
}

// 3. Modification d’un auteur
if (isset($_POST['modifierAuteur'])) {
    $id = $_POST['id_ecrivains'];
    $nom = trim($_POST['nomAuteur']);
    $prenom = trim($_POST['prenomAuteur']);

    if (!empty($nom) && !empty($prenom)) {
        $stmt = $pdo->prepare("UPDATE ecrivains SET nomAuteur = ?, prenomAuteur = ? WHERE id_ecrivains = ?");
        $stmt->execute([$nom, $prenom, $id]);
        echo "<p style='color: green;'>Auteur modifié avec succès.</p>";
    }
}

// 4. Affichage de tous les auteurs
$stmt = $pdo->query("SELECT * FROM ecrivains ORDER BY nomAuteur");

while ($auteur = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<form method='POST'>";
    echo "<td><input type='text' name='nomAuteur' value='" . htmlspecialchars($auteur['nomAuteur']) . "' required></td>";
    echo "<td><input type='text' name='prenomAuteur' value='" . htmlspecialchars($auteur['prenomAuteur']) . "' required></td>";
    echo "<td>
            <input type='hidden' name='id_ecrivains' value='" . $auteur['id_ecrivains'] . "'>
            <input type='submit' name='modifierAuteur' value='Modifier'>
            <input type='submit' name='supprimerAuteur' value='Supprimer' onclick=\"return confirm('Supprimer cet auteur ?');\">
          </td>";
    echo "</form>";
    echo "</tr>";
}
?>
</table>

   
</body>
</html>