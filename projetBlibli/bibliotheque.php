<?php
session_start();

// Redirige si l'utilisateur n'est pas connecté
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

// Connexion à la base de données
$pdo = new PDO("mysql:host=localhost;dbname=projetbibli;charset=utf8", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bibliothèque - Tous les livres</title>
</head>
<body>

    <h2>Bienvenue dans la bibliothèque, <?= htmlspecialchars($_SESSION['email']) ?></h2>
    <a href="logout.php">Se déconnecter</a>
    <br><br>

    <h3>Liste des livres</h3>

    <table border="1" cellpadding="5">
        <tr>
            <th>Titre</th>
            <th>Auteur</th>
            <th>Année</th>
            <th>Genre</th>
        </tr>

        <?php
        // Récupération de tous les livres avec auteur et genre
        $stmt = $pdo->query("
            SELECT l.titre, l.annee, 
                   e.nomAuteur, e.prenomAuteur, 
                   g.nomGenre
            FROM livres l
            LEFT JOIN ecrir ec ON l.id_livres = ec.id_livres
            LEFT JOIN ecrivains e ON ec.id_ecrivains = e.id_ecrivains
            LEFT JOIN estdegenre edg ON l.id_livres = edg.id_livres
            LEFT JOIN genres g ON edg.id_genre = g.id_genre
            ORDER BY l.titre
        ");

        // Affichage de chaque livre
        while ($livre = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($livre['titre']) . "</td>";
            echo "<td>" . htmlspecialchars($livre['prenomAuteur'] . " " . $livre['nomAuteur']) . "</td>";
            echo "<td>" . htmlspecialchars($livre['annee']) . "</td>";
            echo "<td>" . htmlspecialchars($livre['nomGenre']) . "</td>";
            echo "</tr>";
        }
        ?>
    </table>

</body>
</html>
