<?php
// 1. Connexion à la base de données
$host = 'localhost';
$dbname = 'rpg';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie à la base de données.<br><br>";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// 2. Requête SQL et exécution pour la table personnage
$sqlPersonnage = "SELECT * FROM `personnage`";
$reqPersonnage = $pdo->prepare($sqlPersonnage);
$reqPersonnage->execute();
$personnages = $reqPersonnage->fetchAll();

// 3. Requête SQL et exécution pour la table arme
$sqlArme = "SELECT * FROM `arme`";
$reqArme = $pdo->prepare($sqlArme);
$reqArme->execute();
$armes = $reqArme->fetchAll();

// 4. Requête SQL et exécution pour la table classe
$sqlClasse = "SELECT * FROM `classe`";
$reqClasse = $pdo->prepare($sqlClasse);
$reqClasse->execute();
$classes = $reqClasse->fetchAll();

// 5. Requête SQL et exécution pour la table Type Arme
$sqlTypeArme = "SELECT * FROM `typearme`";
$reqTypeArme = $pdo->prepare($sqlTypeArme);
$reqTypeArme->execute();
$reqTypeArmes = $reqTypeArme->fetchAll();

// . Affichage des personnages
echo "<h2>Liste des personnages</h2>";
echo "<ul>";
foreach ($personnages as $perso) {
    echo "<li><strong>{$perso['nom']}</strong> (Surnom : {$perso['surnom']}) - Niveau : {$perso['level']}</li>";
}
echo "</ul>";

// . Affichage des armes
echo "<h2>Liste des armes</h2>";
echo "<ul>";
foreach ($armes as $arme) {
    echo "<li><strong>{$arme['nom']}</strong> - Level : {$arme['levelMin']} - Dégât : {$arme['degat']}</li>";
}
echo "</ul>";

// . Affichage des classe
echo "<h2>Liste des classes</h2>";
echo "<ul>";
foreach ($classes as $classe) {
    echo "<li><strong>{$classe['nom']}</strong> - Base force : {$classe['baseForce']} - Base Agilité : {$classe['baseAgi']} - Base Inteligence : {$classe['baseIntel']}</li>";
}
echo "</ul>";

// . Affichage des classe
echo "<h2>Liste type d'arme</h2>";
echo "<ul>";
foreach ($reqTypeArmes as $typeA) {
    echo "<li><strong>{$typeA['libelle']}</strong> - 1 si distance 0 si non : {$typeA['estDistance']} </li>";
}
echo "</ul>";

?>



