<?php

// Rediriger vers index si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Connexion à la base
$host = 'localhost';
$dbname = 'sessionlog';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Mise à jour des informations
if (isset($_POST['modifier'])) {
    $id = $_SESSION['user']['id_user'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $age = $_POST['age'];

    $sql = "UPDATE user SET nom_user = ?, prenom_user = ?, age_user = ? WHERE id_user = ?";    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $prenom, $age, $id]);

    // Mettre à jour les infos en session
    $_SESSION['user']['nom_user'] = $nom;
    $_SESSION['user']['prenom_user'] = $prenom;
    $_SESSION['user']['age_user'] = $age;

    $message = "Informations mises à jour.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panneau de configuration</title>
</head>
<body>
    <h2>Panneau de configuration</h2>
    <p>Bonjour, <?= $_SESSION['user']['prenom_user'] ?> <?= $_SESSION['user']['nom_user'] ?>.</p>

    <?php if (isset($message)) echo "$message</p>"; ?>

    <form method="POST">
        <label>Nom :</label>
        <input type="text" name="nom" value="<?= $_SESSION['user']['nom_user'] ?>" required><br><br>

        <label>Prénom :</label>
        <input type="text" name="prenom" value="<?= $_SESSION['user']['prenom_user'] ?>" required><br><br>

        <label>Âge :</label>
        <input type="number" name="age" value="<?= $_SESSION['user']['age_user'] ?>" required><br><br>

        <input type="submit" name="modifier" value="Enregistrer les modifications">
    </form>

    <br>
    <a href="index.php">Retour à l'accueil</a>
</body>
</html>

 <!-- "?" racourci de < ?php...  -->