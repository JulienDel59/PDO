<?php
// Connexion à la base de données
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

if (isset($_POST['inscription'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $motdepasse = password_hash($_POST['motdepasse'], PASSWORD_DEFAULT);

    // Vérifie si l'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE mailUtilisateur = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $message = "Ce mail est déjà pris.";
    } else {
        // Insère le nouvel utilisateur
        $stmt = $pdo->prepare("INSERT INTO utilisateur (nomUtilisateur, prenomUtilisateur, mailUtilisateur, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $motdepasse]);
        $message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>
    <h2>Inscription</h2>

    <?php if (!empty($message)) echo "<p>$message</p>"; ?>

    <form method="POST">
        <label>Nom :</label>
        <input type="text" name="nom" required><br>

        <label>Prenom :</label>
        <input type="text" name="prenom" required><br>

        <label>Email :</label>
        <input type="email" name="email" required><br>

        <label>Mot de passe :</label>
        <input type="password" name="motdepasse" required><br>

        <input type="submit" name="inscription" value="S'inscrire">
    </form>

    <br>
    <a href="index.php">Retour à la connexion</a>
</body>
</html>
