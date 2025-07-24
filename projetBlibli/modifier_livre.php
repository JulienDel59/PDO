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

if (!isset($_POST['id_livre'])) {
    die("Aucun livre sélectionné.");
}

$id_livre = $_POST['id_livre'];

// Récupérer les infos du livre
$stmt = $pdo->prepare("
    SELECT l.titre, l.annee, e.nomAuteur, e.prenomAuteur, g.nomGenre
    FROM livres l
    LEFT JOIN ecrir ec ON l.id_livres = ec.id_livres
    LEFT JOIN ecrivains e ON ec.id_ecrivains = e.id_ecrivains
    LEFT JOIN estDeGenre edg ON l.id_livres = edg.id_livres
    LEFT JOIN genres g ON edg.id_genre = g.id_genre
    WHERE l.id_livres = ?
");
$stmt->execute([$id_livre]);
$livre = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$livre) {
    die("Livre introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un livre</title>
</head>
<body>
    <h2>Modifier le livre</h2>

    <form method="POST" action="modifier_livre_action.php">
        <input type="hidden" name="id_livre" value="<?= $id_livre ?>">

        <label>Titre</label>
        <input type="text" name="titre" value="<?= htmlspecialchars($livre['titre']) ?>" required><br>

        <label>Année</label>
        <input type="number" name="annee" value="<?= htmlspecialchars($livre['annee']) ?>" required><br>

        <label>Nom Auteur</label>
        <input type="text" name="nomAuteur" value="<?= htmlspecialchars($livre['nomAuteur']) ?>" required><br>

        <label>Prénom Auteur</label>
        <input type="text" name="prenomAuteur" value="<?= htmlspecialchars($livre['prenomAuteur']) ?>" required><br>

        <label>Genre</label>
        <input type="text" name="genre" value="<?= htmlspecialchars($livre['nomGenre']) ?>" required><br><br>

        <input type="submit" name="updateLivre" value="Enregistrer les modifications">
    </form>
</body>
</html>
