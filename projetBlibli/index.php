<?php
session_start();

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

// Traitement du formulaire de connexion
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $motdepasse = $_POST['motdepasse'];

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE mailUtilisateur = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($motdepasse, $user['password'])) {
        $_SESSION['email'] = $user['mailUtilisateur'];
        header("Location: bibliotheque.php"); // redirige vers la page principale
        exit;
    } else {
        $erreur = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Connexion</title>
</head>
<body>
    <h2>Bienvenue à la Bibliothèque</h2>

    <?php if (!empty($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>

    <form method="POST">
        <label>Email :</label>
        <input type="text" name="email" required><br>

        <label>Mot de passe :</label>
        <input type="password" name="motdepasse" required><br>

        <input type="submit" name="login" value="Se connecter">
    </form>

    <br>
    <a href="signIn.php">Pas encore de compte ? Inscrivez-vous ici</a>
</body>
</html>
