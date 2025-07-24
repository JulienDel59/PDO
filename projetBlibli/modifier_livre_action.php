<?php
$host = 'localhost';
$dbname = 'projetbibli';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if (isset($_POST['updateLivre'])) {
    $id_livre = $_POST['id_livre'];
    $titre = $_POST['titre'];
    $annee = $_POST['annee'];
    $nomAuteur = $_POST['nomAuteur'];
    $prenomAuteur = $_POST['prenomAuteur'];
    $genreNom = $_POST['genre'];

    try {
        $pdo->beginTransaction();

        // Vérifie ou ajoute l’auteur
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

        // Vérifie ou ajoute le genre
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

        // Met à jour le livre
        $stmt = $pdo->prepare("UPDATE livres SET titre = ?, annee = ? WHERE id_livres = ?");
        $stmt->execute([$titre, $annee, $id_livre]);

        // Met à jour les liens
        $stmt = $pdo->prepare("UPDATE ecrir SET id_ecrivains = ? WHERE id_livres = ?");
        $stmt->execute([$id_auteur, $id_livre]);

        $stmt = $pdo->prepare("UPDATE estDeGenre SET id_genre = ? WHERE id_livres = ?");
        $stmt->execute([$id_genre, $id_livre]);

        $pdo->commit();

        echo "<p style='color: green;'>Le livre a été mis à jour avec succès.</p>";
        echo "<a href='index.php'>Retour à la liste des livres</a>";

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<p style='color: red;'>Erreur : " . $e->getMessage() . "</p>";
    }
}
?>
