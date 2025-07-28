<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

// Connexion BDD
$pdo = new PDO("mysql:host=localhost;dbname=projetbibli;charset=utf8", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupération de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT id_utilisateur, nomUtilisateur, prenomUtilisateur FROM utilisateur WHERE mailUtilisateur = ?");
$stmt->execute([$_SESSION['email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$id_utilisateur = $user['id_utilisateur'];

// Traitement d’un emprunt
$message = "";
if (isset($_POST['emprunter']) && isset($_POST['id_livre'])) {
    $id_livre = $_POST['id_livre'];

    // Vérifie si ce livre est déjà emprunté (non encore retourné)
    $check = $pdo->prepare("SELECT * FROM emprunts WHERE id_livres = ? AND dateRetour > NOW()");
    $check->execute([$id_livre]);

    if ($check->rowCount() > 0) {
        $message = "Ce livre est déjà emprunté.";
    } else {
        // Enregistre l'emprunt
        $stmt = $pdo->prepare("INSERT INTO emprunts (dateEmprunt, dateRetour, id_livres, id_utilisateur) VALUES (NOW(), DATE_ADD(NOW(), INTERVAL 21 DAY), ?, ?)");
        $stmt->execute([$id_livre, $id_utilisateur]);

        $message = "Livre emprunté avec succès !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bibliothèque</title>
</head>
<body>

    <h2>Bienvenue, <?= htmlspecialchars($user['prenomUtilisateur']) . " " . htmlspecialchars($user['nomUtilisateur'])?></h2>
    <a href="logout.php">Se déconnecter</a><br><br>

    <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>

    <h3>Liste des livres</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>Titre</th>
            <th>Auteur</th>
            <th>Année</th>
            <th>Genre</th>
            <th>Statut</th>
            <th>Action</th>
        </tr>

    <?php
    // Requête pour tous les livres + info sur disponibilité
    $stmt = $pdo->query("
        SELECT l.id_livres, l.titre, l.annee, 
           e.nomAuteur, e.prenomAuteur, 
           g.nomGenre,
           (
               SELECT COUNT(*) 
               FROM emprunts em 
               WHERE em.id_livres = l.id_livres AND em.dateRetour > NOW()
           ) AS estEmprunte,
           (
               SELECT em.dateRetour
               FROM emprunts em 
               WHERE em.id_livres = l.id_livres AND em.dateRetour > NOW()
               ORDER BY em.dateRetour ASC
               LIMIT 1
           ) AS prochaineDateRetour
    FROM livres l
    LEFT JOIN ecrir ec ON l.id_livres = ec.id_livres
    LEFT JOIN ecrivains e ON ec.id_ecrivains = e.id_ecrivains
    LEFT JOIN estdegenre edg ON l.id_livres = edg.id_livres
    LEFT JOIN genres g ON edg.id_genre = g.id_genre
    ORDER BY l.titre
    ");

        while ($livre = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dispo = $livre['estEmprunte'] == 0;

            echo "<tr>";
            echo "<td>" . htmlspecialchars($livre['titre']) . "</td>";
            echo "<td>" . htmlspecialchars($livre['prenomAuteur']) . " " . htmlspecialchars($livre['nomAuteur']) . "</td>";
            echo "<td>" . htmlspecialchars($livre['annee']) . "</td>";
            echo "<td>" . htmlspecialchars($livre['nomGenre']) . "</td>";
            echo "<td>" . ($dispo ? "Disponible" : "Déjà emprunté") . "</td>";
            echo "<td>";
            if ($dispo) {
                echo "<form method='POST' style='display:inline;'>
                        <input type='hidden' name='id_livre' value='" . $livre['id_livres'] . "'>
                        <input type='submit' name='emprunter' value='Emprunter'>
                      </form>";
            } else {
                $dateRetour = date('d/m/Y' , strtotime($livre['prochaineDateRetour']));
                echo "Retour prévu le : " . $dateRetour;
            }
            echo "</td>";
            echo "</tr>";
        }
        ?>
    </table>

</body>
</html>
